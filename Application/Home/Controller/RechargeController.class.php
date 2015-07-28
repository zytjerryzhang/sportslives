<?php
namespace Home\Controller;
use Common\Lib\Message;
use Think\Controller;
use Common\Lib\Log;
class RechargeController extends Controller{
	function index(){
        redirect('/');
    }

    /**
     * 网银回调地址
     * Enter description here ...
     */
    function backWangyin(){
        $pay_account = C('PAY_ACCOUNT');
        $wangyin = $pay_account['wangyin'];

        $data = I('post.');
        $data['key']=$wangyin['key']; ///////////md5密钥（KEY）
        $md5string =  D('Pay')->wangyinMd5($wangyin,1);

        if($md5string ==  false){
            $data['error'] = '签名缺少参数';
            Log::write_log($data,Log::EMERG,'wangyin');
            echo "error";
            exit;
        }

        $v_md5str = $data['v_md5str'];
        if ($v_md5str!=$md5string) {
            $data['error'] = '签名失败';
            Log::write_log($data,Log::EMERG,'wangyin');
            echo "error";
            exit;
        }
		$v_oid = $data['v_oid'];
        $rchg =D('Order')->where(array('order_no'=>$v_oid))->find();
        $v_pstatus = $data['v_pstatus'];
        if($v_pstatus!='20'){
        	//取消场次锁定
        	if(!empty($rchg)){
        		$sql = "UPDATE __ORDER_ITEM__ oi,__SITE__ s,__ORDER__ o
                SET s.saled_num=s.saled_num-oi.order_number,o.pay_status=2
                  WHERE s.id=oi.site_id AND o.id=oi.order_id AND oi.order_id={$rchg['id']}";
            	M('Order')->execute($sql);
        	}
            $data['success'] = '签名成功，状态异常';
            Log::write_log($data,Log::EMERG,'wangyin');
            echo 'ok';
            exit;
        }
        if(empty($rchg) || $rchg['status']==1) {
            $data['error'] = '数据已处理或不存在';
            Log::write_log($data,Log::ALERT,'wangyin');
            exit;
        }

		//修改订单状态
        $bool = D('Order')->where(array('order_no'=>$v_oid))->save(array('status'=>1,'update_time'=>time()));
        if(!$bool){
            $data['error'] = $model->getError();
            Log::write_log($data,Log::ERR);
            exit;
        }
        $data['success'] = '付款处理理成功';
        Log::write_log($data,Log::NOTICE);
        echo 'ok';
        exit;
    }
}
