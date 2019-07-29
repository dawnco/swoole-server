<?php
/**
 * @author WhoAmI
 * @date   2019-07-25
 */

namespace wmi\core;


use wmi\lib\Log;

class Error {

    public function __construct() {
        $this->init();
    }

    public function init() {
        $this->errorHandle();
    }

    public function errorHandle() {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $str = "$errstr, $errfile [line $errline]";
            Log::error($str);
        });
    }
}