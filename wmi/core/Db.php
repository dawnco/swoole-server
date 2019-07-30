<?php

namespace wmi\core;

/**
 * 数据库抽象类
 * 扩展数据库继承该类
 * @author Dawnc <abke@qq.com>
 * @date   2013-11-23
 */
abstract class Db {

    /**
     * @var 执行过的sql
     */
    public $sql = null;

    /**
     * 根据条件拼接sql where片段
     * 主要解决前台可选一项或多项条件进行查询时的sql拼接
     * 拼接规则：
     * 's'=>sql，必须，sql片段
     * 'v'=>值缩写，必须，sql片段中要填充的值
     * 'c'=>条件，选填，默认判断不为空，如果设置了条件则用所设置的条件
     * $factor_list = array(
     *        array('s'=>'and a.id=?i', 'v'=>12 ),
     *        array('s'=>"and a.name like '%?p'", 'v'=>'xing'),
     *        array('s'=>'and a.age > ?i', 'v'=>18),
     *        array('s'=>'or (a.time > ?s and a.time < ?s )', 'v'=>array('2014', '2015'), 'c'=>(1==1) )
     * );
     * @param array $factor_list
     * @return string
     */
    public function where($factor_list) {
        $where_sql = ' 1=1';
        foreach ($factor_list as $factor) {
            $condition = isset($factor['c']) ? $factor['c'] : $factor['v'];
            if ($condition) {
                $where_sql .= " " . $this->prepare($factor['s'], $factor['v']);
            }
        }
        return $where_sql;
    }

    /**
     * 预编译sql语句 ?i = 表示int
     *              ?s 和 ? 字符串
     *              ?p 原始sql
     *              ?lr = like 'str%' ?ll = like '%str' l = like '%str%'
     *              sql id IN (1,2 3) 用法   ("id IN (?)", [[1,2,3]]);
     * @param string       $query
     * @param array|string $data
     * @return string
     */
    public function prepare($query, $data = null) {
        if ($data === null) {
            return $query;
        } elseif (!is_array($data)) {
            $data  = func_get_args();
            $query = array_shift($data);
        }

        $query = str_replace(
            ['?lr', '?ll', '?l', '?i', '?s', '?p', '?'],
            ['"%s%%"', '"%%%s"', '"%%%s%%"', '%d', '"%s"', '%s', '"%s"'],
            $query);

        foreach ($data as $k => $v) {
            $data[$k] = $this->_escape($v);
        }
        return vsprintf($query, $data);
    }

    /**
     * @param array|string $val
     * @return string
     * @author  Dawnc
     */
    protected function _escape($val) {

        if (is_array($val)) {
            $fd = array_map(function ($v) {
                return $this->escape($v);
            }, $val);
            return implode('","', $fd);
        } else {
            return $this->escape($val);
        }
    }

    /**
     * 获取一个值
     * @param string $query
     * @param array  $bind 预定义参数
     */
    abstract function getVar($query, $bind = null);

    /**
     * 获取一行数据
     * @param string $query
     * @param array  $bind 预定义参数
     */
    abstract function getLine($query, $bind = null);

    /**
     * 获取数据
     * @param string $query
     * @param array  $bind 预定义参数
     * @return array
     */
    abstract function getData($query, $bind = null);

    /**
     * 简单分页数据
     * @param string $table
     * @param array  $where
     * @param int    $page
     * @param int    $size
     * @param string $order
     * @param string $fields
     * @return array
     * @author  Dawnc
     */
    abstract function getPageData($table, $where = [], $page = 1, $size = 15, $order = "id DESC", $fields = '*');

    /**
     * 快捷查询
     * @param string $table
     * @param string $value
     * @param string $index
     * @param string $field
     */
    abstract function getLineBy($table, $value, $index = "id", $field = "*");

    /**
     * 快捷查询
     * @param string $table
     * @param array  $where 数组
     * @param string $field
     * @return mixed
     * @author  Dawnc
     */
    abstract function getLineByWhere($table, $where, $field = "*");


    /**
     * 插入
     * @param string $table
     * @param array  $data
     * @return type
     */
    abstract function insert($table, $data);

    /**
     * 更新或者添加一条数据
     * @param string $table
     * @param array  $data
     * @param string $value
     * @param string $field
     * @return type
     */
    abstract function upsert($table, $data, $value, $field = "id");

    /**
     * 批量插入
     * @param string $table
     * @param array  $data
     */
    abstract function insertBatch($table, $data);

    /**
     * 批量插入(忽略重复索引)
     * @param string $table
     * @param array  $data
     */
    abstract function insertIgnoreBatch($table, $data);

    /**
     * 更新sql
     * @param string $table
     * @param array  $data
     * @param mix    $where 数组 或者 字符串  字符串则表示ID
     * @return type
     */
    abstract function update($table, $data, $where);

    /**
     * 删除
     * @param string           $table 表名
     * @param string|array|int $where 条件 或者 字符串  字符串则表示ID
     */
    abstract function delete($table, $where);

    /**
     * 转义安全字符
     * @param string $val
     * @return string
     */
    abstract function escape($val);

    /**
     * 执行sql
     * @param type $query
     * @param type $bind
     * @return boolean
     */
    abstract function exec($query, $bind = null);

    /**
     * 开始事物
     */
    abstract function begin();

    /**
     * 提交
     */
    abstract function commit();

    /**
     * 回滚
     */
    abstract function rollback();
}
