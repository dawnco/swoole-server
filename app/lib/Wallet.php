<?php
/**
 * @author WhoAmI
 * @date   2019-08-01
 */

namespace app\lib;


use wmi\core\Exception;
use wmi\lib\Helper;
use wmi\lib\Log;

class Wallet {
    protected $db   = 0;
    protected $id   = 0;
    protected $info = [];

    public function __construct($id, $db) {
        $this->id = $id;
        $this->db = $db;
    }

    public function getInfo() {
        if (!$this->info) {
            $this->info = $this->db->getLineBy("member", $this->id);
        }
        return $this->info;
    }

    /**
     * 增加余额
     * @param $money
     * @param $item
     * @param $remark
     * @throws Exception
     */
    public function add($money, $item, $remark = '') {
        if ($money <= 0) {
            throw new Exception("金额不能小于0");
        }

        return $this->operate($money, $item, $remark);
    }

    /**
     * 减少余额
     * @param $money
     * @param $item
     * @param $remark
     * @throws Exception
     */
    public function minus($money, $item, $remark = '') {
        if ($money <= 0) {
            throw new Exception("金额不能小于0");
        }
        return $this->operate(-$money, $item, $remark);
    }

    protected function operate($money, $item, $remark) {
        try {
            $this->db->begin();
            $wallet = $this->lock();
            if ($money < 0 && ($wallet + $money < 0)) {
                throw new Exception("余额不足");
            }

            $ret = $this->db->exec("UPDATE member SET wallet = wallet + ? WHERE id = ?", [$money, $this->id]);
            $this->db->commit();
            return $ret;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 冻结
     * @param        $money
     * @param string $remark
     * @throws Exception
     */
    public function freeze($money, $remark = '') {

        if ($money <= 0) {
            throw new Exception("冻结金额不能小于0");
        }

        try {
            $this->db->begin();
            $user = $this->lock();
            if ($user['money'] < $money) {
                throw new Exception("余额不足");
            }

            $this->db->exec("UPDATE SET freeze = freeze + ?,wallet = wallet + ? WHERE id = ?", [$money, $money, $this->id]);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * 解冻
     * @param        $money
     * @param string $remark
     * @throws Exception
     */
    public function unfreeze($money, $remark = '') {

        if ($money <= 0) {
            throw new Exception("解冻金额不能小于0");
        }

        try {
            $this->db->begin();
            $user = $this->lock();
            if ($user['freeze'] < $money) {
                throw new Exception("已冻结不足");
            }

            $this->db->exec("UPDATE SET freeze = freeze - ?, wallet = wallet + ? WHERE id = ?", [$money, $money, $this->id]);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * @param bool $available true可用金额 false 全部
     * @return mixed
     */
    public function balance($available = true) {
        if ($available) {
            return $this->db->getVar("SELECT wallet - freeze FROM member WHERE id =?", [$this->id]);
        } else {
            return $this->db->getVar("SELECT wallet FROM member WHERE id =?", [$this->id]);

        }
    }

    protected function lock() {
        return $this->db->getVar("SELECT wallet FROM member WHERE id = ? FOR UPDATE", [$this->id]);
    }

}