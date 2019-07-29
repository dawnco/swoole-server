<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

return [
    ".*"       => ['weld' => 'pre_control', 'h' => app\hook\TestHook::class, 'm' => 'hook'],
    "admin/.*" => ['weld' => 'pre_control', 'h' => app\hook\AuthHook::class, 'm' => 'hook'],
];