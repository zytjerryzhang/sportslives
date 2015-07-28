<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 导航控制器
 * @author Jerryzhang
 *2014-09-17
 */
class NavController extends CommonController {
	/**
	 * 导航列表
	 */
	public function navList() {
		$nav = M('Nav');
		//查询处理
		$name = I('post.name');
		$type = I('post.type');
        $where = array();
        if($type > 0){
            $where['type'] = $type;
        }
        if(!empty($name)){
            $where['name'] = array('LIKE',"%{$name}%");
        }
		$navType = array(1=>'顶部导航',2=>'底部导航');
		$pro_otem = M('Project')->where()->field('id,name')->select();

		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) >0  ? I('post.p') : 0;
		$count       = $nav->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出
	    // 进行分页数据查询
    	$nav_info = $nav->where($where)->order('id DESC')->page($currentPage.','.C('PAGESIZE'))->select();
		$ret = array('status' => 1, 'info' => $nav->_sql(), 'data' => array('aData' => $nav_info, 'navType' => $navType,'pro_item'=>$pro_otem, 'pagelink' => $pageLink, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 编辑导航信息
	 */
	public function mNavInfo() {
		//获取参数
		$param = I('post.ekey', array(), '');
		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$nav = M('Nav');

		//检验
		if( empty( $param ) || !is_array( $param ) ) {
			$ret['status'] = -1;
			$ret['info'] = 'the param is require!';
			$this->ajaxReturn($ret);
		}
		$i = 0;
		foreach($param as $key => $val) { //依次更新
			$where  = array('id' => $val['id']);
			$nav_info = $nav->where( $where )->find();
			if( !empty($nav_info) ) {
				$val = array_merge($val,array('update_time'=>time()));
				$nav->where( $where )->save($val); // 根据条件更新记录
			} else {
				unset($val['id']);
				$idx = $nav->count();	//排序数
				$nav->add( array_merge($val,array('idx'=>$idx,'update_time'=>time(),'create_time'=>time())) );
			}
			$i++;
		}
		$ret['status'] = 1;
		$ret['info'] = $i . ' records have been modified!';
		$this->ajaxReturn($ret);
	}
}