<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * 登录控制器
 * @author Jerryzhang
 * 2014-08-05
 */
class LoginController extends Controller {
	/**
	 * 登录页面
	 */
	public function login() {
		$refurl = I('get.refurl');
        $refurl=urldecode($refurl);
		$this->assign('refurl', $refurl);
		$this->display();
	}


	/**
	 * 登录
	 */
	public function doLogin() {
		//获取参数
		$username = I('post.username');
		$passwd = md5(I('post.passwd'));
		//定义变量
		$emp = M('AEmp');
		$dept = M('ADept');
		$pos = M('APos');
		$posRight = M('APosRight');
		$empRight = M('AEmpRight');
		$right = M('ARight');
		$nowTime = time();
		$ret = array('status' => 0, 'info' => '');

		//校验参数，及登陆
		$where = array('username'=>$username, 'passwd'=>$passwd, 'status'=>1);
		$emp_info = $emp->where($where)->find();
		if ($emp_info) {
			if ($emp_info['dept_id']) {
				$where = array('id' => $emp_info['dept_id']);
				$dept_info = $dept->where($where)->find();
				if ($dept_info) {
					$emp_info['dept_name'] = $dept_info['name'];
				}
			}
			if (is_numeric($emp_info['pos_id'])) {
				$where = array('id' => $emp_info['pos_id']);
				$pos_info = $pos->where($where)->find();
				if ($pos_info) {
					$emp_info['pos_name'] = $pos_info['name'];
					$emp_info['user_level'] = $pos_info['level'];
				}
			}
			//以下逻辑实现角色权限和用户权限整合
			//设置一个管理员账号
			if ($emp_info['username'] == 'admin') {
				$right_list_info = $right->field('id, url')->select();
				if ($right_list_info) {
					foreach($right_list_info as $val) { //角色权限
//						$emp_info['rid'][] = $val['id'];
						$pathUrl[] = $val['url'];
					}
				}
			} else { //非管理员账号
				//角色权限
				$posRight_list_info = $posRight->table('sl_a_pos_right as pr')
					->join('sl_a_right as r on pr.rid=r.id')
					->field('r.*')
					->where('pr.pos_id='.$emp_info['pos_id'])
					->select();
				//用户权限
				$empRight_list_info = $empRight->table('sl_a_emp_right as ur')
					->join('sl_a_right as r on ur.rid=r.id')
					->field('r.*')
					->where('ur.eid='.$emp_info['id'])
					->select();
				if ($empRight_list_info) {
					foreach($empRight_list_info as $val) { //用户权限
//						$emp_info['rid'][] = $val['id'];
						$pathUrl[] = $val['url'];
					}
				}
				if ($posRight_list_info) {
					foreach($posRight_list_info as $val) { //角色权限
//						$emp_info['rid'][] = $val['id'];
						$pathUrl[] = $val['url'];
					}
				}
			}
			$where = array('id' => $emp_info['id']);
			$data = array('login_time' => $nowTime);
			$emp->where($where)->save($data); // 根据条件更新记录
			//对rid去重 ：针对权限rid去重
			//权限用Session来存储
			session('pathUrl',base64_encode( serialize( array_unique($pathUrl) ) ) );
//			$emp_info['rid'] = array_unique($emp_info['rid']);
			$jsUser =  json_encode( $emp_info );
			session('jsUser', $jsUser);
			$ret['status'] = 1;
			$ret['info'] = '登陆成功';
			$refurl = I('post.refurl');
			if(empty($refurl)){
				$ret['refurl'] = '/';
			}else{
				$ret['refurl'] = 'http://'.$_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] .'/'.'#'.urldecode($refurl);
			}
			$this->ajaxReturn($ret);
		} else {
			$ret['info'] = '验证失败，请您确认用户为正常用户。';
			$this->ajaxReturn($ret);
		}
	}


	/**
	 * 退出
	 */
	public function logout() {
		session('jsUser', null);
		$this->redirect('/Login/login');
	}


	/**
	 * 修改用户密码
	 */
	public function updatePasswd() {
		//获取参数
		$passwd = I('post.passwd');
		$surePasswd = I('post.surePasswd');

		//定义变量
		$emp = M('AEmp');
		$ret = array('status' => 0, 'info' => '');

		//校验参数
		if (!$passwd) {
			$ret['info'] = '新密码不能为空。';
			$this->ajaxReturn($ret);
		}
		if (!$surePasswd || $passwd != $surePasswd) {
			$ret['info'] = '请确认两次输入的密码一致。';
			$this->ajaxReturn($ret);
		}

		//更改密码，并重新登陆
		$empInfo = json_decode($_SESSION['jsUser'], true);
		$where = array('id' => $empInfo['id']);
		$emp_info = $emp->where($where)->find();
		if (!$emp_info) {
			$ret['info'] = '用户信息不存在，修改未生效。';
			$this->ajaxReturn($ret);
		}
		$emp_data = array();
		$pos_data = array();
		$data = array('passwd'=>md5($passwd));
		$emp->where($where)->save($data); // 根据条件更新记录

		//修改密码重新登录系统
		foreach($_COOKIE as $key => $val) {
			my_setcookie($key, '', -3600);
		}
		$ret['status'] = 1;
		$ret['info'] = '密码修改成功。';
		$this->ajaxReturn($ret);
	}

}