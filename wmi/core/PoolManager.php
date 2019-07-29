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

use Swoole\Coroutine\Redis;
use wmi\core\traits\SingleTrait;
use wmi\lib\Log;
use wmi\lib\Mysql;

class PoolManager {

    protected static $_instance = null;

    use ConnectionPoolTrait;

    protected function init() {
        // 所有的MySQL连接数区间：[4 workers * 2 = 8, 4 workers * 10 = 40]
        $pool1 = new ConnectionPool(
            [
                'minActive' => Config::get("mysql.pool.min"),
                'maxActive' => Config::get("mysql.pool.max"),
            ],
            new CoroutineMySQLConnector,
            [
                'host'        => Config::get("mysql.host"),
                'port'        => Config::get("mysql.port"),
                'user'        => Config::get("mysql.user"),
                'password'    => Config::get("mysql.password"),
                'database'    => Config::get("mysql.database"),
                'timeout'     => Config::get("mysql.timeout"),
                'charset'     => Config::get("mysql.charset"),
                'strict_type' => true,
                'fetch_mode'  => true,
            ]);
        $pool1->init();
        $this->addConnectionPool('mysql', $pool1);

        // 所有Redis连接数区间：[4 workers * 5 = 20, 4 workers * 20 = 80]
        $pool2 = new ConnectionPool(
            [
                'minActive' => Config::get("redis.pool.min"),
                'maxActive' => Config::get("redis.pool.max"),
            ],
            new PhpRedisConnector,
            [
                'host'     => Config::get("redis.host"),
                'port'     => Config::get("redis.port"),
                'database' => Config::get("redis.database"),
                'password' => Config::get("redis.password"),
            ]);
        $pool2->init();
        $this->addConnectionPool('redis', $pool2);
    }

    protected function mysql($callback) {
        try {
            $pool = $this->getConnectionPool("mysql");
            $link = new Mysql($pool->borrow());
            $callback();
            $pool->return($link);
        } catch (\Exception $e) {
            if ($pool && $link->link) {
                $pool->return($link->link);
            }
            throw $e;
        }
    }

    protected function redis($callback) {
        try {
            $pool = $this->getConnectionPool("redis");
            $link = $pool->borrow();
            $callback($link);
            $pool->return($link);
        } catch (\Exception $e) {
            if ($pool && $link) {
                $pool->return($link);
            }
            throw $e;
        }
    }

    protected function pop($name) {
        switch ($name) {
            case "mysql":
                $pool = $this->getConnectionPool("mysql");
                $link = $pool->borrow();
                return new Mysql($link);
            break;
            case "redis":
                $pool = $this->getConnectionPool("redis");
                $link = $pool->borrow();
                return $link;
            break;
        }
        throw new Exception("pop $name 连接池不存在");
    }

    protected function push($link) {
        if ($link instanceof Mysql) {
            $pool = $this->getConnectionPool("mysql");
            $ret  = $pool->return($link->link);
            unset($link);
            return $ret;
        } elseif ($link instanceof \Redis) {
            $pool = $this->getConnectionPool("redis");
            return $pool->return($link);
        }
        throw new Exception("push 连接池不存在");
    }

    protected function close() {
        $this->closeConnectionPools();
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