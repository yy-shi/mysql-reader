<?php
/**
 * 基于cookie存储的闪存
 */
class flash{
    
    public static function add($key, $value){
        $key = 'f-'.$key;
        $_SESSION[$key]= $value;
    }

    public static function get($key){
        $key = 'f-'.$key;
        if(isset($_SESSION[$key])){
           $value = $_SESSION[$key];
           unset($_SESSION[$key]);
           return $value;
        }
    }
}
