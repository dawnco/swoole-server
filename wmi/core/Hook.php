<?php

namespace wmi\core;

use wmi\contract\BaseTract;
use wmi\core\traits\SingleTrait;

/**
 * 钩子类
 * 加载钩子和执行钩子
 * @author Dawnc <abke@qq.com>
 * @date   2015-08-30
 */
class Hook extends BaseTract {

    public function __construct($request, $response) {
        parent::__construct($request, $response);

        $hooks = Conf::file("hook");
        foreach ($hooks as $preg => $hook) {
            if (preg_match("#^$preg$#i", $request->uri)) {

                $handle = new $hook['h']($request, $response);
                $this->addAction($hook['weld'], [
                    $handle, $hook['m'] ?? "hook"
                ], $hook['seq'] ?? 10, $hook['p'] ?? []);
            }
        }
    }


    /**
     * @param type $name 名称
     * @param type $callback
     * @param type $seq  按升序
     * @param type $parameter
     */
    public function addAction($name, $callback, $seq = 10, $parameter = []) {
        $this->__setCallbacks("action", $name, [
                "callback"  => $callback,
                "seq"       => $seq,
                "parameter" => $parameter,
            ]
        );
    }

    /**
     * 执行action  结果有false 则返回false
     * @param type $name
     * @param type $parameter
     */
    public function doAction($name, $parameter = []) {
        foreach ($this->__getCallbacks("action", $name) as $k => $c) {
            //执行
            call_user_func_array($c['callback'], array_merge($c['parameter'], $parameter));
        }

    }

    public function addFilter($name, $callback, $seq = 10, $parameter = []) {
        $this->__setCallbacks("filter", $name, [
            "callback"  => $callback,
            "seq"       => $seq,
            "parameter" => $parameter,
        ]);
    }

    /**
     * 执行过滤
     * @param type $name
     * @param type $value
     * @return type
     */
    public function applyFilter($name, $value, $parameter = []) {
        foreach ($this->__getCallbacks("filter", $name) as $k => $c) {
            //执行
            $value = call_user_func_array($c['callback'], array_merge([$value], $c['parameter'], $parameter));
        }
        return $value;
    }

    /**
     * @param type $type
     * @param type $name
     * @return type
     */
    private function __getCallbacks($type, $name) {

        //没有钩子
        if (!isset($this->__callbacks[$type][$name])) {
            return [];
        }
        $callbacks = $this->__callbacks[$type][$name];

        usort($callbacks, array(__NAMESPACE__ . "\\Hook", "sort"));
        return $callbacks;
    }

    /**
     * @param type $type
     * @param type $name
     * @param type $callbacks
     */
    private function __setCallbacks($type, $name, $callbacks) {
        if (!isset($this->__callbacks[$type])) {
            $this->__callbacks[$type] = [];
        }

        if (!isset($this->__callbacks[$type][$name])) {
            $this->__callbacks[$type][$name] = [];
        }


        $this->__callbacks[$type][$name][] = $callbacks;
    }

    /**
     * 按升序排列
     * @param type $a
     * @param type $b
     * @return int
     */
    public function sort($a, $b) {
        if ($a['seq'] == $b['seq']) {
            return 0;
        }
        return $a['seq'] > $b['seq'] ? 1 : -1; // 按升序排列
    }


}
