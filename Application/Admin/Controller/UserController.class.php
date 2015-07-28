<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 用户列表控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class UserController extends CommonController {
	/**
	 * 获取用户信息列表
	 */
	public function getUserList() {
		$emp       = M('AEmp');
		//查询处理
		$keywords = I('post.keywords');
		$where = ! empty($keywords) ?  "id LIKE '%{$keywords}%' OR realname LIKE '%{$keywords}%' OR username LIKE '%{$keywords}%' OR mobile LIKE '%{$keywords}%' OR email LIKE '%{$keywords}%'" : '';

		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $emp->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出

	    // 进行分页数据查询
    	$emp_info = $emp->where($where)->order('id DESC')->page($currentPage.','.C('PAGESIZE'))->select();
    	$depar = M("ADept");
    	$dept_info = $depar->field("id,name")->select();
		$pos = M("APos");
    	$pos_info = $pos->field("id,name")->select();
    	$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $emp_info,'dept_data'=>$dept_info,'pos_data'=>$pos_info,'pagelink' => $pageLink, 'permission'=>$this->permission));
    	$this->ajaxReturn($ret);
	}


	/**
	 * 添加一个用户信息
	 */
	public function addUser() {
		$ret['status'] = -1;

		//获取参数
		$realname		= I('post.realname');
		$username		= I('post.username');
		$dept_id		= I('post.dept_id');
		$email			= I('post.email');
		$mobile			= I('post.mobile');
		$sup_id			= I('post.sup_id');
		$pos_id			= I('post.pos_id');
		$status			= I('post.status');

		//检验参数
		$ret['status'] = 0;
		if( !$realname ) {
			$ret['info'] = '用户名不能为空！ ';
			$this->ajaxReturn($ret);
		}
		if( !$username ) {
			$ret['info'] = '英文名不能为空！ ';
			$this->ajaxReturn($ret);
		}
		if( !$dept_id || !is_numeric( $dept_id ) ) {
			$ret['info'] = '用户部门不能为空或格式有误！ ';
			$this->ajaxReturn($ret);
		}
		if( !$email ) {
			$ret['info'] = '邮箱地址不能为空！ ';
			$this->ajaxReturn($ret);
		}
		if( !fun_email($email) ) {
			$ret['info'] = '邮箱格式不正确！ ';
			$this->ajaxReturn($ret);
		}
		if( !$mobile || !is_numeric($mobile) ) {
			$ret['info'] = '电话号码不能为空！ ';
			$this->ajaxReturn($ret);
		}
		if( !$sup_id || !is_numeric($sup_id) ) {
			$ret['info'] = '直属上级输入格式有误！ ';
			$this->ajaxReturn($ret);
		}
		if( !$pos_id || !is_numeric($pos_id) ) {
			$ret['info'] = '职位输入格式有误！ ';
			$this->ajaxReturn($ret);
		}
		if( !in_array( $status,array(0,1) ) ) {
			$ret['info'] = '状态值提交错误！ ';
			$this->ajaxReturn($ret);
		}

		//赋值
		$data['realname']		= $realname;
		$data['username']		= $username;
		$data['dept_id']		= $dept_id;
		$data['email']			= $email;
		$data['mobile']			= $mobile;
		$data['sup_id']			= $sup_id;
		$data['pos_id']			= $pos_id;
		$data['status']			= $status;
		//添加用户信息时新增密码为public123
		$data['passwd'] 		= md5('public123');
		$data['create_time'] 	= time();

		$emp = M('AEmp');
		$emp_id = $emp->add($data); //添加
		if( is_numeric($emp_id) ) {
			$ret['status'] = 1;
			$ret['info'] = '恭喜！成功提交!';
			$this->ajaxReturn($ret);
		} else {
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 用户信息修改
	 */
	public function modifyUser() {
		//获取参数
		$param = I('post.ekey', array(), '');

		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$emp = M('AEmp');
		$i = 0;
		//检验
		if( empty( $param ) || !is_array( $param ) ) {
			$ret['status'] = -1;
			$ret['info'] = 'the param is require!';
			$this->ajaxReturn($ret);
		}
		$i = 0;
		foreach($param as $key => $val) { //依次更新
			if(!$val['id']) {
				$this->ajaxReturn(array('status' => -1, 'info' => 'the param id is require!','data'=>array()));
			}
			$where = array('id'=>$val['id']);
			$emp_info = $emp->where( $where )->find();
			if( empty($emp_info) ) continue;
			$emp->where( $where )->save($val); // 根据条件更新记录
			$i++;
		}

		$this->ajaxReturn(array('status' => 1, 'info' => $i . ' records have been modified!','data'=>array()));
	}
}
