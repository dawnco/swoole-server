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
        'daemonize'  => false,
    ],
    'redis'  => [
        'default' => [
            'name'     => 'redis',  //连接池名称
            'class'    => \wmi\lib\database\Redis::class,
            'host'     => '127.0.0.1',
            'port'     => '6379',
            'database' => 0,
            'pool_max' => 4,
            'pool_min' => 2,
        ]
    ],
    'mysql'  => [
        'default' => [
            'name'     => 'mysql', //连接池名称
            'class'    => \wmi\lib\database\Mysql::class,
            'host'     => '192.168.0.11',
            'port'     => '3306',
            'user'     => 'root',
            'database' => 'redpacket',
            'password' => 'Q,Fflgfye6w.',
            'timeout'  => 10,
            'charset'  => 'utf8mb4',
            'pool_max' => 4,
            'pool_min' => 2,
        ]
    ],
];