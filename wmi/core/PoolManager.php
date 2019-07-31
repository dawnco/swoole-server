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
use wmi\lib\MysqlSwoole;

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

    // 已经借出去的连接
    public $borrowed = ['mysql' => 0, 'redis' => 0];

    protected function mysql($callback) {
        try {
            $mysql = $this->pop("mysql");
            $callback($mysql);
            $this->push($mysql);
        } catch (\Exception $e) {
            if (isset($mysql) && $mysql) {
                $this->push($mysql);
            }
            throw $e;
        }
    }

    protected function redis($callback) {
        try {
            $link = $this->pop("redis");
            $callback($link);
            $this->push($link);
        } catch (\Exception $e) {
            if (isset($link) && $link) {
                $this->push($link);
            }
            throw $e;
        }
    }

    protected function pop($name) {
        switch ($name) {
            case "mysql":
                $pool = $this->getConnectionPool("mysql");
                $link = $pool->borrow();
                $this->borrowed['mysql']++;
                return new MysqlSwoole($link);
            break;
            case "redis":
                $pool = $this->getConnectionPool("redis");
                $link = $pool->borrow();
                $this->borrowed['redis']++;
                return $link;
            break;
        }
        throw new Exception("pop $name 连接池不存在");
    }

    protected function push($link) {
        if ($link instanceof MysqlSwoole) {
            $pool = $this->getConnectionPool("mysql");
            $ret  = $pool->return($link->link);
            $this->borrowed['mysql']--;
            return $ret;
        } elseif ($link instanceof \Redis) {
            $pool = $this->getConnectionPool("redis");
            $ret  = $pool->return($link);
            $this->borrowed['redis']--;
            return $ret;
        }
        throw new Exception("push 连接池不存在");
    }

    protected function close() {
        $this->closeConnectionPools();
    }

    protected function status() {

        // 池子中的连接数量
        $pool                  = $this->getConnectionPool("mysql");
        $data['mysql']         = $pool->getConnectionCount();
        $data['mysqlBorrowed'] = $this->borrowed['mysql'];
        $pool                  = $this->getConnectionPool("redis");
        $data['redis']         = $pool->getConnectionCount();
        $data['redisBorrowed'] = $this->borrowed['redis'];

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