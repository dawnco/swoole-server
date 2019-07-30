<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

namespace wmi\core;


/**
 * URL 路由
 * @author  Dawnc
 * @date    2013-11-25
 */
class Route {

    private $__control = "",
        $__method = "index",
        $__param = array(),
        $__uri = null;

    public function __construct(Request $request, Response $response) {
        $this->__uri = $request->uri;
        $this->resolve();
    }

    /**
     * 执行路由
     */
    public function resolve() {
        $routed = false;
        $rules  = Config::file("url");

        //是否配置过路由
        foreach ($rules as $muri => $rule) {
            $matches = array();
            if (preg_match("#^$muri$#", $this->__uri, $matches)) {
                $this->__prase($rule, $matches);
                $routed = true;
                break;
            }
        }

        $control         = APP_NAME . "\\control\\" . str_replace(array("/", DIRECTORY_SEPARATOR), "\\", $this->__control);
        $this->__control = $control;


        if (!$routed) {
            throw new Exception("No Route For URI : " . $this->__uri, 404);
        }

        //默认路由
        //        if(!$routed){
        //           $info                    = preg_split("/[-\/]/", $this->__uri);
        //           $this->__control         = (isset($info[0]) && $info[0]) ? $info[0] : "Default";
        //           $this->__method          = (isset($info[1]) && $info[1]) ? $info[1] : "index";
        //           $this->__param           = array_slice($info, 2);
        //        }
    }

    /**
     * 解析uri
     * @param type $rule
     * @param type $matches
     */
    private function __prase($rule, $matches = array()) {
        $this->__control = $rule['c'];
        $this->__method  = isset($rule['m']) ? $rule['m'] : $this->__method;

        $url_param = array_slice($matches, 1);
        //合并参数
        $prarm = array();
        if (isset($rule['p'])) {
            $prarm = array_merge((array)$rule['p'], $url_param);
        } else {
            $prarm = $url_param;
        }
        $this->__param = $prarm;
    }

    public function setUri($uri) {
        $this->__uri = $uri;
    }

    public function getGroup() {
        return $this->__group;
    }

    public function getControl() {
        return $this->__control;
    }

    public function getMethod() {
        return $this->__method;
    }

    public function getParam() {
        return $this->__param;
    }

    public function getUri() {
        return $this->__uri;
    }

}

