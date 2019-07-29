<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace app\control;


use Swoole\Coroutine\Http\Client;
use wmi\contract\Control;
use wmi\core\PoolManager;
use wmi\core\Request;
use wmi\core\Response;
use wmi\lib\Log;
use wmi\lib\Mysql;

class Portal extends Control {

    public function index() {
        PoolManager::redis(function ($redis) {
            $redis->incr("cbcount");
        });

        go(function () {
            try {
                $mysql = PoolManager::pop("mysql");
                $mysql->exec("UPDATE user SET loginIp = ? WHERE id = ?", ['good', 1]);
            } catch (\Throwable $e) {

            }
        });

        $mysql = PoolManager::pop("mysql");
        $id    = $mysql->getData("SELECT id,name FROM user WHERE  name like ?", ['%3%']);
        PoolManager::push($mysql);
        $this->response->end($id);
        $redis = PoolManager::pop("redis");
        $redis->rpush("list", $id);
        $redis->incr("count");
        PoolManager::push($redis);
    }

    public function id($redis, $time = 0) {

        $time   = $time ?: time();
        $key    = date('s', $time);
        $script = <<<EOT
        
            -- local key = 'inc-id:' .. KEYS[1]
            local key = 'inc-id:' .. ARGV[1]
            local lastKey = 'inc-id:last'
            
            local last = redis.call('get', lastKey)
            
            -- 重置上次记数
            if last ~= false then
                if last ~= key then
                    redis.call('set', last, 0)
                end
            end
            
            local inc = redis.call('incr', key)
            -- 记录这次key
            redis.call('set', lastKey, key)
            
            return inc
            
EOT;

        $sha = $redis->script('load', $script);
        //echo $redis->getLastError();
        $ret = $redis->evalSha($sha, [
            $key
        ]);
        return sprintf("chat-msg-id:%s-%s", $time, $ret);
    }
}