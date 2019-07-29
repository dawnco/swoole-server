<?php

namespace wmi\core;

/**
 * @author  Dawnc
 * @date    2015-05-06
 */
class Exception extends \Exception {
    public function __construct($message = "", $code = 500, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
