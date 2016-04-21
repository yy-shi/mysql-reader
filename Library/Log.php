<?php
/**
 * log类
 * 实例话的参数为目录名称
 * log每天记录一个文件
 */
class Log {
    private $_logInfo = array();
    private $_fileResources = array();
    private $_logDir;
    private $_baseDir;
    private function __construct() {
        $conf = Configer::single()->log;
        if (empty($conf->dir)) {
            throw new Exception("please set the log Dir!the");
        }
        $this->_baseDir = realpath($conf->dir).'/';
    }
    public function setDir($folder){
        $this->_logDir = $this->_baseDir.$folder;
        if(!is_dir($this->_logDir)){
            mkdir($this->_logDir,0777,true);
        }
    }
    static public function single($folder = '') {
        static $sington;
        $folder = empty($folder) ?  : $folder;
        if (empty($sington)) {
            $sington = new self();
        }
        $sington->setDir($folder);
        return $sington;
    }
    public function add($message, $context = array()) {
            if (is_array($message)) {
                $message = json_encode($message, JSON_UNESCAPED_UNICODE);
            }
            $arr = array(
                'message' => $message,
                'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
                'time' => date('Y-m-d h:i:s'),
                'dir' => $this->_logDir,
            );
            $this->_logInfo[] = $arr;
        }
        public function writeLog() {
            foreach ($this->_logInfo as $log) {
                $str = $this->formatLog($log);
                $handle = $this->getFileHandle($log['dir']);
                fputs($handle, $str);
            }
            $this->closeFiles();
        }
        private function formatLog($log) {
            $str = $log['time'] . " | ";
            $str .= $log['message'] . " | ";
            $str .= $log['context']." | ";
            $str .= $_SERVER['REMOTE_ADDR'];
            return trim($str, ' | ') . "\n";
        }
        private function getFileHandle($dir) {
            $file = $dir.'/'. date('y-m-d') . '.log';
            if (!isset($this->_fileResources[$file])) {
                $this->_fileResources[$file] = fopen($file, 'a');
            }
            return $this->_fileResources[$file];
        }
        private function closeFiles() {

            foreach ($this->_fileResources as $handle) {
                fclose($handle);
            }
        }
        public function __destruct() {
            $this->writeLog();
        }
}

