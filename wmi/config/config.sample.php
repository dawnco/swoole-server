<?php
/**
 * @author WhoAmI
 * @date   2019-07-23
 */

return [
    'server' => [
        'host'       => '0.0.0.0',
        'port'       => '9999',
        'log_level'  => 'debug',
        'worker_num' => 1,
        'daemonize'  => true,
        'user'       => 'www',
        'group'      => 'www',
    ],
    'redis'  => [
        'default' => [
            'name'              => 'redis',  //连接池名称
            'class'             => \wmi\lib\database\Redis::class,
            'host'              => '127.0.0.1',
            'port'              => '6379',
            'database'          => 0,
            'poolMax'           => 40,
            'poolMin'           => 20,
            // 可以修改 timeout 和 tcp-keepalive 设置
            // redis 默认是不关闭链接的
            'maxIdleTime'       => 3600, //链接保存时长
            'idleCheckInterval' => 600, //链接检查周期
        ],
    ],
    'mysql'  => [
        'default' => [
            'name'              => 'mysql', //连接池名称
            'class'             => \wmi\lib\database\Mysql::class,
            'host'              => '192.168.0.11',
            'port'              => '3306',
            'user'              => 'root',
            'database'          => 'redpacket',
            'password'          => 'Q,Fflgfye6w.',
            'timeout'           => 10,
            'charset'           => 'utf8mb4',
            'poolMax'           => 40,
            'poolMin'           => 20,
            //需要设置  mysql的   wait_timeout 和 interactive_timeout
            'maxIdleTime'       => 3600, //链接保存时长
            'idleCheckInterval' => 600, //链接检查周期
        ],
    ],
];