<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\Page;
class CommentsController extends CommonController {
    public function _before_index(){
        if (!session('user')) redirect('/login');
    }

    public function index(){
        $uid = intval(UID);

        $m = M(); $data = array();
        $where = "user_id=$uid";

        $count = $m->table('__ORDER__')->where($where)->count();
        $Page = new  \Think\Page($count, 5);
        $res  = $m->table('__ORDER__')->where($where)
            ->field('id,gym_id gid,pro_id pid') 
                ->order('id DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        $this->assign('pageLink', $Page->show());
        if ($res) {
            foreach ($res as $v) {
                $v['gym_info'] = $m->cache('gym_' . $v['gid'])
                    ->table('__GYMNASIUM__')
                    ->where("id=" . $v['gid'])
                    ->field('name,icon')
                    ->find();

                $v['pro_info'] = $m->cache('pro_' . $v['pid'])
                    ->table('__PROJECT__')
                    ->where("id=" . $v['pid'])
                    ->field('name')
                    ->find();

                $v['com_info'] = $m->table('__COMMENTS__')
                    ->where("id=" . $v['id'])
                    ->find();

                $data[$v['id']] = $v;
            }
        }
        $this->assign('d', $data);

        $this->assign('__BODY_ID__', 'order');
        $this->assign('nav', 'comments');
        $this->display();
    }

    public function add(){
        $uid = UID;
        $gid = I('post.gid', 0, 'intval');
        $pid = I('post.pid', 0, 'intval');

        $data = array(
            'user_id'   => $uid,
            'pro_id'    => $pid,
            'gym_id'    => $gid,
        );

        $m = M('fav');
        $fav = $m->where($data)->find();
        if (!$fav) {
            $m->add($data);
        }
        die("0");
    }

    public function del(){
        $uid = UID;
        $gid = I('post.gid', 0, 'intval');
        $pid = I('post.pid', 0, 'intval');

        $data = array(
            'user_id'   => $uid,
            'pro_id'    => $pid,
            'gym_id'    => $gid,
        );

        $m = M('fav');
        $fav = $m->where($data)->delete();
        die("0");
    }
}
