<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 系统入口文件
 * @author Jerryzhang
 * 2014-08-07
 */
class CommonController extends Controller {
    //初始化公共变量
    public $user_info = array();	//用户信息，包括权限信息
    public $rid_info = array();		//right id
    public $permission = array();	//权限信息，内容为URL，如Index/index


    /**
     * 1 构造函数 判断每次请求是否用户信息存在
     * 2 以后的用户权限控制 扩展
     */
    public function __construct(){
        parent::__construct();
        if ( isset($_SESSION['jsUser']) ) {	//COOKIE存在jsUser，则已登陆或自动登陆
            $this->user_info = json_decode($_SESSION['jsUser'], true);
            //暂未用到 先注释 用户拥有权限ID组合
            //			$this->rid_info = $this->user_info['rid'];
            $pathUrl = unserialize( base64_decode( $_SESSION['pathUrl'] ) );
            foreach ($pathUrl as $url) {
                $this->permission[$url] = 1;
            }
            //判断当前请求是否有权限
            if ('admin' != $this->user_info['username'] && !in_array($_SERVER['PATH_INFO'], array('Index/index', 'Login/login', '') ) ) {
                $rote = $_SERVER['PATH_INFO'];
                $rotes = explode('/',$rote);
                $rote = count( $rotes )> 2 ? trim( $rotes[0].'/'.$rotes[1] ) : $rote;
                if ( $this->permission[$rote] != 1 ) {
                    if (IS_AJAX) {
                        $ret = array('status' => 0, 'info' => '亲！你没有权限操作此项！');
                        $this->ajaxReturn($ret);
                    } else {
                        $this->error('亲！你没有权限操作此项！');
                    }
                }
            }
        } else {	//初次（或重新）进系统
            if( in_array($_SERVER['PATH_INFO'], array('Index/index', 'Login/login', '') ) )	{
                $this->redirect('/Login/login');
            }else { //请求跳转
               $ret = array('status' => 99, 'info' => '亲！用户Session过期！！请重新登录！');
               $this->ajaxReturn($ret);
            }
            return false;
        }
    }

    /**
     * 上传图片
     *
     * @param $name $_FILES[$name]中的name
     * @param $dir 上传子目录，默认场馆
     * @return string|false 上传文件名
     */
    public function upload($name='files', $dir = 'gym') {
        $upload = new \Think\Upload();
        $upload->maxSize    = 1048576;
        $upload->exts       = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath   = dirname(APP_PATH) . '/Public/uploads/';
        $upload->savePath   = $dir . '/';
        $upload->subName    = array('date', array('Ymd', NOW_TIME));
        $upload->saveName   = substr(md5(microtime(true)), 0, 9) . rand(10, 99);
        $info   =   $upload->uploadOne($_FILES[$name]);
        if (!$info) {// 上传错误提示错误信息
            echo $upload->getError();
            return false;
        } else {// 上传成功 获取上传文件信息
            return $info['savename'];
        }
    }

    public function add($data) {
        if (!empty($data['id'])) {
            //更新
            $id = $data['id'];
            $this->model->where('id=' . $id)->save($data);
        } else {
            $id = $this->model->add($data);
        }

        if( is_numeric($id) ) {
            $ret['status'] = 1;
            $ret['info']   = '恭喜！添加成功！';
            $ret['data']   = $id;
            $this->ajaxReturn($ret);
        }else {
            $ret['status'] = 0;
            $ret['info'] = $this->model->getError();
            $this->ajaxReturn($ret);
        }
    }

    public function listData($return = false) {
        $data = $this->model->select();
        //echo $this->model->getLastSql();
        if ($return) return $data;

        $data = array('aData' => $data);
        $this->ajaxReturnFormat($data);
    }

    public function ajaxReturnFormat($data, $status = 1, $info = '') {
        $data['permission'] = $this->permission;
        $ret = array(
            'status'    => $status,
            'info'      => $info,
            'data'      => $data
        );
        $this->ajaxReturn($ret);
    }

    public function getinfo() {
        $id     = I('get.id', 0, 'intval');
        $data   = $this->model->where("id=$id")->find();
        $this->ajaxReturn($data);
    }

    /**
     * 修改
     */
    public function update() {
        //获取参数
        $param = I('post.ekey', array(), '');

        //检验
        if( !is_array( $param ) || empty( $param ) ) {
            $ret = array('status' => -1, 'info' => 'the param is require!', 'data' => '');
            $this->ajaxReturn($ret);
        }
        $i = 0;

        foreach($param as $key => $val) { //依次更新
            if(!$val['id']) {
                $ret = array('status' => -1, 'info' => 'the param id is require!', 'data' => '');
                $this->ajaxReturn($ret);
            }
            $where  = array('id'=>$val['id']);
            $menu_info = $this->model->where( $where )->find();
            if( empty($menu_info) ) continue;
            $val['update_time'] = date('Y-m-d H:i:s');
            $this->model->where( $where )->save($val); // 根据条件更新记录
            $i++;
        }
        $ret = array('status' => 1, 'info' => $i . ' records have been modified!', 'data' => '');
        $this->ajaxReturn($ret);
    }
}
