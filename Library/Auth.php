<?php
/**
 * 用户身份类
 * @author shiyongyong@chicv.com
 */
class Auth{

    const U_NAME='cv_udata';
    private $_data = array();
    public  $id='';
    public function __construct(){
        $uSession = isset($_SESSION[self::U_NAME])?$_SESSION[self::U_NAME]:'';
        if($uSession){
            $this->setData($uSession);
        }
    }
    public function __get($key){
        if(isset($this->_data[$key])){
            return  $this->_data[$key];
        }
    }
    /**
     * 设置对象数据
     * 并设置session
     */
    public function setData($data){
        if($data && $data['id']){
            $this->_data = $data;
            $this->id = $data['id'];
            $_SESSION[self::U_NAME]=$data;
        }
    }
    public function isLogin(){
        return !empty($this->id);
    }
    public function login($username, $password,$zone){
        $ldap = new Ldap();
        $data = $ldap->checkUser($username, $password,$zone);
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
