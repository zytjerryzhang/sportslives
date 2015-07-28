<?php
namespace Home\Controller;
use Think\Log;
use Common\Lib\Message;


class RegController  extends CommonController {
	/**
	 * 注册页面
	 */
	function reg(){
		$this->display();
	}


	/**
	 * 提交注册信息，并注册
	 */
	public function doReg() {
		//获取参数
		$mobile    = I('post.mobile');
		$password   = I('post.password');
		$password2  = I('post.password2');
		$verifycode = I('post.verifycode');

		//定义变量
		$user = M('User');
		//检查参数
		$dictPasswd = C('RE_PASSWD');
		$dictMobile = C('RE_MOBILE');

		//验证手机号
		if (!check_param($dictMobile, $mobile)) {
            $ret['message'] = array('mobile'=>$dictMobile['Desc']);
           	$this->error($dictMobile['Desc']);
		}
		//验证密码
		if (!check_param($dictPasswd, $password)) {
            $ret['message'] = array('passwd'=>$dictPasswd['Desc']);
           	$this->error($dictPasswd['Desc']);
		}
		if($password != $password2) {
			$this->error('请确保两次输入密码一致');
		}
		//判断用户名、手机号是否已占用
		$user_info = M('User')->where(array('mobile'=>$mobile))->find();
		if(!empty($user_info)){
			$this->error('该手机号已被注册,请登陆','Login/index');
		}
		//验证手机验证码
		$map = array(
				'type'=>1,//注册
				'mobile'=>$mobile,
				'verify_code'=>$verifycode,
				'create_time'=>array('gt', time()-120),
		);
		$sms_info = M('Sms')->where($map)->order('id DESC')->find();
		if(empty($sms_info)){
			$this->error('请确保手机验证码输入正确！');
		}else if($sms_info['create_time']<time()-120){
			$this->error('验证码输入不能超过120秒！');
		}

		//成功登录跳转
		$user_data = array();
		$user_data['username'] = 'YDH_'.substr($mobile,-4,4);
		$user_data['mobile'] = $mobile;
		$user_data['client'] = 1;
		$user_data['password'] = md5(md5($password));
		$user_data['login_time'] = date('Y-m-d H:i:s');
		$user_data['status'] = 1;
		$user_data['create_time'] = date('Y-m-d H:i:s');
		$uid = $user->add($user_data);
		if($uid) {
			$this->redirect('Login/index');
		}
	}

    /**
     * 获取注册短信验证码
     */
    public function getMobileVerifyCode() {
		//获取参数
    	$mobile = I('post.mobile');

    	//定义变量
    	$user = M('User');
    	$sms = D('Sms');
    	$map = array();
    	$ret = array('status' => 0, 'message'=>'');
    	$nowUnixTime = time();

		//检查参数
		$dictMobile = C('RE_MOBILE');
    	//检查手机
    	if (!check_param($dictMobile, $mobile)) {
    		$ret['status'] = 0;
    		$ret['message'] = $dictMobile['Desc'];
    		$this->ajaxReturn($ret);
    	}
    	$user_info = $user->where(array('mobile'=>$mobile))->find();
    	if($user_info){
    		$ret['status'] = 0;
    		$ret['message'] = '手机号已注册';
    		$this->ajaxReturn($ret);
    	}

		//短信平台发送手机验证码
		$mobileVerifyCode = rand_string(6, 1); //随机生成6个数字
		//两次获取时间<60秒
		$sms_info = M('Sms')->where(array('mobile'=>$mobile,'type'=>1,'create_time'=>array('egt',time()-120)))->find();
		if (!empty($sms_info)) {
			$ret['status'] = -4;
			$ret['message'] = '两次获取时间不能少于120秒';
			$this->ajaxReturn($ret);
		}

		$replace_data = array('verify_code'=>$mobileVerifyCode,'type'=>1);
		$user = array('id'=>0,'mobile'=>$mobile);
		$status = Message::init()->send('register',$user,$replace_data);

		if ($status) {
			$ret['status'] = 1;
			$ret['message'] = '短信已发送，如未收到120秒后重发';
			$this->ajaxReturn($ret);
		} else {
			$ret['status'] = 0;
			$ret['message'] = '短信发送失败，请稍候再试';
			$this->ajaxReturn($ret);
		}
		$this->ajaxReturn($ret);
    }
}