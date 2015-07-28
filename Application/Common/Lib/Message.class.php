<?php
namespace Common\Lib;

use Common\Lib\ShjzSms;

class Message
{
    private static $handle ;
	private $tpl = array(
		'register'=>array(
			'type'=>2, //短信
			'content'=>'尊敬的用户，您正在运动人生进行注册，验证码为{#verify_code#}，请勿泄露，如有疑问，请致电4000-800，详情登陆http://www.sportslives.com.cn'
		),
		'out_password'=>array(
			'type'=>2,
			'content'=>'尊敬的用户，您正在进行找回密码，验证码为{#verify_code#}，请勿泄露，如有疑问，请致电4000-800'
		)
	);
    public static function init(){
        if(!self::$handle)
        {
            self::$handle = new Message();
        }
        return self::$handle;
    }

    /**
     * @param $tpl 模版唯一标识  @todo 改字符串
     * @param $user  用户数据（用户id 或 用户数组 id，moblie，email）
     * @param array $replace_data  替换数据 数组  (如是验证码  特定字段是 verify_code )
     * @param string $time 如是短信指 短信发送时间
     * @return bool
     */
    function send($tpl,$user,$replace_data=array(),$time = ''){
        $tpl_row =$this->tpl[$tpl];
        $content = strreplace($tpl_row['content'],$replace_data,'{#','#}');

        //@todo 过滤不通知的信息
        switch($tpl_row['type']){
            case 1:
                if(!$user['email']){
                    return false;
                }
                $status=  think_send_mail($user['email'],$user['username'],$tpl_row['title'],$content);//发送状态
                $data =array(
                    'type'=>$replace_data['type'],
                    'uid'=>$user['id'],
                    'email'=>$user['email'],
                    'status'=>$status,
                    'title'=>$tpl_row['title'],
                    'content'=>$content,
                    'create_time'=>NOW_TIME,
                );
                D('Email')->add($data);
                break;
            case 2:
                if(!$user['mobile']){
                    return false;
                }
                //发送验证码 关闭
//                $is_send =ShjzSms::sendSms($user['mobile'],$content.'【城建投资】',$time);
                $is_send = 1;
                $verify_code=0;
                if($replace_data['verify_code']){
                    $verify_code=$replace_data['verify_code'];
                }
                $data =array(
                    'mobile'=>$user['mobile'],
                    'type'=>$replace_data['type'],
                    'verify_code'=>$verify_code,
                    'content'=>$content,
                    'status'=>1,
                    'create_time'=>time(),
                );
                D('Sms')->add($data);
                break;
            default :
                return false;
                break;
        }
        return true;
    }


}

