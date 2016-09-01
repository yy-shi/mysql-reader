<?php
ini_set('display_errors','on');
return array(
    'ldap'=>array(
        'account_suffix' => "@chicv.com",
        'baseDn' => 'dc=example,dc=com',
        'host'=>'192.168.0.16',
        'searchDn'=>'cn=beijing',
        'loginDn'=>'cn=database',
    ),
    'cookie'=>array(
        'domain'=>'/',
	),
	'rootUser'=>'root',
    'query'=>array(
        'rowMax'=>200,
        'offsetMax'=>1000,
        'strLenMax'=>50000,
     ),
     'mysqls'=>array(
         'dev1'=>array(
             'name'=>'开发机1',
             'host'=>'127.0.0.1',
             'port'=>'3306',
             'username'=>'root',
             'password'=>'secret'
         ),
         'dev2'=>array(
             'name'=>'开发机2',
             'host'=>'127.0.0.1',
             'port'=>'3306',
             'username'=>'root',
             'password'=>'secret'
         ),
     ),
     'log'=>array(
         'dir'=>ROOT_PATH.'logs/',
     ),

     );
