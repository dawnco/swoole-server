<?php
/**
 * @author WhoAmI
 * @date   2019-07-25
 */

namespace app\hook;


use wmi\contract\Hook;
use wmi\core\Exception;

class AuthHook extends Hook {
    public function hook() {
        if (false) {
            throw new Exception("没有登录", 401);
        }
    }
}