<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';

/**
 * 项目控制器
 * @author Easytzb 
 * 2015-01-05
 */
class ProController extends CommonController {
	 public function __construct(){
	 	parent::__construct();
        $this->model = M('project');
     }

    /**
     * 项目列表
     */
    public function proList() {
        $kw = I('post.keywords', '' );
        $this->model->order('id DESC');
        if ($kw) $this->model->where('name LIKE "%%%s%%"', $kw);

        $data = $this->listData();
    }

    /**
     * 编辑项目下的属性
     */
    public function editAttr() {
        $id = I('get.id', 0, 'intval');

        $attr_ids = $this->model
            ->where('id=' . $id)
            ->field('attr_ids')
            ->find();
        if (empty($attr_ids)) $attr_ids = array();
        else $attr_ids = explode(',', $attr_ids['attr_ids']); 

        $this->model = M('attr');
        $attr = $this->model
            ->where('status=1')
            ->order('is_common DESC,id DESC')
            ->field('id,name,is_common,value,status')
            ->select();
        foreach ($attr as &$v) {
            $v['checked'] = in_array($v['id'], $attr_ids); 
        }
        $this->assign('attr', $attr);
        $this->display();
    }

    /**
     * 保存项目属性
     */
    public function saveAttr(){
        $data['id']   = I('post.id', 0, 'intval');
        $data['attr_ids'] = I('post.attr_ids', '', 'trim');
        $data['update_time'] = date('Y-m-d H:i:s');
        $this->add($data);
    }

    /**
     * 新增项目
     */
    public function addPro(){
        $data['name']   = I('post.name', '', 'trim');
        $data['number'] = I('post.number', 0, 'intval');
        $data['status']	= I('post.status', 0);
        $id             = I('post.id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['name']) {
            $ret['status'] = -1;
            $ret['info'] = '项目名称不能为空！';
            $this->ajaxReturn($ret);
        }
        $this->add($data);
    }
}
