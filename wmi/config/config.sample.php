<?php
/**
 * @author WhoAmI
 * @date   2019-07-23
 */

return [
    "app"   => [
        'host' => "0.0.0.0",
        "port" => "9999",
    ],
    "redis" => [
        'host'     => "127.0.0.1",
        "port"     => "6379",
        "database" => 0,
        "pool"     => [
            "max" => 20,
            "min" => 10,
        ]
    ],
    "mysql" => [
        'host'     => "127.0.0.1",
        "port"     => "3306",
        "user"     => "root",
        "database" => "redpacket",
        "password" => 'Q,Fflgfye6w.',
        "timeout"  => 10,
        'charset'  => 'utf8mb4',
        "pool"     => [
            "max" => 20,
            "min" => 10,
        ]
    ],
];