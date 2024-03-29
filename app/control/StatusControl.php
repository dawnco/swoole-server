<?php
/**
 * @author WhoAmI
 * @date   2019-07-30
 */

namespace app\control;


use wmi\contract\Control;
use wmi\core\Conf;
use wmi\core\PoolManager;
use wmi\lib\database\Mysql;
use wmi\lib\database\Redis;


class StatusControl extends Control {

    public function index() {


        $str = '';
        foreach (Conf::get('mysql') as $v) {
            $status = PoolManager::status($v['poolName']);
            $str    .= <<<EOT
<pre>
{$v['poolName']} links      : {$status['links']}
{$v['poolName']} available  : {$status['available']}
</pre>
EOT;
        }

        foreach (Conf::get('redis') as $v) {
            $status = PoolManager::status($v['poolName']);
            $str    .= <<<EOT
<pre>
{$v['poolName']} links     : {$status['links']}
{$v['poolName']} available : {$status['available']}
</pre>
EOT;
        }


        return $str;
    }
}