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
        if($config->base_dn){
            $this->_basedn  = $config->base_dn;
        }
        if($config->search_dn){
            $this->_searchDn =  $config->search_dn;
        }
        ldap_set_option($this->_ldap,LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($this->_ldap,LDAP_OPT_REFERRALS,0);
    }

    public function checkUser($user, $password){
        $bind= @ldap_bind($this->_ldap,'uid='.$user.',ou=Users,dc=example,dc=com',$password);
        if($bind )  
        {
            $SEARCH_DN= $this->_searchDn;;
            $SEARCH_FIELDS= array('mail','displayName', 'cn');  
            $result= ldap_search($this->_ldap,$SEARCH_DN,"cn=" . $user,$SEARCH_FIELDS);  
            $retData = ldap_get_entries($this->_ldap, $result);  
            $v= current($retData);
            return array(  
                'userName'=> $v['cn'][0]?$v['cn'][0]:$user,  
                'nickName'=> $v['displayname'][0], 
                'mail'=> $v['mail'][0]  
            );   
        }else{
            return false;
        }
    }

    public function __destruct(){
        ldap_close($this->_ldap);
    }
}

