<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';

/**
 * 属性控制器
 * @author Easytzb 
 * 2015-01-05
 */
class AttrController extends CommonController {

	 public function __construct(){
	 	parent::__construct();
        $this->model = M('attr');
     }

    /**
     * 项目列表
     */
    public function attrList() {
        $kw = I('post.keywords', '' );
        $this->model->order('id DESC');
        if ($kw) $this->model->where('name LIKE "%%%s%%"', $kw);
        $this->listData();
    }

    /**
     * 项目属性
     */
    public function addAttr() {
        $data['name']       = I('post.name', '', 'trim');
        $data['value']      = I('post.value', '', 'trim');
        $data['value']      = str_replace('，', ',', $data['value']); 
        $data['status']     = I('post.status', 0, 'intval');
        $data['type']       = I('post.type', 0, 'intval');
        $data['is_common']  = I('post.is_common', 0, 'intval');
        $data['is_search']  = I('post.is_search', 0, 'intval');
        $id             = I('post.id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['name']) {
            $ret['status'] = -1;
            $ret['info'] = '属性名不能为空！';
            $this->ajaxReturn($ret);
        }

        $this->add($data);
    }

    /**
     * 编辑属性
     */
    public function editAttr() {
        $id = I('get.id', 0, 'intval');
        if ($id) { 
            $this->assign('data',$this->model
                ->where('id=' . $id)
                ->find()
            );
        }
        //echo $this->model->getLastSql();
        $this->display();
    }
}
