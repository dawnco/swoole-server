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
use wmi\lib\database\Mysql;
use wmi\lib\Helper;
use wmi\lib\Log;

class Portal extends Control {
    public function index() {
        $mysql = Helper::database();
        $mysql->getData("SELECT * FROM cashlogs_201907 WHERE outSn = ?", ['SPK20190709184328641881']);
        return posix_getpid();
    }

    public function id($redis, $time = 0) {

        $redis = Helper::redis();

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