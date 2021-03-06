<?php
namespace Home\Controller;

use Common\Lib\Log;
use Common\Lib\Message;
use Think\Exception;

class UserController extends CommonController
{
    private $user = null;

    function __construct()
    {
        parent::__construct();
        if (UID <= 0) {
            redirect(U('login/login'));
        }
        $this->user = session('user');
    }

    public function index(){
        $this->assign('__BODY_ID__', 'order');
        $this->assign('nav','index');
    	$this->display();
    }
}
