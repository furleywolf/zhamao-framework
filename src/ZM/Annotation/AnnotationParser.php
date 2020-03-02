<?php


namespace ZM\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Framework\ZMBuf;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ZM\Annotation\CQ\{CQAfter, CQBefore, CQCommand, CQMessage, CQMetaEvent, CQNotice, CQRequest};
use ZM\Annotation\Http\Controller;
use ZM\Annotation\Http\RequestMapping;
use ZM\Annotation\Interfaces\CustomAnnotation;
use ZM\Annotation\Interfaces\Level;
use ZM\Annotation\Module\Closed;
use ZM\Annotation\Module\SaveBuffer;
use ZM\Annotation\Swoole\SwooleEventAfter;
use ZM\Annotation\Swoole\SwooleEventAt;
use ZM\Annotation\Interfaces\Rule;
use ZM\Connection\WSConnection;
use ZM\Utils\DataProvider;

class AnnotationParser
{
    /**
     * 注册各个模块类的注解和模块level的排序
     * @throws ReflectionException
     * @throws AnnotationException
     */
    public static function registerMods() {
        self::loadAnnotationClasses();
        $all_class = getAllClasses(WORKING_DIR . "/src/Module/", "Module");
        $reader = new AnnotationReader();
        foreach ($all_class as $v) {
            $reflection_class = new ReflectionClass($v);
            $class_prefix = '';
            $methods = $reflection_class->getMethods(ReflectionMethod::IS_PUBLIC);
            $class_annotations = $reader->getClassAnnotations($reflection_class);
            foreach ($class_annotations as $vs) {
                if ($vs instanceof Closed) {
                    continue 2;
                } elseif ($vs instanceof Controller) {
                    $class_prefix = $vs->prefix;
                } elseif ($vs instanceof SaveBuffer) {
                    DataProvider::addSaveBuffer($vs->buf_name, $vs->sub_folder);
                }
            }
            foreach ($methods as $vs) {
                $method_annotations = $reader->getMethodAnnotations($vs);
                foreach ($method_annotations as $vss) {
                    if ($vss instanceof Rule) $vss = self::registerRuleEvent($vss, $vs, $reflection_class);
                    else $vss = self::registerMethod($vss, $vs, $reflection_class);

                    if ($vss instanceof SwooleEventAt)              ZMBuf::$events[SwooleEventAt::class][] = $vss;
                    elseif ($vss instanceof SwooleEventAfter)       ZMBuf::$events[SwooleEventAfter::class][] = $vss;
                    elseif ($vss instanceof CQMessage)              ZMBuf::$events[CQMessage::class][] = $vss;
                    elseif ($vss instanceof CQNotice)               ZMBuf::$events[CQNotice::class][] = $vss;
                    elseif ($vss instanceof CQRequest)              ZMBuf::$events[CQRequest::class][] = $vss;
                    elseif ($vss instanceof CQMetaEvent)            ZMBuf::$events[CQMetaEvent::class][] = $vss;
                    elseif ($vss instanceof CQCommand)              ZMBuf::$events[CQCommand::class][] = $vss;
                    elseif ($vss instanceof RequestMapping)         self::registerRequestMapping($vss, $vs, $reflection_class, $class_prefix);
                    elseif ($vss instanceof CustomAnnotation)       ZMBuf::$events[get_class($vss)][] = $vss;
                    elseif ($vss instanceof CQBefore)               ZMBuf::$events[CQBefore::class][$vss->cq_event][] = $vss;
                    elseif ($vss instanceof CQAfter)                ZMBuf::$events[CQAfter::class][$vss->cq_event][] = $vss;
                }
            }
        }

        //给支持level的排个序
        foreach (ZMBuf::$events as $class_name => $v) {
            if ((new $class_name()) instanceof Level) {
                for ($i = 0; $i < count(ZMBuf::$events[$class_name]) - 1; ++$i) {
                    for ($j = 0; $j < count(ZMBuf::$events[$class_name]) - $i - 1; ++$j) {
                        $l1 = ZMBuf::$events[$class_name][$j]->level;
                        $l2 = ZMBuf::$events[$class_name][$j + 1]->level;
                        if ($l1 < $l2) {
                            $t = ZMBuf::$events[$class_name][$j + 1];
                            ZMBuf::$events[$class_name][$j + 1] = ZMBuf::$events[$class_name][$j];
                            ZMBuf::$events[$class_name][$j] = $t;
                        }
                    }
                }
            }
        }
    }

