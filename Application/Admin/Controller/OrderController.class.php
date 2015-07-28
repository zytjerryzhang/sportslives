<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 订单列表控制器
 * @author Jerryzhang
 * 2015-01-20
 */
require_once 'CommonController.class.php';
/**
 * 订单管理
 * @author Jerryzhang
 *2014-09-17
 */
class OrderController extends CommonController {
	static public $s = array(0=>'未使用',1=>'已使用',2=>'已取消');
	static public $ps = array(0=>'未支付',1=>'支付成功',2=>'支付失败',3=>'支付超时');
	public function index(){
		//tabs栏数据
		$tabsMenu 				= C('ORDER_STATUS');
		$order					= M('Order');
	    //计算不同状态下的单据数
	    $counts      = $order->field('status,count(*) as total')->group('status')->select();
	    foreach($counts as $val){
	    	$arrCounts[$val['status']] = $val['total'];
	    	$arrCounts[100] += $val['total'];//总记录数
	    }
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => array(),'tabsMenu'=>$tabsMenu, 'arrCounts'=>$arrCounts, 'permission'=>$this->permission));
		$this->ajaxReturn($ret);
	}
	/**
	 * 订单列表
	 * Enter description here ...
	 */
	public function orderList(){
		$tA = 'sl_order';
		$tB = 'sl_gymnasium';
		$tC = 'sl_user';
		//获取参数
		$status 		= I('get.status');
		$currentPage 	= I('get.currentPage','1');
		$pageSize    	= I('get.pageSize','15');
		$orderby	 	= I('get.orderby');
		$browseType		= I('get.browsetype');
		$orderby	 	= !empty( $orderby ) ? str_replace(';', ' ',$tA.'.'.$orderby) :  "$tA.id desc";
		//检索项
		$order_no			= I('post.order_no');
		$user_name			= I('post.user_name');
		$min_money			= I('post.min_money',0,'int');
		$max_money			= I('post.max_money',0,'int');
		$min_date			= I('post.min_date','','is_date');
		$max_date			= I('post.max_date','','is_date');

		if($user_name){
			$where["$tC.username"] = array('like',"%{$user_name}%");
		}
		if( $order_no ) {
			$where["$tA.order_no"] = array('like',"%{$order_no}%");
		}
		if($min_money && $max_money) {
			$where["$tA.order_money"] = array('between', array($min_money, $max_money));
		}
		if($min_date && $max_date) {
			$where["$tA.create_time"] = array('between', array($min_date, $max_date));
		}
		if( $status != 100 && $status ) {
			$where["$tA.status"] 	= $status;
		}

		$count		 = M('Order')->join("LEFT JOIN $tB on $tA.gym_id = $tB.id")
						->where( $where )->count();
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出

		//进行分页查询数据
		$order_list = M('Order')->join("LEFT JOIN $tB ON $tA.gym_id = $tB.id")
						->join("JOIN $tC ON $tA.user_id=$tC.id")
						->where( $where )
						->order( $orderby )
						->page($currentPage . ',' . $pageSize)
						->field("$tA.*,$tB.name,$tC.username,$tC.mobile")
						->select();

		foreach($order_list as $key=>$val){
			$pro_info = M('Project')->where(array('id'=>$val['pro_id']))->find();
			$order_list[$key]['pro_name']=$pro_info['name'];
			$order_list[$key]['pay_s_name']=self::$ps[$val['pay_status']];
			$order_list[$key]['status_name']=self::$s[$val['status']];
			//产品列表
			if($browseType=='unfold'){
				$item_list = M('OrderItem')->where(array('order_id'=>$val['id']))->select();
				$order_list[$key]['item_list'] = $item_list;
			}
		}
		//分页数据输出
		$aOut['pages'] =array('pageSize'=>$pageSize,'currentPage'=>$currentPage,'count'=>$count) ;
        $aOut['list'] = $order_list;
      	$this->ajaxReturn($aOut);
	}
	/**
	 * 我地订单
	 * Enter description here ...
	 */
	public function myOrder(){
		//获取变量
		$user_name		= I('post.user_name');
		$mobile			= I('post.mobile');
		$min_money		= I('post.min_moey','0','int');
		$max_money		= I('post.max_money','0','int');
		$start_date		= I('post.start_date','','is_date');
		$end_date		= I('post.end_date','','is_date'); //时间戳
		$browseType		= I('get.browseType');

		$tA = 'sl_order';
		$tB = 'sl_gymnasium';
		$tC = 'sl_user';
		$where["$tB.seller_uid"]	 = $this->user_info['id'];
		//检索
		if($user_name) {
			$where["$tC.username"] = array('like',"%{$user_name}%");
		}
		if($mobile) {
			$where["$tC.mobile"] = array('like',"{$mobile}%");
		}
		if($min_money && $max_money) {
			$where["$tA.order_money"] = array('between', array($min_money ,$max_money));
		}
		if($start_date && $end_date){
			$where["$tA.create_time"] = array('between', array($start_date, $end_date));
		}

		//排序
		//点击排序
		$orderby	 = I('post.orderby');
		$orderInfo   = !empty( $orderby ) ? explode(' ',$orderby) : '';
		$orderby	 = !empty( $orderby ) ? "$tA.'.'.$orderby" :  "$tA.create_time desc";

		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');

		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) > 0 ? I('post.p') : 0;
		$count		 = M('Order')->join("LEFT JOIN $tB on $tA.gym_id = $tB.id")
						->join("LEFT JOIN $tC on $tA.user_id = $tC.id")
						->where( $where )->count();
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出

		//进行分页查询数据
		$list =  M('Order')->join("LEFT JOIN $tB on $tA.gym_id = $tB.id")
						->join("LEFT JOIN $tC on $tA.user_id = $tC.id")
						->where( $where )
						->order( $orderby )
						->page($currentPage . ',' . C('PAGESIZE'))
						->field("$tA.*,$tB.name,$tC.username,$tC.mobile")
						->select();
//		die(M('Order')->getLastSql());
		foreach($list as $key=>$val){
			$pro_info = M('Project')->where(array('id'=>$val['pro_id']))->find();
			$list[$key]['pro_name']=$pro_info['name'];
			$list[$key]['pay_s_name']=self::$ps[$val['pay_status']];
			$list[$key]['status_name']=self::$s[$val['status']];
			//产品列表
			if($browseType=='unfold'){
				$item_list = M('OrderItem')->where(array('order_id'=>$val['id']))->select();
				$list[$key]['item_list'] = $item_list;
			}
		}
//		print_r($list);exit;
		$ret = array('status' => 1,
					 'info' => '',
					 'data' => array('aData' => $list,
					 				 'pagelink' => $pageLink,
					 				 'permission' => $this->permission));
		$this->ajaxReturn($ret);
	}
}