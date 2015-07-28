<?php
namespace Common\Lib;

use Think\Log\Driver;

//use Common\Lib\ShjzSms;
class Log extends \Think\Log
{
    function __construct($config=array()){
        parent::init($config);
    }

    static function write_log($data,$leven,$prefix='rchg_'){
        $str = json_encode($data);
        Log::write($str,$leven,'File',C('LOG_PATH').$prefix.date('y_m_d').'.log');
    }
}