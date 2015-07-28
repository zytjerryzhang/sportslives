<?php
namespace Common\Lib;

class ShjzSms {

	static private $account = 'jzyy609';
	static private $password = 'gaowenjie';
	static private $newPassword = '';


	/**
	 * 验证用户密码
	 * @return boolean
	 */
	static public function validateUser() {
		$url = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/validateUser';
		$result = curl_post($url, array('account'=>self::$account, 'password'=>self::$password));

		if ($result === false) {
			//发送http请求失败
			Log::write('sms: curl_post请求失败', Log::ERR);
		} else {
			if ($result === '1') {
				return true;
			} else if ($result === '0') {
				Log::write('sms: 验证用户密码成功', Log::ERR);
			} else {
				Log::write('sms: 处理失败', Log::ERR);
			}
		}
		return false;
	}


	/**
	 * 修改密码
	 * @return boolean
	 */
	static public function modifyPassword() {
		$url = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/modifyPassword';
		$result = curl_post($url, array('account'=>self::$account,
				'oldPassword'=>self::$password, 'newPassword'=>self::$newPassword));

		if ($result === false) {
			//发送http请求失败
			Log::write('sms: curl_post请求失败', Log::ERR);
		} else {
			if ($result === '1') {
				return true;
			} else if ($result === '0') {
				Log::write('sms: 修改用户密码失败', Log::ERR);
			} else {
				Log::write('sms: 处理失败', Log::ERR);
			}
		}
		return false;
	}


	/**
	 * 获取余额等用户基本信息
	 * @return array | boolean
	 */
	static public function getUserInfo() {
		$url = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/getUserInfo';
		$result = curl_post($url, array('account'=>self::$account, 'password'=>self::$password));
		if ($result === false) {
			//发送http请求失败
			Log::write('sms: curl_post请求失败', Log::ERR);
		} else {
			if ($result) {
				return simplexml_load_string($result);
			} else {
				Log::write('sms: 处理失败', Log::ERR);
			}
		}
		return false;
	}



	/**
	 * 获取短信
	 * @return bool | array
	 */
	static public function getReceivedMsg() {
		$url = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/getReceivedMsg';
		$result = curl_post($url, array('account'=>self::$account, 'password'=>self::$password));
		if ($result === false) {
			//发送http请求失败
			Log::write('sms: curl_post请求失败', Log::ERR);
		} else {
			return $result;
		}
		return false;
	}


	/**
	 * 发送短信
	 * @param string $phones：以半角分号分隔的电话号码序列（最后必须以一个分号结束）
	 * @param string $content
	 * @param number $postFixNumber
	 * @param string $sendTime
	 * @param number $sendType
	 * @return bool
	 */
	static public function sendSms($phones, $content, $sendTime='') {
		$url = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage';
		$param = array('account'=>self::$account, 'password'=>self::$password, 'destmobile'=>$phones, 'msgText'=>$content, 'sendDateTime'=>$sendTime);
        $result = curl_post($url, $param);
		if ($result === false) {
			//发送http请求失败
			Log::write('sms: curl_post请求失败', Log::ERR);
		} else {
			if ($result > 0) {
				return true;
			} else {
 				Log::write('sms: 发送短信失败' . $result, Log::ERR);
			}
		}
		return false;
	}


} //End Class