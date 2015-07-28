<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 职位控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class PosController extends CommonController {
	/**
	 * 获取职位信息列表
	 */
	public function posList() {
		$pos = M('APos');
		//查询处理
		$keywords = I('post.keywords');
		$where    = ! empty($keywords) ?  "id LIKE '%{$keywords}%' OR name LIKE '%{$keywords}%'" : '';
		
		//分页查询
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $pos->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出

		$depar = M('ADept');
    	$dept_list_info = $depar->field('id,name')->select();
    	foreach($dept_list_info as $val) {
    		$dept_info[$val['id']] = $val['name'];
    	}
	    //进行分页数据查询
    	$pos_list_info = $pos->where($where)->order('id DESC')->page($currentPage, C('PAGESIZE'))->select();
    	foreach($pos_list_info as $key=>$val) {
    		$pos_list_info[$key]['dept_name'] = $dept_info[$val['dept_id']];
    	}
 
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $pos_list_info,'dept_data'=>$dept_list_info,'pagelink' => $pageLink, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 新增职位
	 */
	public function addPos() {
		$ret['status'] = -1;

		//获取参数
		$id			= I('post.id');
		$name		= I('post.name');
		$dept_id	= I('post.dept_id');
		$level		= I('post.level');
		$status		= I('post.status');

		//参数检验
		$ret['status'] = 0;
		if( !is_numeric($id) ) {
			$ret['info'] = '职位ID不能为空！';
			$this->ajaxReturn($ret);
		}
		if( !$name ) {
			$ret['info'] = '职位名称不能为空！';
			$this->ajaxReturn($ret);
		}
		if( !in_array( $level, array(1,2,3,4,5,6) ) ) {
			$ret['info'] = '职位级别值提交错误！';
			$this->ajaxReturn($ret);
		}
		if( !in_array( $status, array(0,1) ) ) {
			$ret['info'] = '状态值提交错误！';
			$this->ajaxReturn($ret);
		}

		//变量赋值
		$create_time = time();
		$pos = M('APos');
		$data['id'] 		= $id;
		$data['name'] 		= $name;
		$data['dept_id'] 	= $dept_id;
		$data['level'] 		= $level;
		$data['status'] 	= $status;
		$data['create_time']= $create_time;

		$posid = $pos->add($data); //添加
		if( is_numeric($posid) ) {
			$ret['status'] = 1;
			$ret['info'] = '恭喜！添加成功！';
			$this->ajaxReturn($ret);
		}else {
			$ret['status'] = -1;
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 职位信息修改
	 */
	public function modifyPos() {
		//获取参数
		$param = I('post.ekey', array(), '');

		//检验
		if( !is_array( $param ) || empty( $param ) ) {
			$this->ajaxReturn(array('status' => -1, 'info' => 'the param is require!'));
		}
		$i = 0;
		$pos = M('APos');
		foreach($param as $key => $val) { //依次更新
			if(!$val['id']) {
				$this->ajaxReturn(array('status' => -1, 'info' => 'the param id is require!','data'=>array()));
			}
			$where  = array('id'=>$val['id']);
			$pos_info = $pos->where( $where )->find();
			if( empty( $pos_info ) ) continue;

			$pos->where( $where )->save($val); // 根据条件更新记录
			$i++;
		}
		$this->ajaxReturn(array('status' => 1, 'info' => $i . ' records have been modified!','data'=>array()));
	}


	/**
	 * 角色权限
	 */
	public function posRight() {
		$pos = M('APos');
		//查询处理
		$keywords = I('post.keywords');
		$where = ! empty($keywords) ?  "id LIKE '%{$keywords}%' OR name LIKE '%{$keywords}%'" : '';
		
		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) >0  ? I ('post.p') : 1;
		$count       = $pos->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出
		$count       = $pos->where($where)->count();

		$depar = M('ADept');
    	$dept_list_info = $depar->field('id,name')->select();
    	foreach($dept_list_info as $val) {
    		$dept_info[$val['id']] = $val['name'];
    	}
	    // 进行分页数据查询
    	$pos_list_info = $pos->where($where)->order('id DESC')->page($currentPage.','.C('PAGESIZE'))->select();
    	foreach($pos_list_info as $key=>$val) {
    		$pos_list_info[$key]['dept_name'] = $dept_info[$val['id']];
    	}
   
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $pos_list_info,'dept_data'=>$dept_list_info,'pagelink' => $pageLink, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 获取角色已经分配的权限
	 */
	public function getPosRight() {
		$ret['status'] = -1;
		//获取变量
		$pos_id			= I('get.pos_id');
		if( !is_numeric($pos_id) ) {
			$ret['info'] = '参数错误！';
			$this->ajaxReturn($ret);
		}
		
		//获取顶级目录
		$menu_list  = $this->getTopmenu();
		//获取权限
		$right = M('ARight');
		$rightlist = $right->order('url ASC')->select();
		if( !empty($rightlist) ) {
			$posRight = M('APosRight');
			$posRight_info = $posRight->where('pos_id='.$pos_id)->select();//角色已有权限
			foreach($posRight_info as $val) { //用户权限
				$prids[] = $val['rid'];
			}
			foreach($rightlist as $key=>$val) {
				if(in_array($val['id'], $prids) ) {
					$rightlist[$key]['isper'] = 1;
				} else {
					$rightlist[$key]['isper'] = 0;
				}
			}
			$this->ajaxReturn(array('menu_list'=>$menu_list,'right_list'=>$rightlist,'info'=>'获取权限成功','status'=>1));
		}else {
			exit(json_encode(array('message'=>'系统没有数据！','msg'=>0)) );
		}
	}


	/**
	 * 设置角色权限
	 */
	public function setPosRight() {
		$ret['status'] = -1;

		//获取参数
		$pos_id 	= I('get.pos_id');
		$rids		= I('post.rid');

		//参数检验
		$ret['status'] = 0;
		if( !is_numeric($pos_id) ) {
			$ret['info'] = '请确认职位ID是否正确！';
			$this->ajaxReturn($ret);
		}
		if( empty($rids) ) {
			$ret['info'] = '请你选择需要权限！！';
			$this->ajaxReturn($ret);
		}

		//角色已有权限
		$roleRight = M('APosRight');
		$where  = array('pos_id'=>$pos_id);
		$roleHasRight_list_info = $roleRight->field('rid,pos_id')->where( $where )->select();//角色已有权限
		//角色权限取消
		if( !empty($roleHasRight_list_info) ) {
			foreach($roleHasRight_list_info as $val) {
			 	if( !in_array($val['rid'], $rids ) ) { //取消的权限
			 		$r_where = array('pos_id'=>$pos_id,'rid'=>$val['rid']);
			 		$roleRight_o_info = $roleRight->where( $r_where )->find();
					if( empty($roleRight_o_info) ) continue;
					$roleRight->delete( $roleRight_o_info['id'] ); //取消权限
			 	}
			}
		}
		//角色添加权限
		foreach($rids as $val){
			if( !$val || !is_numeric($val) ) continue;
			$posRight = M('APosRight');
			$p_where = array('pos_id'=>$pos_id,'rid'=>$val);
			$posRight_info = $posRight->where($p_where)->find();
			if(!empty($posRight_info) ) continue;
			$data = array('pos_id'=>$pos_id,'rid'=>$val,'create_time'=>time() );
			$prid = $posRight->add( $data ); //添加
		}
		$prid = is_numeric( $prid ) ? $prid : 1;
		if( is_numeric($prid) ) {
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
