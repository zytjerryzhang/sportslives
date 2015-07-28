<?php
namespace Home\Controller;
use Common\Lib\Log;
class PayController extends CommonController
{
    private $user = null;
    function __construct()
    {
        parent::__construct();
        if (UID <= 0) {
            redirect(U('login/login'));
        }
        $this->user = session('user');
    }

    public function index(){
        $oid = I('get.oid', 0, 'intval');
        $ref = I('get.ref', $_SERVER['HTTP_REFERER'], 'trim');
        if (!$oid)
            $this->error("您访问的页面出现了错误！", $ref);

        $mOrder = M('order');
        $oInfo = $mOrder->where("id=$oid AND user_id=" . UID)->find();
        if (!$oInfo) $this->error("订单并不存在！", $ref);
        if ($oInfo['pay_status'] == 1)
            $this->error("订单已成功支付！", $ref);
        if ($oInfo['pay_status'] == 3)
            $this->error("订单已超时！", $ref);
        if (strtotime($oInfo['create_time']) + 900 < NOW_TIME) {
            if ($oInfo['pay_status'] == 0) {
                $sql = "UPDATE __ORDER_ITEM__ oi,__SITE__ s,__ORDER__ o
                    SET s.saled_num=s.saled_num-oi.order_number,o.pay_status=3
                    WHERE s.id=oi.site_id AND o.id=oi.order_id AND oi.order_id=$oid";
                $mOrder->execute($sql);
            }
            $this->error("订单已超时！", $ref);
        }
        $this->assign('orderNo', $oInfo['order_no']);
        $p = number_format($oInfo['order_money']/100, 2, '.', '');
        $this->assign('orderPrice', $p);
        $this->assign('reminds', strtotime($oInfo['create_time']) + 900 - NOW_TIME);

        $tmp = M('gymnasium')->where("id={$oInfo['gym_id']}")->field("name")->find();
        if (empty($tmp) || !isset($tmp['name']))
            $this->error("场馆信息不存在！", $ref);
        $this->assign('gymName', $tmp['name']);

        $tmp = M('project')->where("id={$oInfo['pro_id']}")->field("name")->find();
        if (empty($tmp) || !isset($tmp['name']))
            $this->error("体育项目信息不存在！", $ref);
        $this->assign('proName', $tmp['name']);

        $sql = "SELECT `date` d,begin_time b,end_time e,
                price p,site_name n,order_number o
            FROM __ORDER_ITEM__ oi
            WHERE oi.order_id=$oid";
        $tmp = $mOrder->query($sql);
        if (empty($tmp))
            $this->error("场次信息不存在！", $ref);
        $siteInfo = array();
        foreach ($tmp as $v) {
            $date = $v['d'];
            $siteInfo[] = array(
                'b' => substr($v['b'],0,-3),
                'e' => substr($v['e'],0,-3),
                'p' => number_format($v['p']/100, 2, '.', ''),
                'n' => $v['n'],
                'o' => $v['o'],
            );
        }
        $this->assign('siteInfo', $siteInfo);
        $week = array('日','一','二','三','四','五','六');
        $time = strtotime($date);
        $this->assign('date', array(
            'd' => date('n月j日', $time),
            'w' => $week[date('w', $time)],
        ));

		$list = M('Bank')->where(array('status'=>1))->order('idx asc')->select();
		$this->assign('bankList',$list);

        $this->assign('__BODY_ID__', 'choose-site');

        //支付订单
        $pay_account = C('PAY_ACCOUNT');
		//网银在线
		$wangyin=$pay_account['wangyin'];
		$wangyin['v_oid'] = $oInfo['order_no'];
		$wangyin['v_amount'] = $oInfo['order_money'];
		$md5str = D('Pay')->wangyinMd5($wangyin,1);
		if(!$md5str){
			$this->error('缺少加密参数，请联系管理员');
		}
		unset($wangyin['key']); //删除key
		$wangyin['v_md5info'] =$md5str;
		$wangyin['remark1'] = '';
		$this->assign('payUrl', $wangyin['payUrl']);
		$this->assign('rchg', $wangyin);
		$this->display();
    }
}
