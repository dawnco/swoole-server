<?php
/**
 * @author WhoAmI
 * @date   2019-08-01
 */

namespace app\control;


use app\lib\Wallet;
use wmi\contract\Control;
use wmi\lib\Helper;

class WalletControl extends Control {

    public function index() {
        $db     = Helper::database();
        $wallet = new Wallet(1003, $db);
        $r      = $wallet->add(100, 'item1');
        return $r;
    }
}