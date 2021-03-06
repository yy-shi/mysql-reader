<?php
define('ROOT_PATH',realpath(__DIR__.'/../').'/');
include ROOT_PATH.'Library/Func.php';
session_start();

ini_set('memory_limit', -1);
spl_autoload_register(function ($className) {
    $filePath = ROOT_PATH  .'Library/'.$className. '.php';
    if (file_exists($filePath)){
        include ($filePath);
    }else{
        //echo $filePath;
    }
});
 //echo Configer::config('ldap.host');
$url = 'http://' . $_SERVER['HTTP_HOST'];
define('WWW_URL', $url);
$uri    = $_SERVER['REQUEST_URI'];
$hosts  = parse_url($uri);
$path   = $hosts['path'];
$path   = strtolower($path);
$path   = rtrim($path,'/');
$path   = empty($path)?'/':$path;
$method = strtolower($_SERVER['REQUEST_METHOD']);


$auth =new Auth();
if(!$auth->isLogin()){
    if(isAjax()){
        responseJson(array('code'=>403,'msg'=>'登录超时，请重新登录'));
        exit;
    }
    if($path!=='/login'){
        redirect('/login');
    }
}
switch($path){
    case "/":
		$mysqlHost = Configer::single()->mysqls;
		$db = new DbMysql();
		$databases = $db->query('show databases;')[1];
        foreach($databases as $k=>$d){
            if(!$db->checkDatabase($d['Database'])){
                unset($databases[$k]);
            }
        }
        $rowMax = Configer::single()->query->rowMax;
        return view('index',array(
            'mysqlHost'=>$mysqlHost,
            'databases'=>$databases,
            'rowMax'=>$rowMax?$rowMax:100,
        ));
      break;
    case "/login":

        if($method=="get"){
            return view('login',array('loginError'=>Flash::get('login-error')));
        }elseif($method=="post"){
            $auth =new Auth();
            $auth->login(getPost('username'), getPost('password'),getPost('zone'));
            if($auth->isLogin()){
                redirect('/');
            }else{
                redirect('/login');
            }
        }
        break;
    case "/getdb":
        if($method == "get"){
            return ;
        }
        $host = getPost('host');
        $db = new DbMysql($host);
        $databases = $db->query('show databases;')[1];
        foreach($databases as $k=>$d){
            if(!$db->checkDatabase($d['Database'])){
                unset($databases[$k]);
            }
        }
        responseJson($databases);
        break;
    case "/query":
        if($method == "get"){
            return ;
        }
        $sql = getPost('query');
        $host = getPost('host');
        $dbName = getPost('dbname');
        $db = new DbMysql($host, $dbName);
		if(in_array($auth->id,explode(',',Configer::single()->rootUser))){
			$db->setRoot(true);
		}
		$params = $_REQUEST;
        try{
            $stime = microtime(true);
            $data= $db->queryMany($sql);
            $etime = microtime(true);
            $msg = $auth->getUsername().'|sql:'.$sql.'|result:'.round(($etime-$stime),3);
            unset($params['query']);
            Log::single('query')->add($msg, $params);
            if(isAjax()){
                responseJson(array(
                    'code'=>200,
                    'msg'=>'ok',
                    'data'=> [
                        array_map('htmlspecialchars',$data[0]),
                        array_map(function($v){
                            return array_map('htmlspecialchars',$v);
                        },$data[1])
                    ],
                ));
            }else{
                if(!is_array($data[1])){
                    $data = array('no data');
                }
                exportCsv('query-result',$data[0],$data[1]);
                //导出csv
            }
        }catch(Exception $e){
            $msg = $auth->getUsername().'|sql:'.$sql.'|result:refused|error:'.$e->getMessage();;
            Log::single('query')->add($msg, $params);
            responseJson(array(
                'code'=>$e->getCode(),
                'msg'=>$e->getMessage(),
                'data'=>'',
            ));
        }
        break;

}


