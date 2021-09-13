<?php


namespace ZM\Event;


use Error;
use Exception;
use Swoole\Timer;
use ZM\Annotation\AnnotationBase;
use ZM\Annotation\AnnotationParser;
use ZM\Annotation\Swoole\OnTick;
use ZM\Config\ZMConfig;
use ZM\Console\Console;
use ZM\Store\LightCache;
use ZM\Store\ZMAtomic;

class EventManager
{
    public static $events = [];
    public static $middleware_map = [];
    public static $middlewares = [];
    public static $req_mapping = [];

    public static function addEvent($event_name, ?AnnotationBase $event_obj) {
        Console::debug("Adding event $event_name at ".$event_obj->class.":".$event_obj->method);
        self::$events[$event_name][] = $event_obj;
        (new AnnotationParser())->sortByLevel(self::$events, $event_name);
    }

    public static function loadEventByParser(AnnotationParser $parser) {
        self::$events = $parser->generateAnnotationEvents();
        self::$middlewares = $parser->getMiddlewares();
        self::$middleware_map = $parser->getMiddlewareMap();
        self::$req_mapping = $parser->getReqMapping();
        $parser->verifyMiddlewares();
    }

    /**
     * 注册所有计时器给每个进程
     * @throws Exception
     */
    public static function registerTimerTick() {
        $dispatcher = new EventDispatcher(OnTick::class);
        foreach (self::$events[OnTick::class] ?? [] as $vss) {
            if (server()->worker_id !== $vss->worker_id && $vss->worker_id != -1) return;
            //echo server()->worker_id.PHP_EOL;
            $plain_class = $vss->class;
            Console::debug("Added Middleware-based timer: " . $plain_class . " -> " . $vss->method);
            Timer::tick($vss->tick_ms, function () use ($vss, $dispatcher) {
                set_coroutine_params([]);
                if (ZMAtomic::get("stop_signal")->get() != 0) {
                    Timer::clearAll();
                    return;
                }
                try {
                    $dispatcher->dispatchEvent($vss, null);
                } catch (Exception $e) {
                    Console::error(zm_internal_errcode("E00034") . "Uncaught error from TimerTick: " . $e->getMessage() . " at " . $e->getFile() . "({$e->getLine()})");
                } catch (Error $e) {
                    Console::error(zm_internal_errcode("E00034") . "Uncaught fatal error from TimerTick: " . $e->getMessage());
                    echo Console::setColor($e->getTraceAsString(), "gray");
                    Console::error("Please check your code!");
                }
            });
        }
        $conf = ZMConfig::get("global", "worker_cache") ?? ["worker" => 0];
        if (server()->worker_id == $conf["worker"]) {
            zm_timer_tick(ZMConfig::get("global", "light_cache")["auto_save_interval"] * 1000, function () {
                LightCache::savePersistence();
            });
        }
    }
}
