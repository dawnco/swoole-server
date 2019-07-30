<?php
/**
 * @author WhoAmI
 * @date   2019-07-30
 */

namespace app\control;


use wmi\contract\Control;
use wmi\core\PoolManager;

class StatusControl extends Control {

    public function index() {
        $status = PoolManager::status();
        $str    = <<<EOT
<pre>
MySQL links : {$status['mysql']}
MySQL borrowed : {$status['mysqlBorrowed']}
Redis links : {$status['redis']}
Redis borrowed : {$status['redisBorrowed']}
</pre>
EOT;

        return $str;
    }
}