<?php
namespace Admin\Controller;
use Think\Controller;
require_once 'CommonController.class.php';
/**
 * 系统入口文件
 * @author Jerryzhang
 * 2014-08-04
 */
class IndexController extends CommonController {

    /**
    * 主页
    */
	public function index(){
		//定义变量
		$menu = M('AMenu');
		$emp = M('AEmp');
		$pos = M('APos');
		$dept = M('ADept');

		$menu_list_info = $menu->where('status>0')->order('id ASC')->select();
		if($menu_list_info) {
			$permMenuData = $this->getPermissionTree($menu_list_info);
			$menuTree = genTree($permMenuData);
			$jsMenuTree = json_encode($menuTree);
		}
		//js框架，初始化LC所需要的参数$config
		define(HTTP_BASE, 'http://'.$_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] .'/');
		$config = array();
		$config['baseUrl'] = HTTP_BASE;
        $config['siteView'] = HTTP_BASE . 'Admin/View/';   //视图脚本路径
		$config['ctrolAction'] = HTTP_BASE;   //控制器脚本路径
        $config['version'] = microtime(true);
        $config['nomenu'] = ($config['version'] == 1 ) ? true : false  ;	// 用于是否显示主菜单
        $config = json_encode($config);

        //所有用户信息
        $emps = array();
        $emp_list_info = $emp->where('status=1')->field('id,realname,username')->order('id ASC')->select();
        if( $emp_list_info ) {
        	foreach($emp_list_info as $val) {
        		$emps[$val['id']] = $val['realname'];
        		$emps[$val['id'].'_e'] = $val['username'];
        	}
        }
        //设置dialog的样式
		$css = ( date('d')%2 == 0 )  ? 'start' : ( date('d')%3 ==0 ? 'smoothness' : 'redmond');
		//默认样式
		$css = 'smoothness';
        //权限组合组
        $pathUrl = unserialize( base64_decode($_SESSION['pathUrl']) );
        //View
        $this->assign('pInfos', json_encode($posInfos) );
        $this->assign('users', json_encode($emps) );
        $this->assign('rPathInfo', json_encode(array_flip($pathUrl),true) );    //用户权限path组合
        $this->assign('user_info', $this->user_info);          //用户登录信息
        $this->assign('json_user_info', stripslashes($_SESSION['jsUser']));   //json格式用户登录信息
        $this->assign('config',$config);                       //config配置文件列表
		$this->assign('json_menu',$jsMenuTree);                //json格式的目录树
		$this->assign('menu_info', $menuTree);                 //顶部菜单列表
		$this->assign('css',$css);                 			   //样式
        $this->display();
    }



    /**
     * 系统首页信息
     */
    public function sysInfo() {
    	$emp_info = json_decode($_SESSION['jsUser'], true);
    	$sysInfo = array(
	        'php'     		=> PHP_VERSION,
	        'zend'    		=> Zend_Version(),
	        'serv'    		=> php_uname('s'),  //系统类型
	        'maxlink' 		=> @get_cfg_var('mysql.max_links')==-1 ? '不限' : @get_cfg_var('mysql.max_links'),
	        'clink'   		=> @get_cfg_var('mysql.allow_persistent') ? '是' : '否' ,
	        'mysql'   		=> '是',
	        'sermes'  		=> $_SERVER['SERVER_SOFTWARE'],
	        'maxup'   		=> get_cfg_var('upload_max_filesize') ? get_cfg_var('upload_max_filesize') : '不允许上传附件',
	        'maxrun'  		=> get_cfg_var('max_execution_time') . '秒',                                  		   //最大执行时间
	        'maxmem'  		=> get_cfg_var('memory_limit') ? get_cfg_var('memory_limit') : '无',                 //脚本运行占用最大内存
    		'cip'	  		=> $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'],
    		'user_agent'  	=> $_SERVER['HTTP_USER_AGENT'],
    	 );
    	$emp_info['create_time'] = date('Y-m-d H:i:s',$emp_info['create_time']);
    	$ret = array('status' => 1, 'info' => '', 'data' => array('userInfo' => $emp_info ,'sysInfo'=>$sysInfo));
    	$this->ajaxReturn($ret);
    }


	/**
	 * 获取用户的权限树
	 * @param array $menu_list
	 */
	private function getPermissionTree($menu_list){
		//定义变量
		//以ID为键，组织menu_list
		$idMenu_list = array();
		foreach ($menu_list as $menu) {
			$idMenu_list[$menu['id']] = $menu;
		}
		$topNodes = $parentNodes = $leafNodes = array();
		foreach($menu_list as $val) {
			if( $this->permission[$val['url']] /*&& $level == 3*/ && $val['url']) {
				$parentNode = $idMenu_list[$val['pid']];  //父节点
				$parentNodes[] = $parentNode;
				$topNodes[] = $idMenu_list[$parentNode['pid']];  //顶层节点
				if( count(explode('/', $val['level'])) == 3 && $val['url'] ) { //二级目录Ajax请求不放在叶子树上  加这个是防止二级目录在父目录前出现
					$parentNodes[] = $val;
				}else {
					$leafNodes[] = $val;
				}
			}
		}
		$pitems = array_merge($topNodes, $parentNodes, $leafNodes);
		$menu_list = $this->unique_arr($pitems,true);
		return $menu_list;
	}


	/**
	 * 去掉二维数组中重复的记录
	 * @param array $array2D
	 * @param bool $stkeep
	 * @param bool $ndformat
	 */
	private function unique_arr($array2D, $stkeep=false, $ndformat=true){
		if($stkeep) $stArr = array_keys($array2D);
		if($ndformat) $ndArr = array_keys(end($array2D));
		foreach ($array2D as $v) {
			$v = implode(',', $v);
			$temp[] = $v;
		}
		$temp = array_unique($temp);
		foreach ($temp as $k => $v) {
			if($stkeep) $k = $stArr[$k];
			if($ndformat) {
				$tempArr = explode(',',$v);
				foreach($tempArr as $ndkey => $ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
			}
			else $output[$k] = explode(',',$v);
		}
		return $output;
	}

}
