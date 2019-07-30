<?php

namespace wmi\lib;

use wmi\core\Db;
use wmi\core\Exception;


class MysqlSwoole extends Db {

    public $link  = null;
    public $error = [];
    public $sql   = null;

    public function __construct($link) {
        $this->link = $link;
    }

    /**
     * 获取一行数据
     * @param type $query
     * @param type $bind
     * @return boolean
     */
    public function getLine($query, $bind = null) {

        $query = $this->prepare($query, $bind);
        $stmt  = $this->__exec($query);
        if (!$stmt) {
            return false;
        }
        $row = $stmt->fetch();
        return $row;
    }

    /**
     * 快捷查询
     * @param string $table
     * @param string $value
     * @param string $index
     * @param string $field
     */
    public function getLineBy($table, $value, $index = "id", $field = "*") {
        $query = "SELECT $field FROM `$table` WHERE `$index` = ?s ";
        return $this->getLine($this->prepare($query, array($value)));
    }

    public function getLineByWhere($table, $where, $field = "*") {

        $_where = [];

        foreach ($where as $k => $v) {
            $_where[] = [
                "s" => "AND `$k` = ?s", "v" => $v,
            ];
        }

        $where_sql = $this->where($_where);
        $query     = "SELECT $field FROM `$table` WHERE $where_sql LIMIT 1";
        return $this->getLine($query);
    }

    /**
     * 获取一个值
     * @param type $query
     * @param type $bind
     * @return type
     */
    public function getVar($query, $bind = null) {
        $query = $this->prepare($query, $bind);
        $line  = $this->getLine($query);
        return $line ? array_shift($line) : false;
    }

    /**
     * 获取数据
     * @param type $query
     * @param type $bind
     * @return array
     */
    public function getData($query, $bind = null) {
        $data = array();

        $query = $this->prepare($query, $bind);
        $stmt  = $this->__exec($query);
        if (!$stmt) {
            return false;
        }

        return $stmt->fetchAll();
    }

    /**
     * 插入sql
     * @param type $table
     * @param type $data
     * @return type
     */
    public function insert($table, $data) {
        $insert_fileds = array();
        $insert_data   = array();
        foreach ($data as $field => $value) {
            array_push($insert_fileds, "`{$field}`");
            array_push($insert_data, '"' . $this->escape($value) . '"');
        }
        $insert_fileds = implode(', ', $insert_fileds);
        $insert_data   = implode(', ', $insert_data);
        $query         = "INSERT INTO `{$table}` ({$insert_fileds}) values ({$insert_data});";
        $result        = $this->__exec($query);

        if ($result) {
            return $this->link->insert_id;
        }

        return $result;
    }

    /**
     * 更新或者添加一条数据
     * @param type $table
     * @param type $data
     * @param type $value
     * @param type $field
     * @return type
     */
    public function upsert($table, $data, $value, $field = "id") {
        if ($value && $this->getVar("SELECT id FROM `$table` WHERE `$field` = ?s", $value)) {
            return $this->update($table, $data, array($field => $value));
        } else {
            return $this->insert($table, $data);
        }
    }

    /**
     * 批量插入
     * @param type $table
     * @param type $data
     */
    public function insertBatch($table, $data) {
        $insert_fileds = array();
        foreach ($data as $value) {
            foreach ($value as $field => $row) {
                array_push($insert_fileds, "`{$field}`");
            }
            break;
        }
        $insert_fileds = implode(', ', $insert_fileds);

        foreach ($data as $field => $value) {
            $insert_data = array();
            foreach ($value as $row) {
                array_push($insert_data, '"' . $this->escape($row) . '"');
            }
            $insert_data_str[] = "(" . implode(', ', $insert_data) . ")";
        }

        $query  = "INSERT INTO `{$table}` ({$insert_fileds}) values " . implode(",", $insert_data_str) . ";";
        $result = $this->__exec($query);
        return $result;
    }

    /**
     * 批量插入(忽略重复索引)
     * @param type $table
     * @param type $data
     */
    public function insertIgnoreBatch($table, $data) {
        $insert_fileds = array();
        foreach ($data as $value) {
            foreach ($value as $field => $row) {
                array_push($insert_fileds, "`{$field}`");
            }
            break;
        }
        $insert_fileds = implode(', ', $insert_fileds);


        foreach ($data as $field => $value) {
            $insert_data = array();
            foreach ($value as $row) {
                array_push($insert_data, '"' . $this->escape($row) . '"');
            }
            $insert_data_str[] = "(" . implode(', ', $insert_data) . ")";
        }

        $query  = "INSERT IGNORE INTO `{$table}` ({$insert_fileds}) values " . implode(",", $insert_data_str) . ";";
        $result = $this->__exec($query);
        return $result;
    }

