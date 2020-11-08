<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
global $config;

/** bind host */
$config['host'] = '0.0.0.0';

/** bind port */
$config['port'] = 20001;

/** 框架开到公网或外部的HTTP访问链接，通过 DataProvider::getFrameworkLink() 获取 */
$config['http_reverse_link'] = "http://127.0.0.1:" . $config['port'];

/** 框架是否启动debug模式 */
$config['debug_mode'] = false;

/** 存放框架内文件数据的目录 */
$config['zm_data'] = realpath(__DIR__ . "/../") . '/zm_data/';

/** 存放崩溃和运行日志的目录 */
$config['crash_dir'] = $config['zm_data'] . 'crash/';

/** 对应swoole的server->set参数 */
$config['swoole'] = [
    'log_file' => $config['crash_dir'] . 'swoole_error.log',
    'worker_num' => 8,
    'dispatch_mode' => 2,
    'max_coroutine' => 30000,
    //'task_worker_num' => 4,
    //'task_enable_coroutine' => true
];

/** 轻量字符串缓存，默认开启 */
$config['light_cache'] = [
    "status" => true,
    "size" => 2048,                     //最多允许储存的条数（需要2的倍数）
    "max_strlen" => 4096,               //单行字符串最大长度（需要2的倍数）
    "hash_conflict_proportion" => 0.6   //Hash冲突率（越大越好，但是需要的内存更多）
];

/** MySQL数据库连接信息，host留空则启动时不创建sql连接池 */
$config['sql_config'] = [
    'sql_host' => '',
    'sql_port' => 3306,
    'sql_username' => 'name',
    'sql_database' => 'db_name',
    'sql_password' => '',
    'sql_enable_cache' => true,
    'sql_reset_cache' => '0300',
    'sql_options' => [
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ],
    'sql_no_exception' => false,
    'sql_default_fetch_mode' => PDO::FETCH_ASSOC         // added in 1.5.6
];

/** CQHTTP连接约定的token */
$config["access_token"] = "";

/** HTTP服务器固定请求头的返回 */
$config['http_header'] = [
    'X-Powered-By' => 'zhamao-framework',
    'Content-Type' => 'text/html; charset=utf-8'
];

/** HTTP服务器在指定状态码下回复的页面（默认） */
$config['http_default_code_page'] = [
    '404' => '404.html'
];

/** zhamao-framework在框架启动时初始化的atomic们 */
$config['init_atomics'] = [
    //'custom_atomic_name' => 0,  //自定义添加的Atomic
];

/** 终端日志显示等级（0-4） */
$config["info_level"] = 2;

/** 自动保存计时器的缓存保存时间（秒） */
$config['auto_save_interval'] = 900;

/** 上下文接口类 implemented from ContextInterface */
$config['context_class'] = \ZMTest\Mock\Context::class;

/** 静态文件访问 */
$config['static_file_server'] = [
    'status' => false,
    'document_root' => realpath(__DIR__ . "/../") . '/resources/html',
    'document_index' => [
        'index.html'
    ]
];

/** 注册 Swoole Server 事件注解的类列表 */
$config['server_event_handler_class'] = [
    \ZM\Event\ServerEventHandler::class,
];

/** 注册自定义指令的类 */
$config['command_register_class'] = [
    //\Custom\Command\CustomCommand::class
];

/** 服务器启用的外部第三方和内部插件 */
$config['modules'] = [
    'onebot' => true, // QQ机器人事件解析器，如果取消此项则默认为 true 开启状态，否则你手动填写 false 才会关闭
];

return $config;
