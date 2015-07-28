<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';

/**
 * 会员控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class SluserController extends CommonController {
	/**
	 * 获取会员信息列表
	 */
	public function userList() {


		//定义变量
		$slUser = M('User');

		//校验参数
		$where = $this->getWhere();

		//分页查询部门
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $slUser->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
	    $page        = new  Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
    	$pageLink = $page->show();	// 分页显示输出

    	$slUser_list_info = $slUser->where($where)->order('id desc')->page( $currentPage . ',' . C('PAGESIZE'))->select();

    	//返回
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $slUser_list_info,'pagelink' => $pageLink, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}
	/**
	 * 条件查询
	 * Enter description here ...
	 */
	private function getWhere(){
		$where = array();
		//获取参数
		$username 		= I('post.username');
		$mobile 		= I('post.mobile');
		$email 			= I('post.email');
		$start_date		= I('post.start_date','','is_date');
		$end_date		= I('post.end_date','','is_date'); //时间戳
		//检索
		if( $username ) {
			$where['username'] = array('like',"%{$username}%");
		}
		if( $email ) {
			$where['email'] = array('like',"%{$email}%");
		}
		if( $mobile ) {
			$where['mobile'] = array('like',"%{$mobile}%");
		}
		if($start_date && $end_date){
			$where['create_time'] = array('between', array($start_date, $end_date));
		}
		return $where;
	}
	/**
	 * 编辑会员信息
	 * Enter description here ...
	 */
	public function modifyUser(){
		//获取参数
		$param = I('post.ekey', array(), '');

		//检验
		if( !is_array( $param ) || empty( $param ) ) {
			$this->ajaxReturn(array('status' => -1, 'info' => 'the param is require!'));
		}
		$i = 0;
		$user = M('User');
		foreach($param as $key => $val) { //依次更新
			if(!$val['id']) {
				$this->ajaxReturn(array('status' => -1, 'info' => 'the param id is require!','data'=>array()));
			}
			$where  = array('id'=>$val['id']);
			$user_info = $user->where( $where )->find();
			if( empty( $user_info ) ) continue;

			$user->where( $where )->save($val); // 根据条件更新记录
			$i++;
		}
		$this->ajaxReturn(array('status' => 1, 'info' => $i . ' records have been modified!','data'=>array()));
	}

	/**
	 *用户详情
	 */
	public function userInfo(){
		$id = I('get.id');
		$user_info = M('User')->where(array('id'=>$id))->find();
		//权限
		$per['Sluser/myOrderList'] 	= isset($this->permission['Sluser/myOrderList']) ? 1 : 0 ;

		$ret = array('status' => 1,
					 'info' => '获取数据成功！',
					 'data' => array(
					 	'userInfo'=>$user_info,
						'permission'=>$per)
				);
		$this->ajaxReturn($ret);
	}
}