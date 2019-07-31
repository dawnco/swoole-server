<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\core;


class Response {

    protected $response;

    public function __construct(\Swoole\Http\Response $response) {
        $this->response = $response;
    }

    public function end($content) {
        $this->response->header('Server', 'WMI-SERVER');
        if (!$content || is_scalar($content)) {
            return $this->response->end($content);
        } else {
            return $this->response->end(json_encode($content));
        }
    }

    public function status($code) {
        return $this->response->status($code);
    }

}