<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\contract;


use wmi\core\Request;
use wmi\core\Response;

class BaseTract {

    /**
     * @var Request
     */
    public $request;
    /**
     * @var Response
     */
    public $response;

    public function __construct(Request $request, Response $response) {
        $this->request  = $request;
        $this->response = $response;
    }

    public function __destruct() {

    }
}