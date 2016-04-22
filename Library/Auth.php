<?php
/**
 * 用户身份类
 * @author shiyongyong@chicv.com
 */
class Auth{

    private $_cookieName='cv_udata';
    private $_expire=600;
    private $_data = array();
    private $_cookieObj;
    public  $id='';
    public function __construct(){
        $ucookie = isset($_SESSION[$this->_cookieName])?$_SESSION[$this->_cookieName]:'';
        if($ucookie){
            $this->setData($ucookie);
        }
    }
    public function __get($key){
        if(isset($this->_data[$key])){
            return  $this->_data[$key];
        }
    }
    /**
     * 设置对象数据
     * 并设置cookie
     */
    public function setData($data){
        if($data && $data['id']){
            $this->_data = $data;
            $this->id = $data['id'];
            $_SESSION[$this->_cookieName]=$data;
        }
    }
    public function isLogin(){
        return !empty($this->id);
    }
    public function login($username, $password){
        $ldap = new Ldap();
        $data = $ldap->checkUser($username, $password);
        if(empty($data)){
            return false;
        }
        $this->setData($data);
        return $this->isLogin();     
    }
    /**
     *获取当前登录名称
     */
    public function getUserName(){
        if($this->username){
            return $this->username;
        }
        if($this->email){
            return $this->email;
        }
        return $this->id;
    }
}
