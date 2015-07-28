<?php
namespace Common\Model;
use Think\Model;
/**
 * @todo 防止以后增加扩展  单独写Model
 * Enter description here ...
 * @author Jerryzhang
 *
 */
class PayModel extends Model {
    private $error_msg='';

    /**
     * 获取错误信息
     * @return string
     */
    public function getError(){
        return $this->error_msg;
    }
 	/**
     * 网银参数加密
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $type
     */
	public	function wangyinMd5($data,$type=0){
        $str='';
        if($type==1){
            //加密
            $md5_array =array('v_amount','v_moneytype','v_oid','v_mid','v_url','key');
        }else{
            $md5_array =array('v_oid','v_pstatus','v_amount','v_moneytype','key');
        }
        foreach($md5_array as $key=>$val){
            if(!isset($data[$val])){
                return false;
            }
            $str.= $data[$val];
        }
        return strtoupper(md5($str));
    }
}