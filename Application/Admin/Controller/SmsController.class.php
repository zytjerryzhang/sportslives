<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 短信管理
 * @author Jerryzhang
 * 2014-09-17
 */
class SmsController extends CommonController {
	/**
	 * 信息统计
	 */
	public function smsList() {
		//获取参数
		$mobile	= I('post.mobile');
		$type   = I('post.select');
		$btime  = I('post.btime');
		$etime  = I('post.etime');

		//定义变量
		$sms = M('Sms');
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$where = array();
		$sms_type = C('SMS_TYPE');

		//检验参数
		if (!empty($mobile)) {
			$where['mobile'] = $mobile;
		}
		if (!empty($type)) {
			$where['type'] = $type;
		}
		if (!empty($btime) && !empty($etime)) {
			$where['create_time'] = array('between', array($btime . " 00:00:01", $etime . " 23:59:59"));
		} elseif (!empty($btime)) {
			$where['create_time'] = array("gt", $btime . " 00:00:01");
		} elseif (!empty($etime)) {
			$where['create_time'] = array("lt", $etime . " 23:59:59");
		}

		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) > 0 ? I ('post.p') : 1;
		$count       = $sms->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出

		// 进行分页数据查询
		$list = $sms->where($where)->order('create_time DESC')->page($currentPage.','.C('PAGESIZE'))->select();
		$ret = array('status' => 1,
					 'info' => '',
					 'data' => array(
									 'sms_type' => $sms_type,
									 'aData' => $list,
									 'pagelink' => $pageLink,
									'permission' => $this->permission));
		$this->ajaxReturn($ret);
	}
}