<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 权限控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class RightController extends CommonController {
	/**
	 * 获取权限列表
	 */
	public function rightList() {
		$right = M('ARight');
		//查询处理
		$keywords = I('post.keywords');
		$where = ! empty($keywords) ?  "id LIKE '%{$keywords}%' OR name LIKE '%{$keywords}%' OR url LIKE '%{$keywords}%'" : '';
		//获取顶级目录
		$menu_list  = $this->getTopmenu();
		//分页查询
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $right->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出
		//点击排序
		$orderby	 = I('post.orderby');
		$orderby	 = !empty( $orderby ) ? $orderby :  'id desc';
		$orderInfo   = !empty( $orderby ) ? explode(' ',$orderby) : '';
	    // 进行分页数据查询
    	$right_info = $right->where($where)->order( $orderby )->page( $currentPage . ',' . C('PAGESIZE') )->select();
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $right_info,'menu_list'=>$menu_list,'pagelink' => $pageLink, 'orderInfo'=>$orderInfo, 'permission'=>$this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 获取用户权限列表
	 */
	public function userRight() {
		$emp       = M('AEmp');

		//查询处理
		$keywords = I('post.keywords');
		$where = !empty($keywords) ?  "id LIKE '%{$keywords}%' OR realname LIKE '%{$keywords}%' OR username LIKE '%{$keywords}%' OR mobile LIKE '%{$keywords}%' OR email LIKE '%{$keywords}%'" : '';

		//分页处理
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count      = $emp->where( $where )->count();       // 查询满足要求的总记录数 $map表示查询条件
	    $page       = new  Page($count, C('PAGESIZE'));  	// 实例化分页类 传入总记录数
	    $pageLink = $page->show();// 分页显示输出

	    // 进行分页数据查询
    	$emp_info = $emp->where( $where )->order('id DESC')->page( $currentPage . ',' . C('PAGESIZE') )->select();
    	$pos = M('APos');
    	$dept = M('ADept');
    	foreach($emp_info as $key => $val) {
			$dept_info = $pos_info = array();
	    	if( isset( $val['dept_id'] ) && is_numeric( $val['dept_id'] )){
				$dept_info = $dept->where( array('id'=>$val['dept_id']) )->find();
	    	}
    		if( isset( $val['pos_id'] ) && is_numeric( $val['pos_id'] )){
				$pos_info = $pos->where( array('id'=>$val['pos_id']) )->find();
	    	}
	    	$emp_info[$key]['dept_name'] = empty( $dept_info ) ? '' : $dept_info['name'];
	    	$emp_info[$key]['pos_name'] = empty( $pos_info ) ? '' : $pos_info['name'];
    	}
    	$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $emp_info,'pagelink' => $pageLink, 'permission'=>$this->permission));
    	$this->ajaxReturn($ret);
	}


	/**
	 * 添加一个权限
	 */
	public function addRight(){
		$ret['status'] = -1;

		//获取变量
		$name		= I('post.name');
		$url		= I('post.url');
		$grp		= I('post.grp');

		//检验
		$ret['status'] = 0;
		if( !$name ) {
			$ret['info'] = '权限名称不能为空！';
			$this->ajaxReturn($ret);
		}
		if( !$url ) {
			$ret['info'] = '模块名称不能为空！';
			$this->ajaxReturn($ret);
		}

		//赋值
		$data['grp']		 = $grp;
		$data['name']		 = $name;
		$data['url']		 = trim($url,'/'); //去除前后的/
		$data['create_time'] = time();

		$right = M('ARight');
		$rid = $right->add($data); //添加

		if(is_numeric($rid) ) {
			//每添加一个权限给指定的岗位加个权限 (线上或者 dev得看指定角色的角色ID而定)
			$pos_right = M('APosRight');
			$pos_right->add(array('rid'=>$rid, 'pos_id'=>15,'create_time'=>time())); //默认给技术总监加上权限
			$ret['status'] = 1;
			$ret['info'] = '恭喜！成功提交!';
			$this->ajaxReturn($ret);
		}else {
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 删除一条权限项
	 */
	public function delRight() {
		//获取键值
		$id	= I('post.id');
		//定义变量
		$right = M('ARight');
		$ret 	= array('status' => 0, 'info' => '内部错误！','code' => -1, 'data' => array('aData' => 'Success'));
		if( !$id || !is_numeric($id) ) {
			$this->ajaxReturn($ret);
		}

		$num = $right->where( array('id'=>$id) )->delete();
		if( is_numeric($num) ) {
			$uright = M('AEmpRight');
			$uright->where( array('rid'=>$id) )->delete();
			$pright = M('APosRight');
			$pright->where( array('rid'=>$id) )->delete();
			$ret = array('status' => 1, 'info' => "成功删除记录(id:{$id})", 'code' => 0,'data' => array('aData' => 'Success')); //code表示是否删除标识
		}
		$this->ajaxReturn($ret);
	}

	/**
	 * 权限名称修改
	 */
	public function modifyRight(){
		//获取参数
		$param = I('post.ekey', array(), '');

		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$right = M('ARight');
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
			$right_info = $right->where( $where )->find();
			if( $right_info ) {
				$right->where( $where )->save($val); // 根据条件更新记录
				$i++;
			}
		}
		$ret['status'] = 1;
		$ret['info'] = $i . ' records have been modified!';
		$this->ajaxReturn($ret);
	}


	/**
	 * 获取用户已经分配的权限
	 */
	public function getUserRight() {
		$ret['status'] = -1;
		$eid	= I('get.uid');

		if( !is_numeric($eid) ) {
			$ret['info'] = '确认用户ID正确！';
			$this->ajaxReturn($ret);
		}

		//用户信息
		$emp = M('AEmp');
		$u_where = array('id'=>$eid);
		$emp_info = $emp->where( $u_where )->find();
		if( empty($emp_info) || $emp_info['status'] == 0 ) {
			$ret['info'] = '用户信息不存在或用户为禁用账号！';
			$this->ajaxReturn($ret);
		}
		$right = M('ARight');
		$rightlist = $right->where()->order('url ASC')->select();

		//用户处需要整合角色权限
		if( !empty($rightlist) ) {
			$empRight = M('AEmpRight');
			$ur_where = array('eid'=>$eid);
			$empRight = $empRight->where( $ur_where )->select();//用户已有权限
			foreach($empRight as $val) { //用户权限
				$urids[] = $val['rid'];
			}
			//角色权限
			$posRight = M('APosRight');
			$pr_where = array('pos_id'=>$emp_info['pos_id']);
			$posRight_info = $posRight->where( $pr_where )->select();
			foreach($posRight_info as $val) { //用户权限
				$prids[] = $val['rid'];
			}

			//获取顶级目录
			$menu_list  = $this->getTopmenu();
			foreach($rightlist as $key=>$val) { //对权限列表作归类处理
				if( in_array($val['id'], $urids) || in_array($val['id'], $prids) ) {
					$rightlist[$key]['isper'] = 1;
				} else {
					$rightlist[$key]['isper'] = 0;
				}
				if( in_array($val['id'], $prids) ) {
					$rightlist[$key]['isroleper'] = 1; //角色权限
				} else {
					$rightlist[$key]['isroleper'] = 0; //角色权限
				}
			}
			$this->ajaxReturn(array('menu_list'=>$menu_list,'right_list'=>$rightlist,'info'=>'获取权限成功','status'=>1));
		}else {
			$ret['info'] = '系统没有数据！';
			$this->ajaxReturn($ret);
		}
	}

	/**
	 * 设置用户权限
	 */
	public function setUserRight(){
		$ret['status']	= -1;
		$eid	= I('get.uid');
		$rids	= I('post.rid');

		if( !is_numeric($eid) ) {
			$ret['info'] = '确认用户ID正确！';
			$this->ajaxReturn($ret);
		}
		if( empty($rids) ) {
			$ret['info'] = '请你选择需要权限！！';
			$this->ajaxReturn($ret);
		}

		$empRight = M('AEmpRight');
		$u_where = array('eid'=>$eid);
		$empHasRight_info = $empRight->field('rid,eid')->where( $u_where )->select();//用户已有权限
		//权限取消
		if( !empty($empHasRight_info) ) {
			foreach($empHasRight_info as $val) {
			 	if( !in_array($val['rid'], $rids ) ) { //取消的权限
			 		$empRight_o_info = $empRight->where(array('eid'=>$eid,'rid'=>$val['rid']) )->find();
					if( empty($empRight_o_info) ) continue;
					$empRight->delete($empRight_o_info['ur_id'] ); //取消权限
			 	}
			}
		}
		//新增权限  (新增权限若角色已有该权限 则用户表中不插入权限项)
		$emp = M('AEmp');
		$u_where = array('id'=>$eid);
		$emp_info = $emp->where( $u_where )->find();
		//角色权限
		$roleRight = M('APosRight');
		$roleRight_info = $roleRight->field('rid,pos_id')->where(array('pos_id'=>$emp_info['pos_id']))->select();
		if( !empty($roleRight_info) ) {
			foreach( $roleRight_info as $val ) {
				$roleRids[] = $val['rid'];
			}
		}
		foreach($rids as $val) {
			if( !$val || !is_numeric($val) ) continue;
			$empRight = M('AEmpRight');
			$empRight_info = $empRight->where(array('eid'=>$eid,'rid'=>$val))->find();
			if( !empty($empRight_info) || in_array($val, $roleRids) ) continue; //角色已经有该权限的  不插入到用户权限表
			$urid = $empRight->add( array('eid'=>$eid,'rid'=>$val,'create_time'=>time() )); //添加
		}
		$urid = is_numeric( $urid ) ? $urid : 1;
		if( is_numeric($urid) ) {
			$ret['status'] = 1;
			$ret['info'] = '恭喜！成功提交!';
			$this->ajaxReturn($ret);
		} else {
			$ret['status'] = -1;
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 *  获取顶级菜单目录
	 */
	private function getTopmenu() {
		$menu		= M('AMenu');
		//初始menu_info
		$menu_list	= $menu->where( array('pid'=>0) )->field('id,name')->order('id ASC')->select();
		return !empty( $menu_list ) ? $menu_list : array();
	}
}
