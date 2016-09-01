<?php
/**
 * 数据库操作类
 * sql检查， 只允许select和show
 * select时必须有limit参数
 * 数据库选择 不允许选择mysql
 *
 */
class DbMysql  {
    private $_db = null;
    private $_rowMax = 100;
    private $_offsetMax = 5000;
    private $_strLenMax = 50000;
    private $_checkLimit = true;
    public function __construct($conf='', $dbName='') {
        $config= Configer::single();
        $hosts= $config->mysqls;
        if($config->query->offsetMax){
            $this->_offsetMax = $config->query->offsetMax;
        }
        if($config->query->strLenMax){
            $this->_strLenMax = $config->query->strLenMax;
        }
        if($config->query->rowMax){
            $this->_rowMax = $config->query->rowMax;
        }
        if(empty($conf) || !isset($hosts->$conf)){
            $conf = current($hosts);
        }else{
            $conf = $hosts->$conf;
        }
        $this->setDb($conf, $dbName);
	}
	public function setRoot($isRoot=false){
		$this->_checkLimit= !$isRoot;
	}
    private function setDb($conf, $dbName) {
        try {
            $cdn  = "mysql:host=" . $conf['host'] . ";port=" . $conf['port'] . ";";
            if($this->checkDatabase($dbName)){
                $cdn.="dbname=".$dbName.';';
            }
            $this->_db = new PDO($cdn, $conf['username'], $conf['password'], array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                PDO::ATTR_TIMEOUT => 10,
                //  PDO::ATTR_PERSISTENT=>true,
            ));
            $this->_db->query("set SQL_MODE=''");
            //$this->_db->query("set wait_timeout=10;set interactive_timeout=5;");
        }
        catch(PDOException $e) {
            throw new Exception('数据库连接失败,: ' . $e->getMessage());
        }
    }
    public function query($sql){
        $this->checkSql($sql);
        $ex = $this->_db->prepare($sql);
        $ex->execute();
        if($ex->errorCode()>0){
            $error =$ex->errorInfo();
            throw new Exception($error[2], $error[1]);
        }
        $ex->setFetchMode(2);
        $data = $ex->fetchAll();
		$ex->closeCursor();
		if($this->_checkLimit){
			$slice = array_slice($data,0,$this->_rowMax);
			$strLen = strlen(json_encode($slice));
			if($strLen > $this->_strLenMax){
				throw new Exception('the data is too large,max len is '.$this->_strLenMax,105);
			}
			return $slice;
		}
		return $data;
    }
    /**
     * 把分号的sql语句拆开执行;
     */
    public function queryMany($sql){
        $result = array();
        $i = 0;
        $regular = "/(['\"])((?!['\"]).)+\\1/";
        $this->mcSql = array();
        $sql = preg_replace_callback($regular,function($match){
            $this->mcSql[] = $match[0];
            return "%s";
        },$sql);
        $mcSql = $this->mcSql;
        foreach(explode(';',$sql) as  $singleSql){
            if($i >2) break;
            $singleSql = trim($singleSql);
 			$singleSql = strtr($singleSql,array("\r\n"=>" ","\n"=>" "));
            if(empty($singleSql)) continue;
            $params = array($singleSql);
            $strCount = substr_count($singleSql,'%s');
            if($strCount){
                $params = array_merge($params, array_slice($mcSql,0,$strCount));
                $singleSql = call_user_func_array('sprintf',$params);
                $mcSql = array_slice($mcSql,$strCount);
            }
            $result[] = $this->query($singleSql);
            $i++;
        }
        return end($result);
    }
    /**
     *检测一些mysql 更新删除语句
     *检测select的limit大小
     *只支持show 和 符合行数限制要求的select语句
     */
    public function checkSql($sql){
        $regular = "/^select\ ((?!\s*sleep\s*\().)+\s+limit\s+\d+/i";
        $regularShow = "/^(show|explain|set) /i";
        if(!preg_match($regular,$sql) && !preg_match($regularShow, $sql)){
            $regular = '/[\s\r\n]*(alter|create|update|delete|insert|drop|dump|sleep\(\d+\)|replace|kill)[\s\r\n]+/i';
            if(preg_match($regular,$sql)){
                throw new Exception('there has some dangerous opration in your sql,please contact the administrator to execute this sql',102);
            }else{
				if(preg_match("/^select/i", $sql)){
					if($this->_checkLimit==false){
						return true;
					}
                    throw new Exception('miss limit',103);
                }else{
                    throw new Exception('are u sure ,what u gave to  me is  a sql?',108);
                }
            }
        }//else 为可执行sql
		//匹配每个select查询（包含子查询）,每个查询必须有limit
		if($this->_checkLimit==false){
			return true;
		}
        $ckRegular = "/select\ ((?!(limit|select)).)+limit\ +(\d+)(?:\ *\,\ *(\d+))?/i";
        $ckSql = $sql;
        while(preg_match($ckRegular,$ckSql)){
            $ckSql = preg_replace_callback($ckRegular,function($match){
                $limit = array_pop($match);
                $offset = array_pop($match);
                if($limit > $this->_rowMax){
                    throw new Exception('select too much rows, max value is '.$this->_rowMax,104);
                }
                if($offset > $this->_offsetMax){
                    throw new Exception('offset too much, max value is '.$this->_offsetMax,104);
                }
                return "";
            },$ckSql);
        }
        if(preg_match("/^select/i", $sql) && !empty($ckSql)){
            throw new Exception('select and limit isnot equal in your sql', 109);
        }
    }
    public function checkDatabase($db){
        return !empty($db) && !in_array($db, array('mysql','information_schema','performance_schema'));
    }
}

