<?php
/*
 *渲染模版方法
 *支持layout和注入变量
 */
function view($tempName, $data=array()){
    extract($data, EXTR_PREFIX_SAME, "_");
    ob_start();
    $layouts = include(ROOT_PATH.'views/'.$tempName.'.html');
    $content = ob_get_clean();
    include (ROOT_PATH.'views/layouts.html');
}
/**
 * 跳转
 */
function redirect($uri, $params=array()) {
    if (strpos($uri,'://') == false) {
        $uri = WWW_URL.$uri;
    }
    $uri = $uri . (strpos($uri, '?') ? '&' : '?') . http_build_query($params);
    header('Location: ' . $uri);
    exit;
}
/**
 *输出json
 *
 */
function responseJson($data){
    if(!isset($data['code'])){
        $data  = array(
            'code'=>200,
            'data'=>$data,
        );
    }
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
}

function isAjax(){
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function getPost($key){
    return isset($_POST[$key])?$_POST[$key]:'';
}

