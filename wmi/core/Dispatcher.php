<?php

namespace wmi\core;

use wmi\lib\Log;

/**
 * Description:
 */
class Dispatcher {

    public static function resolve(Request $request, Response $response) {

        try {
            $content = self::dispatch($request, $response);
            if ($content !== null) {
                $response->end($content);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->__toString());
            $response->status($e->getCode());
            $response->end("exception:" . $e->getMessage());
        } catch (\Error $e) {
            Log::error($e->getMessage());
            Log::error($e->__toString());
            $response->status(500);
            $response->end("error:" . $e->getMessage());
        }
    }

    /**
     * 执行
     */
    protected static function dispatch(Request $request, Response $response) {

        $hook    = new Hook($request, $response);
        $route   = new Route($request, $response);
        $control = $route->getControl();
        $method  = $route->getMethod();
        $param   = $route->getParam();

        $hook->doAction("pre_control", $param);

        $content = null;
        if (class_exists($control)) {
            $classInstance = new $control($request, $response);
            if (method_exists($classInstance, $method)) {
                $content = call_user_func_array(array($classInstance, $method), $param);
            } else {
                throw new Exception($control . "->" . $method . "() Method Not Found", 404);
            }
        } else {
            throw new Exception($control . " File Not Found", 404);
        }

        $hook->doAction("after_control", array_merge([$classInstance], $param));
        return $content;

    }

}
