<?php
/**
 *配置文件操作类
 */
class Configer {
    private $options;
    private function __construct() {
        $config = include(ROOT_PATH.'config.php');
        $this->options = (object) $config;
    }
    public function __get($value) {
        if (isset($this->options->$value)) {
            $v = $this->options->$value;
            if(is_array($v)){
                return (object) $v;
            }
            return $v;
        }
    }
    public static function single() {
        static $sington;
        if (is_null($sington)) {
            $sington = new self;
        }
        return $sington;
    }
    public function toArray() {
        return (array) $this->options;
    }
}
