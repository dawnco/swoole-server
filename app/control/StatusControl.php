<?php
/**
 * @author WhoAmI
 * @date   2019-07-30
 */

namespace app\control;


use wmi\contract\Control;
use wmi\core\Config;
use wmi\core\PoolManager;
use wmi\lib\database\Mysql;
use wmi\lib\database\Redis;


class StatusControl extends Control {

    public function index() {


        $str = '';
        foreach (Config::get('mysql') as $v) {
            $status = PoolManager::status($v['name']);
            $str    .= <<<EOT
<pre>
{$v['name']} links      : {$status['links']}
{$v['name']} available  : {$status['available']}
</pre>
EOT;
        }

        foreach (Config::get('redis') as $v) {
            $status = PoolManager::status($v['name']);
            $str    .= <<<EOT
<pre>
{$v['name']} links     : {$status['links']}
{$v['name']} available : {$status['available']}
</pre>
EOT;
        }


        return $str;
    }
}