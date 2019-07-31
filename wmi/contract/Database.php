<?php
/**
 * @author WhoAmI
 * @date   2019-07-31
 */

namespace wmi\contract;


use wmi\core\PoolManager;
use wmi\lib\Log;

class Database {

    protected $poolName = '';

    protected $link;

    public function __construct($poolName = null) {

        if ($poolName) {
            $this->poolName = $poolName;
        }

        $this->link = PoolManager::pop($this->poolName);

    }

    /**
     * 放链接到池子中
     * @param string $conf
     * @param string $type
     */
    public function release() {
        if ($this->link) {
            PoolManager::push($this->poolName, $this->link);
            $this->link = null;
        }
    }

    public function __destruct() {
        $this->release();
    }
}