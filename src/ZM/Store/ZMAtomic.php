<?php


namespace ZM\Store;


use Swoole\Atomic;
use ZM\Config\ZMConfig;

class ZMAtomic
{
    /** @var Atomic[] */
    public static $atomics;

    /**
     * @param $name
     * @return Atomic|null
     */
    public static function get($name): ?Atomic {
        return self::$atomics[$name] ?? null;
    }

    /**
     * 初始化atomic计数器
     */
    public static function init() {
        foreach ((ZMConfig::get("global", "init_atomics") ?? []) as $k => $v) {
            self::$atomics[$k] = new Atomic($v);
        }
        self::$atomics["stop_signal"] = new Atomic(0);
        self::$atomics["_int_is_reload"] = new Atomic(0);
        self::$atomics["wait_msg_id"] = new Atomic(0);
        self::$atomics["_event_id"] = new Atomic(0);
        self::$atomics["server_is_stopped"] = new Atomic(0);
        if (!defined("ZM_WORKER_NUM")) define("ZM_WORKER_NUM", 1);
        for($i = 0; $i < ZM_WORKER_NUM; ++$i) {
            self::$atomics["_#worker_".$i] = new Atomic(0);
        }
        for ($i = 0; $i < 10; ++$i) {
            self::$atomics["_tmp_" . $i] = new Atomic(0);
        }
        self::$atomics["ss"] = new Atomic(1);
    }


}
