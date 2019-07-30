<?php
/**
 * @author WhoAmI
 * @date   2019-07-23
 */

return [
    "worker_num" => 5,
    "app"        => [
        'host'      => "0.0.0.0",
        "port"      => "9999",
        'log_level' => 'debug',
    ],
    "redis"      => [
        'host'     => "127.0.0.1",
        "port"     => "6379",
        "database" => 0,
        "pool"     => [
            "max" => 4,
            "min" => 2,
        ]
    ],
    "mysql"      => [
        'host'     => "192.168.0.11",
        "port"     => "3306",
        "user"     => "root",
        "database" => "redpacket",
        "password" => 'Q,Fflgfye6w.',
        "timeout"  => 10,
        'charset'  => 'utf8mb4',
        "pool"     => [
            "max" => 4,
            "min" => 2,
        ]
    ],
];