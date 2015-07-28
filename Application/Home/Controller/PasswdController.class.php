<?php
namespace Home\Controller;
use Common\Lib\Message;
/**
 * 忘记密码 /密码找回
 * Enter description here ...
 * @author Jerryzhang
 *
 */

class PasswdController  extends CommonController {

	//标识
	private $flag = '';
	/**
	 * 找回密码
	 * Enter description here ...
	 */
	public function outPasswd() {
        if(IS_POST){
        	//获取变量
			$mobile   = I('post.mobile');
			$verify_code = I('post.verifycode');
       		//定义变量
			$sms = D('Sms');
			$user = M('User');
			$where = array();
			$nowTime = time();

			//检查用户名
			$where = array('mobile'=>$mobile,'status' => 1);
			$user_info = $user->where($where)->find();
			if( empty($user_info) ) {
				$this->error('抱歉！该用户已不存在！');
			}
			//检查验证码是否超时
			$where = array(
					'mobile' => array('eq', $mobile),
					'type' => array('eq', 2),
					'create_time' => array('gt', $nowTime - 120));
			$sms_info = $sms->where($where)->find();
			if($sms_info) {
				if($sms_info['verify_code'] != $verify_code) {
					$this->error('验证码输入错误！');
				}
			} else {
				$this->error('验证码输入不能超过120秒！');
			}
			$this->assign('mobile',$user_info['mobile']);
			$this->assign('username',$user_info['username']);
            $this->display('Passwd/rPasswd');
            $this->flag = 1;
            exit;
        }
        $this->display();
	}

	/**
	 * 重置密码
	 * Enter description here ...
	 */
	public function rPasswd(){
		if(IS_POST){
        	//获取变量
            $username = I('post.username');
			$mobile   = I('post.mobile');
			$passwd   = I('post.passwd');
			$password = I('post.password');
       		//定义变量
			$user = M('User');
			$where = array();

			$where = array('username' => $username,'mobile'=>$mobile, 'status' => 1);
			$user_info = $user->where($where)->find();

			if( empty($user_info) ) {
				$this->error('抱歉！该用户不存在！');
			}
			//检验密码以字母开头,长度至少8位以上
			if(!preg_match("/^[a-zA-Z\d_]{8,}$/",$passwd)){
				$this->error('密码格式不正确！');
			}
			if(md5($passwd) != md5($password) ) {
				$this->error('请确保两次输入的密码一致！');
			}
			$data = array('password'=> md5(md5($passwd)));
			$res = $user->where( $where )->save($data);
			//更改成功直接登陆
			redirect(U('Login/index'));
		}else{
			if( $this->flag ==1 ) {
				$this->display();
			}
			$this->error('非法请求！', U('Passwd/outPasswd'));
		}
	}


	/**
	 * 获取手机验证码
	 * Enter description here ...
	 */
    public function ajax_get_code() {
		//获取参数
    	$mobile 	= I('post.mobile');

    	//定义变量
    	$sms = D('Sms');
    	$user = M('User');
    	$where = array();
    	$nowTime = time();
		//验证参数
		if( $mobile ) {
			$where = array('mobile' => $mobile,'status' => 1);
			$user_info = $user->where($where)->find();
			if( empty($user_info) ) {
                $ret['status'] = 0;
                $ret['message'] = '该手机号码没有绑定用户';
				$this->ajaxReturn($ret);
			}
		}else{
			$ret['status'] = 0;
            $ret['message'] = '请确认是否用该手机号绑定过用户';
			$this->ajaxReturn($ret);
		}
		//短信平台发送手机验证码
		$verify_code = rand_string(6, 1); //随机生成6个数字
		$where = array(
			'mobile' => array('eq', $mobile),
			'type' => array('eq', 2),
			'create_time' => array('gt', $nowTime - 120));
		$sms_send = $sms->where($where)->find();
		if($sms_send) {
			$ret['status'] = 0;
			$ret['message'] = '获取失败，两次获取的时间少于120秒！';
			$this->ajaxReturn($ret);
		} else {
			$replace_data = array('verify_code'=>$verify_code,'type'=>2);
			$res =  Message::init()->send('out_password',$user_info,$replace_data);
			if(!$res){
				$ret['status'] = 0;
				$ret['message'] = '获取短信失败！';
				$this->ajaxReturn($ret);
			}
			$ret['status'] = 1;
			$ret['message'] = '获取短信成功！';
			$this->ajaxReturn($ret);
		}
    }

}