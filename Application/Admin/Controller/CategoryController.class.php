<?php
namespace Admin\Controller;
use Think\Controller;
require_once 'CommonController.class.php';
/**
 * 系统目录控制器
 * @author Jerryzhang
 * 2014-08-04
 */
class CategoryController extends CommonController {
 	/**
     * showCategory
     * 格式化数据,展示分类
     */
	public function showCategory() {
		$menu = M('AMenu');
		//查询处理
		$keywords = trim($_REQUEST['keywords']);
		$where = ! empty($keywords) ?  "name LIKE '%{$keywords}%' OR level LIKE '%{$keywords}%' OR url LIKE '%{$keywords}%' AND" : '';
		$menuData = $menu->where("$where status>=0")->order('level, id')->select();
		if($menuData) {
			$menu_list_info = genTree($menuData);
		}
		$ret = array('status' => 1, 'info' => '', 'data' => array('aData' => $menu_list_info, 'menuTree' => $menu_list_info, 'permission'=> $this->permission));
		$this->ajaxReturn($ret);
	}


	/**
	 * 新增分类节点
	*/
	public function addCate(){
		$ret = array('status' => 0, 'info' => '');

		//获取参数
		$name 			= I('post.name');
		$url			= I('post.url');
		$page			= I('post.page');
		$status			= I('post.status');
		$create_time	= time();
		$id				= I('get.id');

		//参数检验
		$ret['status'] = 0;
		if( !$name ) {
			$ret['status'] = -1;
			$ret['info'] = '分类名称不能为空！';
			$this->ajaxReturn($ret);
		}
//		if( !$url ) {
//			$ret['status'] = -1;
//			$ret['info'] = '分类数据地址不能为空！';
//			$this->ajaxReturn($ret);
//		}
//		if( !$page ) {
//			$ret['status'] = -1;
//			$ret['info'] = '分类模板不能为空！';
//			$this->ajaxReturn($ret);
//		}
		if( !in_array( $status, array(0,1,2) ) ) {
			$ret['status'] = -1;
			$ret['info'] = '状态值提交错误！';
			$this->ajaxReturn($ret);
		}

		//参数赋值
		$data['name'] 		= $name;
		$data['url'] 		= trim($url,'/');
		$data['page']		= trim($page,'/');
		$data['status'] 	= $status;
		$data['create_time']= $create_time;
		$menu 				= M('AMenu');
		//查询
		$where = array('id'=>$id);
		$menu_info = $menu->where( $where )->find();

		if( !empty($menu_info) ) { //存在父目录
			$level = $menu_info['level'].$id.'/';
			$data['level'] = $level;
			$data['pid'] = $id;
			//绑定所属权限块
			$topIds = explode('/',$level);
			//针对叶子节点 插入一条权限控制数据  二级目录请求的也加权限
			if( !empty($url) && !empty($page) ) {
				$right = M('ARight');
				$rid = $right->add( array('name'=>$name,'grp'=>$topIds[1],'url'=>trim($url,'/'),'create_time'=>time()) ); //添加
			}
		}
		$id = $menu->add($data); //添加
		if( is_numeric($id) ) {
			$ret['status'] = 1;
			$ret['info'] = '恭喜！添加成功！';
			$this->ajaxReturn($ret);
		}else {
			$ret['info'] = '内部提交错误！';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 菜单项修改
	*/
	public function updateCate() {
		//获取参数
		$param = I('post.ekey', array(), '');

		//检验
		if( !is_array( $param ) || empty( $param ) ) {
			$ret = array('status' => -1, 'info' => 'the param is require!', 'data' => '');
			$this->ajaxReturn($ret);
		}
		$i = 0;
		$menu = M('AMenu');

		foreach($param as $key => $val) { //依次更新
			if(!$val['id']) {
				$ret = array('status' => -1, 'info' => 'the param id is require!', 'data' => '');
				$this->ajaxReturn($ret);
			}
			$where  = array('id'=>$val['id']);
			$menu_info = $menu->where( $where )->find();
			if( empty($menu_info) ) continue;
			$menu->where( $where )->save($val); // 根据条件更新记录
			$i++;
		}
		$ret = array('status' => 1, 'info' => $i . ' records have been modified!', 'data' => '');
		$this->ajaxReturn($ret);
	}


	/**
	 *  支持二级目录  叶子节点的移动
	 *  目录(叶子节点)移动
	 */
	public function moveCate() {
		//移动目录
		$mid 	= I('get.mid');
		//目标目录
		$tid	= I('post.tid');
		//检验
		if( !isset($mid) || !is_numeric($mid) || !isset($tid) || !is_numeric($tid) ) {
			$ret = array('status' => -1, 'info' => 'the param is require!', 'data' => '');
			$this->ajaxReturn($ret);
		}
		$menu = M('AMenu');
		$right = M('ARight');

		//移动目录   目标目录
		$where = array('id'=>$mid);
		$menu_info = $menu->where( $where )->find();
		$tmenu_info = $menu->where( array('id'=>$tid) )->find();

		//检验
		if( count(explode('/',$tmenu_info['level'] ))>=4 ) {
			$ret = array('status' => -1, 'info' => '很抱歉！系统现目前不支持四级目录!', 'data' => '');
			$this->ajaxReturn($ret);
		}
		if( $mid == $tid ) {
			$ret = array('status' => -1, 'info' => '无法移动！目标目录和移动目录相同！', 'data' => '');
			$this->ajaxReturn($ret);
		}

		//被移动节点
		// 2 层|是否Ajax=>顶层    3层=> 顶层/2层某目录
		$levels = explode('/',$menu_info['level'] );
		switch ( count($levels) ) {
			case 3: //移动二级
				if( !empty( $menu_info['url'] ) && !empty( $menu_info['page'] ) ) { //移动的二级目录有Ajax请求
					if( count( explode('/',$tmenu_info['level']) ) == 2 ) { //移动至二级
						$data = array('level'=>'0/'.$tmenu_info['id'].'/','pid'=>$tmenu_info['id']);
						$menu->where( array('id'=>$menu_info['id']) )->save( $data );
						//修改权限所在的导航
						$right->where( array('url'=>$menu_info['url']) )->save( array('grp'=>$tmenu_info['id']) );
					} else { //移动至三级
						$data = array('level'=>'0/'.$tmenu_info['pid'].'/'.$tmenu_info['id'].'/','pid'=>$tmenu_info['id']);
						$menu->where( array('id'=>$menu_info['id']) )->save( $data );
						//修改权限所在的导航
						$right->where( array('url'=>$menu_info['url']) )->save( array('grp'=>$tmenu_info['pid']) );
					}
				} else if( count( explode('/',$tmenu_info['level']) ) == 2 ) {//目录移动至二级     	有风险  子目录中权限处的子权限无法关联移动
					$data = array('level'=>'0/'.$tmenu_info['id'].'/','pid'=>$tmenu_info['id']);
					$menu->where( array('id'=>$menu_info['id']) )->save( $data );
					//查找二级目录的子目录
					$child_menuInfo = $menu->where( array('pid'=>$menu_info['id']) )->select();
					if( !empty($child_menuInfo) ) {  //仅能移动 菜单处权限  或手动去修改 权限列表处的子权限
						foreach( $child_menuInfo as $val ) {
							$child_data = array('level'=>'0/'.$tmenu_info['id'].'/'.$menu_info['id'].'/');
							$menu->where( array('id'=>$val['id']) )->save( $child_data );
							$right->where( array('url'=>$val['url']) )->save( array('grp'=>$tmenu_info['id']) );
						}
					}
				} else {
					$ret = array('status' => -1, 'info' => '抱歉！无请求二级目录仅能移动至导航栏下！', 'data' => '');
					$this->ajaxReturn($ret);
				}
			  	break;
			case 4: //3层
			  	if ( count( explode('/',$tmenu_info['level']) ) == 2 ) { //移动至二级
			  		$data = array('level'=>'0/'.$tmenu_info['id'].'/','pid'=>$tmenu_info['id']);
					$menu->where( array('id'=>$menu_info['id']) )->save( $data );
					//修改权限所在的导航
					$right->where( array('url'=>$menu_info['url']) )->save( array('grp'=>$tmenu_info['id']) );
			  	} else if ( count( explode('/',$tmenu_info['level']) ) == 3 ) { //移动至三级
			  		$data = array('level'=>'0/'.$tmenu_info['pid'].'/'.$tmenu_info['id'].'/','pid'=>$tmenu_info['id']);
					$menu->where( array('id'=>$menu_info['id']) )->save( $data );
					//修改权限所在的导航
					$right->where( array('url'=>$menu_info['url']) )->save( array('grp'=>$tmenu_info['pid']) );
			  	} else {
			  		$ret = array('status' => -1, 'info' => '很抱歉！系统现目前不支持四级目录!', 'data' => '');
					$this->ajaxReturn($ret);
			  	}
			  	break;
		}
		$ret['status'] = 1;
		$ret['info'] = 'Move success';
		$this->ajaxReturn($ret);
	}


	/**
	 * 删除目录
	 */
	public function delCate() {
		//获取键值
		$id	= I('post.id');
		//定义变量
		$menu = M('AMenu');
		$ret 	= array('status' => 0, 'info' => '内部错误！','code' => -1, 'data' => array('aData' => 'Success')); //code表示是否删除标识
		if( !$id || !is_numeric($id) ) {
			$this->ajaxReturn($ret);
		}

		$child_menu_info = $menu->where( array('pid'=>$id) )->find();
		if( !empty($child_menu_info) ) {
			$ret['info'] = '该菜单有子目录，无法删除！请确认子目录是否可以删除！';
			$this->ajaxReturn($ret);
		}
		$menu_info = $menu->where( array('id'=>$id) )->find();
		//删除权限项
		if( !empty($menu_info) && $menu_info['url'] ) {
			$right = M('ARight');
			$right->where( array('url'=>$menu_info['url']) )->delete();
		}

		$num = $menu->where( array('id'=>$id) )->delete();
		if( is_numeric($num) ) {
			$ret = array('status' => 1, 'info' => "成功删除记录(id:{$id})", 'code' => 0,'data' => array('aData' => 'Success')); //code表示是否删除标识
		}
		$this->ajaxReturn($ret);
	}
}
