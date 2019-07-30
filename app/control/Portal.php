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
use wmi\lib\MysqlSwoole;

class Portal extends Control {

    public function index() {

        go(function () {
            try {
                $mysql = PoolManager::pop("mysql");
                $mysql->exec("UPDATE user SET loginIp = ? WHERE id = ?", ['good', 1]);
            } catch (\Throwable $e) {

            } finally {
                PoolManager::push($mysql);
            }
        });

        PoolManager::mysql(function ($mysql) {
            $id = $mysql->getData("SELECT id,name FROM user WHERE  name like ?l", ['123']);
        });

        return "123";
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