    /**
     * 简单分页数据
     * @param        $table
     * @param array  $where
     * @param int    $page
     * @param int    $size
     * @param string $order
     * @param string $fields
     * @return array
     * @author  Dawnc
     */
    public function getPageData($table, $where = [], $page = 1, $size = 10, $order = "id DESC", $fields = '*') {

        $sql_where = $this->where($where);
        $total     = $this->getVar("SELECT count(*) FROM `$table` WHERE " . $sql_where);

        $size  = abs($size) ?: 1;
        $start = abs(($page ?: 1) - 1) * $size;

        $total_page = ceil($total / $size);

        $data['total'] = (int)$total;
        $data['page']  = (int)$page;
        $data['ended'] = $total_page ? ($total_page == $page) : true;

        $entries = [];

        $query = "SELECT {$fields} FROM `$table` WHERE $sql_where ORDER BY $order LIMIT $start, $size";
        $stmt  = $this->__exec($query);
        if ($stmt) {
            $entries = $stmt->fetchAll();
        }

        $data['entries'] = $entries;

        return $data;

    }

    /**
     * 更新sql
     * @param type $table
     * @param type $data
     * @param type $where
     * @return type
     */
    public function update($table, $data, $where) {
        $update_data  = array();
        $update_where = array();
        foreach ($data as $field => $value) {
            array_push($update_data, sprintf('`%s` = "%s"', $field, $this->escape($value)));
        }
        $update_data = implode(', ', $update_data);

        if (is_array($where)) {
            foreach ($where as $field => $value) {
                array_push($update_where, sprintf('`%s` = "%s"', $field, $this->escape($value)));
            }
            $update_where = 'WHERE ' . implode(' AND ', $update_where);
        } elseif (is_numeric($where)) {
            $update_where = 'WHERE ' . $this->prepare("id = ?i", $where);
        } else {
            throw new Exception("Db Not Specified Where", 500);
        }
        $query = "UPDATE `{$table}` SET {$update_data} {$update_where}";

        return $this->__exec($query);
    }

    public function delete($table, $where) {

        if (is_array($where)) {
            $delete_where = array();
            foreach ($where as $field => $value) {
                array_push($delete_where, sprintf('`%s` = "%s"', $field, $this->escape($value)));
            }
            $delete_where = 'WHERE ' . implode(' AND ', $delete_where);
        } elseif (is_numeric($where)) {
            $delete_where = 'WHERE ' . $this->prepare("id = ?i", $where);
        } else {
            throw new Exception("Db Not Specified Where", 500);
        }

        $query = "DELETE FROM `$table` $delete_where";
        return $this->__exec($query);
    }

    /**
     * 执行sql
     * @param type $query
     * @return boolean
     */
    private function __exec($query) {
        $start = microtime(true);

        $stmt = $this->link->prepare($query);
        if ($stmt === false) {
            $this->throwError($sql);
        }
        $result = $stmt->execute();
        if ($result === false) {
            $this->throwError($sql);
        }

        $end         = microtime(true);
        $this->sql[] = "[" . substr(($end - $start) * 1000, 0, 5) . "ms] " . $query;
        return $stmt;
    }

    /**
     * 执行sql
     * @param type $query
     * @param type $bind
     * @return boolean
     */
    public function exec($query, $bind = null) {
        $query  = $this->prepare($query, $bind);
        $result = $this->__exec($query);
        return $result;
    }

    /**
     * 转义安全字符
     * @param type $val
     * @return type
     */
    public function escape($val) {
        return $this->link->escape($val);
    }

    /**
     * 关闭数据库
     * @param string $conf
     * @param string $type
     */
    public function close() {
    }

    /**
     * 开启事物
     * @return type
     */
    public function begin() {
        $this->sql[] = "begin";
        $ret         = $this->link->begin();
        if ($ret === false) {
            $this->throwError();
        }
    }

    /**
     * 提交事物
     * @return type
     */
    public function commit() {
        $this->sql[] = "commit";
        $ret         = $this->link->commit();
        if ($ret === false) {
            $this->throwError();
        }
    }

    /**
     * 回滚
     * @return type
     */
    public function rollback() {
        $this->sql[] = "rollback";
        $ret         = $this->link->rollback();
        if ($ret === false) {
            $this->throwError();
        }
    }

    public function throwError($sql) {
        Log::error('数据库错误', [$this->link->errno, $this->link->error, $sql]);
        throw new Exception("数据库错误");
    }

}

