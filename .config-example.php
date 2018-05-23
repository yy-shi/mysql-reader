<?php
ini_set('display_errors', 'on');
return [
    'ldap' => [
        'account_suffix' => "@chicv.com",
        'baseDn' => 'dc=example,dc=com',
        'host' => '192.168.0.16',
        'searchDn' => 'cn=beijing',
        'loginDn' => 'cn=database'
    ],
    'cookie' => [
        'domain' => '/'
    ],
    'rootUser' => 'root',
    'query' => [
        'syntax_check' => true,
        'rowMax' => 200,
        'offsetMax' => 1000,
        'strLenMax' => 50000
    ],
    'mysqls' => [
        'dev1' => [
            'name' => '开发机1',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'secret'
        ],
        'dev2' => [
            'name' => '开发机2',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'secret'
        ]
    ],
    'log' => [
        'dir' => ROOT_PATH . 'logs/'
    ]
];
