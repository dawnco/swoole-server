<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\core;

use Smf\ConnectionPool\ConnectionPool;
use Smf\ConnectionPool\ConnectionPoolTrait;
use Smf\ConnectionPool\Connectors\CoroutineMySQLConnector;
use Smf\ConnectionPool\Connectors\PhpRedisConnector;


class PoolManager {

    protected static $_instance = null;

    use ConnectionPoolTrait;

    protected function init() {

        $config = Config::get('mysql');
        foreach ($config as $c) {
            $this->_initMysql($c);
        }

        $config = Config::get('redis');
        foreach ($config as $c) {
            $this->_initRedis($c);
        }
    }

    protected function _initMysql($config) {
        // 所有的MySQL连接数区间：[4 workers * 2 = 8, 4 workers * 10 = 40]
        $pool = new ConnectionPool(
            [
                'minActive'         => $config["poolMin"],
                'maxActive'         => $config["poolMax"],
                'maxIdleTime'       => $config['maxIdleTime'],
                'idleCheckInterval' => $config['idleCheckInterval'],
            ],
            new CoroutineMySQLConnector,
            [
                'host'        => $config["host"],
                'port'        => $config["port"],
                'user'        => $config["user"],
                'password'    => $config["password"],
                'database'    => $config["database"],
                'timeout'     => $config["timeout"],
                'charset'     => $config["charset"],
                'strict_type' => true,
                'fetch_mode'  => true,
            ]);
        $pool->init();
        $this->addConnectionPool($config['poolName'], $pool);
    }

    protected function _initRedis($config) {
        // 所有Redis连接数区间：[4 workers * 5 = 20, 4 workers * 20 = 80]
        $pool = new ConnectionPool(
            [
                'minActive' => $config["poolMin"],
                'maxActive' => $config["poolMax"],
            ],
            new PhpRedisConnector,
            [
                'host'     => $config["host"],
                'port'     => $config["port"],
                'database' => $config["database"],
                'password' => $config["password"] ?? null,
            ]);
        $pool->init();
        $this->addConnectionPool($config['poolName'], $pool);
    }

    /**
     * @param $name
     * @return Database
     * @throws Exception
     * @throws \Smf\ConnectionPool\BorrowConnectionTimeoutException
     */
    protected function pop($name) {
        $pool = $this->getConnectionPool($name);
        $link = $pool->borrow();
        return $link;
    }

    protected function push($name, $link) {
        $pool = $this->getConnectionPool($name);
        $ret  = $pool->return($link);
        return $ret;
    }

    protected function close() {
        $this->closeConnectionPools();
    }

    protected function status($name) {
        // 池子中的连接数量
        $pool = $this->getConnectionPool($name);
        $data = [
            'links'     => $pool->getConnectionCount(), // 总共的连接数
            'available' => $pool->getIdleCount(), // 可用的连接数
        ];
        return $data;
    }

    public static function __callStatic($name, $arguments) {

        if (!static::$_instance) {
            static::$_instance = new static();
        }

        if (method_exists(static::$_instance, $name)) {
            return static::$_instance->$name(...$arguments);
        } else {
            throw new Exception("Method Not Found");
        }
    }
}