    private static function getRuleCallback($rule_str) {
        $func = null;
        $rule = $rule_str;
        if ($rule != "") {
            $asp = explode(":", $rule);
            $asp_name = array_shift($asp);
            $rest = implode(":", $asp);
            //Swoole 事件时走此switch
            switch ($asp_name) {
                case "connectType": //websocket连接类型
                    $func = function (WSConnection $connection) use ($rest) {
                        return $connection->getType() == $rest ? true : false;
                    };
                    break;
                case "containsGet": //handle http request事件时才能用
                case "containsPost":
                    $get_list = explode(",", $rest);
                    if ($asp_name == "containsGet")
                        $func = function ($request) use ($get_list) {
                            foreach ($get_list as $v) if (!isset($request->get[$v])) return false;
                            return true;
                        };
                    else
                        $func = function ($request) use ($get_list) {
                            foreach ($get_list as $v) if (!isset($request->post[$v])) return false;
                            return true;
                        };
                    /*
                    if ($controller_prefix != '') {
                        $p = ZMBuf::$req_mapping_node;
                        $prefix_exp = explode("/", $controller_prefix);
                        foreach ($prefix_exp as $k => $v) {
                            if ($v == "" || $v == ".." || $v == ".") {
                                unset($prefix_exp[$k]);
                            }
                        }
                        while (($shift = array_shift($prefix_exp)) !== null) {
                            $p->addRoute($shift, new MappingNode($shift));
                            $p = $p->getRoute($shift);
                        }
                        if ($p->getNodeName() != "/") {
                            $p->setMethod($method->getName());
                            $p->setClass($class->getName());
                            $p->setRule($func);
                            return "mapped";
                        }
                    }*/
                    break;
                case "containsJson": //handle http request事件时才能用
                    $json_list = explode(",", $rest);
                    $func = function ($json) use ($json_list) {
                        foreach ($json_list as $v) if (!isset($json[$v])) return false;
                        return true;
                    };
                    break;
                case "dataEqual": //handle websocket message事件时才能用
                    $func = function ($data) use ($rest) { return $data == $rest; };
                    break;
            }
            switch ($asp_name) {
                case "msgMatch": //handle cq message事件时才能用
                    $func = function ($msg) use ($rest) { return matchPattern($rest, $msg); };
                    break;
                case "msgEqual": //handle cq message事件时才能用
                    $func = function ($msg) use ($rest) { return trim($msg) == $rest; };
                    break;

            }
        }
        return $func;
    }

    private static function registerRuleEvent(?AnnotationBase $vss, ReflectionMethod $method, ReflectionClass $class) {
        $vss->callback = self::getRuleCallback($vss->getRule());
        $vss->method = $method->getName();
        $vss->class = $class->getName();
        return $vss;
    }

    private static function registerMethod(?AnnotationBase $vss, ReflectionMethod $method, ReflectionClass $class) {
        $vss->method = $method->getName();
        $vss->class = $class->getName();
        return $vss;
    }

    private static function registerRequestMapping(RequestMapping $vss, ReflectionMethod $method, ReflectionClass $class, string $prefix) {
        $prefix_exp = explode("/", $prefix);
        $route_exp = explode("/", $vss->route);
        foreach ($prefix_exp as $k => $v) {
            if ($v == "" || $v == ".." || $v == ".") {
                unset($prefix_exp[$k]);
            }
        }
        foreach ($route_exp as $k => $v) {
            if ($v == "" || $v == ".." || $v == ".") {
                unset($route_exp[$k]);
            }
        }
        $a = ZMBuf::$req_mapping_node;
        $p = $a;
        if ($prefix_exp == [] && $route_exp == []) {
            $p->setMethod($method->getName());
            $p->setClass($class->getName());
            $p->setRequestMethod($vss->request_method);
            return;
        }
        while (($shift = array_shift($prefix_exp)) !== null) {
            $p->addRoute($shift, new MappingNode($shift));
            $p = $p->getRoute($shift);
        }
        while (($shift = array_shift($route_exp)) !== null) {
            if (mb_substr($shift, 0, 1) == "{" && mb_substr($shift, -1, 1) == "}") {
                $p->removeAllRoute();
            }
            $p->addRoute($shift, new MappingNode($shift));
            $p = $p->getRoute($shift);
        }
        $p->setMethod($method->getName());
        $p->setClass($class->getName());
        $p->setRequestMethod($vss->request_method);
    }

    private static function loadAnnotationClasses() {
        $class = getAllClasses(WORKING_DIR . "/src/ZM/Annotation/", "ZM\\Annotation");
        foreach ($class as $v) {
            $s = WORKING_DIR . '/src/' . str_replace("\\", "/", $v) . ".php";
            require_once $s;
        }
        $class = getAllClasses(WORKING_DIR . "/src/Custom/Annotation/", "Custom\\Annotation");
        foreach ($class as $v) {
            $s = WORKING_DIR . '/src/' . str_replace("\\", "/", $v) . ".php";
            require_once $s;
        }
    }
}