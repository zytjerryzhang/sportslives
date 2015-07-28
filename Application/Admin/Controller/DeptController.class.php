<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';

/**
 * 部门控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class deptController extends CommonController {
	/**
	 * 获取部门信息列表
	 */
	public function deptList() {
		//获取参数
		$keywords = I('post.keywords');

		//定义变量
		$dept = M('ADept');

		//校验参数
		$where = array();
		if ($keywords) {
			$where['name'] = array('like', "%{$keywords}%");
		}

		//分页查询部门
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $dept->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
	    $page        = new  Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
    	$pageLink = $page->show();	// 分页显示输出

    	$dept_list_info = $dept->where($where)->order('id desc')->page( $currentPage . ',' . C('PAGESIZE'))->select();

    	//返回
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $dept_list_info,'pagelink' => $pageLink, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 添加一个部门信息
	 */
	public function addDept() {
		//获取参数
		$id 	= I('post.id');
		$name 	= I('post.name');
		$pid 	= I('post.pid');
		$status = I('post.status');

		//定义变量
		$ret = array('status' => 0, 'info' => '');
		$nowTime = time();
		$dept = M('ADept');

		//校验参数
		if( !$name ) {
			$ret['status'] = -1;
			$ret['info'] = '部门名称不能为空！';
			$this->ajaxReturn($ret);
		}
		if( !in_array($status, array(0,1)) ) {
			$ret['status'] = -2;
			$ret['info'] = '状态值提交错误！';
			$this->ajaxReturn($ret);
		}

		//将新部门写入数据库
		$data = array();
		$data['id'] =  $id;
		$data['name'] =  $name;
		$data['pid'] = $pid;
		$data['status'] = $status;
		$data['create_time'] = $nowTime;

		$deptid = $dept->add($data); //添加
		if( $deptid ) {
			$ret['status'] = 1;
			$ret['info'] = '恭喜！成功提交!';
			$this->ajaxReturn($ret);
		} else {
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 部门信息修改
	 */
	public function modifyDept() {
		//获取参数
		$param = I('post.ekey', array(), '');

		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$dept = M('ADept');
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
				$ret['status'] = -2;
				$ret['info'] = 'the param deptid is require!';
				$this->ajaxReturn($ret);
			}
			$where  = array('id' => $val['id']);
			$dept_info = $dept->where( $where )->find();
			if( $dept_info ) {
				$dept->where( $where )->save($val); // 根据条件更新记录
				$i++;
			}
		}
		$ret['status'] = 1;
		$ret['info'] = $i . ' records have been modified!';
		$this->ajaxReturn($ret);
	}

}