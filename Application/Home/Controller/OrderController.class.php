<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\Page;
class OrderController extends CommonController {
    public function _before_index(){
        if (!session('user')) redirect('/login');
    }

    public function index(){
        $status = I('get.ps', 0, 'intval');
        $tRange = I('get.t', 0, 'intval');

        $uid = intval(UID);

        $m = M(); $data = array();
        $t = array(1 => '待支付','支付成功','支付失败','超时');
        $where = "user_id=$uid";
        if ($status) {
            $this->assign('s_des', $t[$status]);
            $where .= " AND pay_status=" . ($status-1);
        }
        $this->assign('t', $t);

        $count = $m->table('__ORDER__')->where($where)->count();
        $Page = new  \Think\Page($count, 5);
        $res  = $m->table('__ORDER__')->where($where)
            ->field('id, order_no o,pay_status s,create_time c,
                gym_id gid, pro_id pid, order_money m,user_id uid') 
                ->order('id DESC')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        $this->assign('pageLink', $Page->show());
        if ($res) {
            foreach ($res as $v) {
                $v['s_d'] = $t[$v['s']+1];
                $v['m']   = number_format($v['m']/100,2,'.','');
                $v['can_pay'] = (($v['s'] == 0 || $v['s']  == 2)
                    && strtotime($v['c']) + 900 > NOW_TIME);

                $v['gym_info'] = $m->cache('gym_' . $v['gid'])
                    ->table('__GYMNASIUM__')
                    ->where("id=" . $v['gid'])
                    ->field('name,icon')
                    ->find();

                $key = "is_fav_{$v['uid']}_{$v['gid']}_{$v['pid']}";
                $v['is_fav'] = $m->cache($key, 5)
                    ->table('__FAV__')
                    ->where("user_id={$v['uid']} 
                        AND gym_id={$v['gid']} 
                        AND pro_id={$v['pid']}"
                    )->field('id')->find();

                $v['pro_info'] = $m->cache('pro_' . $v['pid'])
                    ->table('__PROJECT__')
                    ->where("id=" . $v['pid'])
                    ->field('name')
                    ->find();

                $v['order_item_info'] = $m->table('__ORDER_ITEM__')
                    ->where("order_id=" . $v['id'])
                    ->field('`date` d,`begin_time` bt,`end_time` et,
                        `price` p,`site_name` sn,`pwd_no` pn,
                        `pwd_status` ps,order_number o')
                        ->select();

                foreach ($v['order_item_info'] as $k2 => $v2) {
                    $v['order_item_info'][$k2]['bt'] = substr($v2['bt'], 0, 5);
                    $v['order_item_info'][$k2]['et'] = substr($v2['et'], 0, 5);
                    $v['order_item_info'][$k2]['p']  = number_format($v2['p']/100,2,'.','');
                    $v['order_item_info'][$k2]['ps']  = ($v2['ps']?'已':'未').'使用';
                }
                $v['order_item_num'] = count($v['order_item_info']);

                $data[$v['id']] = $v;
            }
        } 
        $this->assign('d', $data);

        $this->assign('__BODY_ID__', 'order');
        $this->assign('nav', 'order');
        $this->display();
    }
}
