<?php
/**
 * ldap
 * 有登录和用户信息查询
 */
class Ldap{
    private $_ldap;
    private $_baseDn='dc=dc,dc=net';
    public function __construct(){
        $config = Configer::single()->ldap;
        $this->_ldap = ldap_connect($config->host);
        if($config->baseDn){
            $this->_baseDn  = $config->baseDn;
        }
        if($config->loginDn){
            $this->_loginDn =  $config->loginDn;
        }
        if($config->searchDn){
            $this->_searchDn =  $config->searchDn;
        }
        ldap_set_option($this->_ldap,LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($this->_ldap,LDAP_OPT_REFERRALS,0);
    }

    public function checkUser($user, $password){
        $bind= ldap_bind($this->_ldap,'uid='.$user.','.$this->_loginDn.','.$this->_baseDn,$password);
        if($bind)  
        {
            if($this->checkLimit($user)){
                $result= ldap_search($this->_ldap,$this->_baseDn,"uid=" . $user,array('mail','cn'));  
                $retData = ldap_get_entries($this->_ldap, $result);  
                $v= $retData[0];
                return array( 
                    'id'=>$user,
                    'username'=> $v['cn'][0]?$v['cn'][0]:$user,  
                    'mail'=> $v['mail'][0],
                ); 
            }else{
                flash::add('login-error','你没有权限');   
            }

        }else{
            flash::add('login-error','用户名或密码错误');   
            return false;
        }
    }
    public function checkLimit($uid){
        $result= ldap_search($this->_ldap,$this->_baseDn,$this->_searchDn,array('member','cn'));  
        $retData = ldap_get_entries($this->_ldap, $result);
        if(isset($retData[0])){
            $data = $retData[0];
            $user = 'uid='.$uid.','.$this->_loginDn.','.$this->_baseDn;
            return in_array($user, $data['member']);
        }
    }
    public function __destruct(){
        ldap_close($this->_ldap);
    }
}

