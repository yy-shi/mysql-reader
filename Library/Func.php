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


function exportcsv($filename,$header,$data){
    $filename = $filename.date('YmdHis').rand(1000,9999).'.csv';
    header("Cache-Control: public");
    header("Pragma: public");
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Content-Type:APPLICATION/OCTET-STREAM');
    $output = fopen('php://output', 'w');
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($agent, 'windows nt')) {
        $header = array_map(function($value){
            return iconv("utf-8",'gbk',$value);
        }, $header);
    }
    fputcsv($output, $header);
    if(!empty($data)){
        foreach($data as $key => $val){
            if(strpos($agent, 'windows nt')) {
                $val = array_map(function($value){
                    return iconv("utf-8",'gbk',$value);
                }, $val);
            }
            fputcsv($output, (array)$val);
        }
    }
}



