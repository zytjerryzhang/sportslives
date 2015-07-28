<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-11-19
 * Time: 下午1:43
 */
namespace Home\Controller;

class LoginController extends CommonController {

    //用户登录
    public function index() {
		//获取参数
		$mobile = I('post.mobile');
		$passwd   = I('post.passwd');
		$verify_code = I('post.verify_code');

		//定义变量
		$user     = M('User');
		//检查参数
		if(IS_POST){
			//查询数据
			$map = array(
				'mobile' => $mobile,
				'password'   => md5(md5($passwd)),
			);
			$dictMobile = C('RE_MOBILE');
			//检查用户名
			if (!check_param($dictMobile, $mobile)) {
	            $ret = array('mobile'=>$dictMobile['Desc']);
				$this->error($dictMobile['Desc']);
			}
			$dictPasswd = C('RE_PASSWD');
			if (!check_param($dictPasswd, $passwd)) {
	            $ret = array('password'=>$dictPasswd['Desc']);
				$this->error($dictPasswd['Desc']);
			}
			//检测验证码
			if(!check_verify($verify_code)){
				$this->error('验证码输入错误');
			}
			$user_info = $user->where($map)->find();
			if ($user_info) {
				if ($user_info['status'] == '1') {
					$uid = $user_info['id'];
					//设定session值，然后登录
					$session_user = $user->where(array('id'=>$uid))->find();
					session('user',$session_user);
					//根据来路地址 登陆后即跳转到当前地址去
					$refurl = I('post.refurl');
                    if(empty($refurl)){
                        $refurl = U('Index/index');
                    }else{
                        $refurl = urldecode($refurl);
                    }
                    redirect($refurl);
//					$this->redirect($refurl);
				} elseif ($user_info['status'] == '0') {
					$this->error('你的帐号已锁定或未激活');
				}
			} else {
				$this->error('您输入的用户名和密码不匹配');
			}
		} else {
			$refurl = I('get.refurl');
	        if(empty($refurl)){
	            $refurl = $_SERVER['HTTP_REFERER'];
	        }else{
	            $refurl = urldecode($refurl);
	        }
	        $refurl = strtolower($refurl);
	        $host = parse_url($refurl);
	        if($_SERVER['HTTP_HOST'] == $host['host']){ //在同一域名中
	            if(strpos($host['path'],'login') > 0){ //除 登录 注册
	                $refurl = U('Index/index');
	            }elseif(strpos($host['path'],'reg') > 0){
	                $refurl = U('Index/index');
	            }
	        }else{
	            $refurl = U('Index/index');
	        }
	        $refurl=urldecode($refurl);
			$this->assign('refurl', $refurl);
			$this->display('login');
		}
    }

    function login(){
        redirect('/login');
    }

    //生成验证码
    public function verify(){
        $config = array(
            'imageH' => 45,
            'fontSize' => 24,            // 验证码字体大小
            'length' =>5,
            'useNoise' => false,        // 关闭验证码杂点
        );
        $verify = new \Think\Verify($config);
        $verify->entry(1);
    }

    //退出
    public function logout(){
        session('user',null);
        session('UID',null);
        redirect('/login/login');
    }

}