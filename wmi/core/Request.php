<?php

namespace wmi\core;

use wmi\lib\Log;

/**
 * @author: 五马石 <abke@qq.com>
 * Time: 2013-8-11
 * Description:
 */
class Request {

    public $post;
    public $get;
    public $request;
    public $cookie;
    public $uri;
    public $fd;

    public function __construct(\Swoole\Http\Request $request) {

        $this->fd        = $request->fd;
        $this->post      = $request->post;
        $this->get       = $request->get;
        $this->request   = $request->request;
        $this->cookie    = $request->cookie;
        $uri             = $request->server['path_info'];
        if ($uri == '/') {
            $this->uri = "portal";
        } else {
            $this->uri = trim($uri, " /");
        }
    }
}
