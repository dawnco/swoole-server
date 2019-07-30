<?php
/**
 * @author WhoAmI
 * @date   2019-07-23
 */

namespace wmi\core\db;


use Swoole\Coroutine\Redis;

class RedisPool {
    protected $available = true;
    protected $pool;

    protected static $instance = null;

    public function __construct() {
        $this->pool = new \SplQueue;
    }

    public static function getInstance() {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new static();
    }


    public function put($redis) {
        $this->pool->push($redis);
    }

    /**
     * @return bool|mixed|\Swoole\Coroutine\Redis
     */
    public function get() {
        //有空闲连接且连接池处于可用状态
        if ($this->available && count($this->pool) > 0) {
            return $this->pool->pop();
        }

        //无空闲连接，创建新连接
        $redis = new \Redis();
        $res   = $redis->connect(Config::get("redis.host"), Config::get("redis.port"));
        if ($res == false) {
            return false;
        } else {
            return $redis;
        }
    }

    public function size() {
        return $this->pool->count();
    }

    public function destruct() {
        // 连接池销毁, 置不可用状态, 防止新的客户端进入常驻连接池, 导致服务器无法平滑退出
        $this->available = false;
        while (!$this->pool->isEmpty()) {
            $this->pool->pop();
        }
    }
}