<?php

/**
 * -------------------------------------------------
 * redis 工具箱
 * -------------------------------------------------
 * @author 刘健 59208859@qq.com
 * -------------------------------------------------
 */
class Rediskit {

    protected $redis;  // redis对象

	function __construct() {
        $this->redis = new Redis();
        $state = $this->redis->connect('127.0.0.1', 6379, 10);
        if($state==false){
            die('redis connect failure');
        }
    }

    // 设置一条String
    function setString( $key, $text, $expire=null ){
        $key = 'string:'.$key;
        $this->redis->set($key, $text);
        if(!is_null($expire)){
            $this->redis->setTimeout($key, $expire);
        }
    }

    // 获取一条String
    function getString( $key ){
        $key = 'string:'.$key;
        $text = $this->redis->get($key);
        return empty($text)?NULL:$text;
    }

    // 删除一条String
    function delString( $key ){
        $key = 'string:'.$key;
        $this->redis->del($key);
    }

    // 设置一条array
    function setArray( $key, $arr, $expire=null ){
        $key = 'array:'.$key;
        $this->redis->hMset($key, $arr);
        if(!is_null($expire)){
            $this->redis->setTimeout($key, $expire);
        }
    }

    // 获取一条Arrry
    function getArray( $key ){
        $key = 'array:'.$key;
        $arr = $this->redis->hGetAll($key);
        return empty($arr)?NULL:$arr;
    }

    // 删除一条Array
    function delArray( $key ){
        $key = 'array:'.$key;
        $this->redis->del($key);
    }

    // 设置一条hash数据
    function setHash( $table, $id, $arr, $expire=null ) {
        $key = $table.':'.$id;
        $this->redis->hMset($key, $arr);
        if(!is_null($expire)){
            $this->redis->setTimeout($key, $expire);
        }
    }

    // 获取一条hash数据，$fields可为字符或数组
    function getHash( $table, $id, $fields=null ) {
        $key = $table.':'.$id;
        if(is_null($fields)){
            $arr = $this->redis->hGetAll($key);
        }else{
            if(is_array($fields)){
                $arr = $this->redis->hmGet($key, $fields);
            }else{
                $arr = $this->redis->hGet($key, $fields);
            }
        }
        return empty($arr)?NULL:$arr;
    }

    // 删除一条hash
    function delHash( $table, $id ){
        $key = $table.':'.$id;
        $this->redis->del($key);
    }

    // 推送数据给list，头部
    function pushList( $key, $arr ) {
        $key = 'list:'.$key;
        $this->redis->lPush($key, json_encode($arr));
    }

    // 从list拉取一条数据，尾部
    function pullList( $key, $timeout=0 ){
        $key = 'list:'.$key;
        if($timeout>0){
            $val = $this->redis->brPop($key, $timeout);  // 该函数返回的是一个数组, 0=key 1=value
        }else{
            $val = $this->redis->rPop($key);
        }
        $val = is_array($val) && isset($val[1]) ? $val[1] : $val;
        return empty($val)?null:$this->object_to_array(json_decode($val));
    }

    // 取得list的数据总条数
    function getListSize( $key ){
        $key = 'list:'.$key;
        return $this->redis->lSize($key);
    }

    // 删除list
    function delList( $key ){
        $key = 'list:'.$key;
        $this->redis->del($key);
    }

    // 使用递归，将stdClass转array
    protected function object_to_array( $obj ){
        if(is_object($obj)){
            $arr = (array)$obj;
        }
        if(is_array($obj)){
            foreach ($obj as $key=>$value) {
                $arr[$key] = $this->object_to_array($value);
            }
        }
        return !isset($arr)?$obj:$arr;
    }

}
