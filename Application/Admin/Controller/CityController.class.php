<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';

/**
 * 城市管理控制器
 * @author Easytzb 
 * 2015-01-05
 */
class CityController extends CommonController {

	 public function __construct(){
	 	parent::__construct();
        $this->model = M('city');
     }

    /**
     * 城市列表
     */
    public function cityList() {
        $kw = I('post.keywords', '', 'trim');
        $this->model->order('id DESC');
        if ($kw) $this->model->where('city_name LIKE "%%%s%%"', $kw);

        $data = $this->listData();
    }

    /**
     * 城市区域
     */
    public function addArea() {
        $data['name']   = I('post.val', '', 'trim');
        $data['status'] = I('post.status', 0);
        $id             = I('post.id', 0, 'intval');
        $data['city_id']= I('post.city_id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['name']) {
            $ret['status'] = -1;
            $ret['info'] = '地铁线名称不能为空！';
            $this->ajaxReturn($ret);
        }

        $this->model = M('CityArea');
        $this->add($data);
    }

    /**
     * 城市地铁线
     */
    public function addSubway() {
        $data['name']   = I('post.val', '', 'trim');
        $data['status'] = I('post.status', 0);
        $id             = I('post.id', 0, 'intval');
        $data['city_id']= I('post.city_id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['name']) {
            $ret['status'] = -1;
            $ret['info'] = '地铁线名称不能为空！';
            $this->ajaxReturn($ret);
        }

        $this->model = M('CitySubway');
        $this->add($data);
    }

    /**
     * 编辑城市区域
     */
    public function editArea() {
        $id     = I('get.id', 0, 'intval');
        $isShow = I('get.show', 0, 'intval');
        $this->model = M('cityArea');
        if ($id) { 
            $data = $this->model
                ->where('city_id=' . $id)
                ->order('id DESC')
                ->select();
            if ($isShow) {
                $str = '';
                foreach ($data as $v) {
                    $str .= ' ' . $v['name'] . " (" . 
                        ($v['status']?"已启用":"已禁用") . 
                        ")<br />";
                }
                die($str?$str:'未设定');
            } else $this->assign('data', $data);
        }
        $this->display();
    }

    /**
     * 编辑城市地铁线
     */
    public function editSubway() {
        $id     = I('get.id', 0, 'intval');
        $isShow = I('get.show', 0, 'intval');
        $this->model = M('citySubway');
        if ($id) { 
            $data = $this->model
                ->where('city_id=' . $id)
                ->order('id DESC')
                ->select();
            if ($isShow) {
                $str = '';
                foreach ($data as $v) {
                    $str .= ' ' . $v['name'] . " (" . 
                        ($v['status']?"已启用":"已禁用") . 
                        ")<br />";
                }
                die($str?$str:'未设定');
            } else $this->assign('data', $data);
        }
        //echo $this->model->getLastSql();
        $this->display();
    }

    /**
     * 编辑城市
     */
    public function editCity() {
        $id = I('get.id', 0, 'intval');
        if ($id) { 
            $this->assign('data',$this->model
                ->where('id=' . $id)
                ->find()
            );
        }
        $this->display();
    }

    /**
     * 新增城市
     */
    public function addCity(){
        $data['city_name']   = I('post.city_name', '', 'trim');
        $data['status']	= I('post.status', 0);
        $id             = I('post.id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['city_name']) {
            $ret['status'] = -1;
            $ret['info'] = '城市名称不能为空！';
            $this->ajaxReturn($ret);
        }

        $this->add($data);
    }

    /**
     * 城市修改 
     *
     */
    public function updateCity() {
        $this->update();
    }
}
