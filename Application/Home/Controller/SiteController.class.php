<?php
namespace Home\Controller;
use Think\Controller;
class SiteController extends CommonController {
    public function index() {
        $gymId = I('get.gid', 0, 'intval');
        $proId = I('get.pid', 0, 'intval');
        $date  = date('Y-m-d');

        $gym = M('gymnasium')
            ->where(array('status'=>1,'id'=>$gymId))
            ->find();
        $this->assign('gym', $gym);

        //场馆辅图
        $sImg = M('img')
            ->field('name')
            ->where('parent_id=' . $gymId)
            ->select();
        $this->assign('sImg', $sImg);

        $gymPro = M('gymPro')
            ->where(array('gym_id' => $gymId, 'pro_id' => $proId))
            ->find();
        $gymPro['mark'] = $gymPro['common_number']?($gymPro['mark']/100/$gymPro['common_number']):0;
        $ceil   = ceil($gymPro['mark']);
        $floor  = floor($gymPro['mark']);
        if ($gymPro['mark']==$ceil) {
            $gymPro['mark'] = $ceil;
        } else if ($gymPro['mark']==$floor) {
            $gymPro['mark'] = $floor;
        } else if ($gymPro['mark'] - $floor < $ceil - $gymPro['mark']) {
            $gymPro['mark'] = $floor;
        } else $gymPro['mark'] = $floor + 0.5;
        $gymPro['mark'] *= 10;
        $this->assign('gymPro', $gymPro);

        //属性
        $pro = M('project')
            ->where('id=' . $proId)
            ->field('attr_ids,name')
            ->find();
        $this->assign('proName', $pro['name']);
        if (!$pro || !$pro['attr_ids']) {
            $this->display();
            return;
        }
        $this->assign('proName', $pro['name']);
        $gymProAttrVal = M('gymProAttrVal')
            ->join('__ATTR__ a ON a.id=attr_id
                AND a.status=1')
            ->where("gym_id=$gymId
                AND pro_id=$proId
                AND attr_id IN (" . $pro['attr_ids'] . ")")
            ->field("attr_id id,val,name,value,a.type t")
            ->select();
        $tmp = array();
        foreach($gymProAttrVal as $v) {
            $tmp[$v['id']]['name'] = $v['name'];
            $tmp[$v['id']]['type'] = $v['t'];
            $tmp[$v['id']]['val']  = explode(',', $v['value']);
            if ($v['t'] == 0)
                $tmp[$v['id']]['gpv']  = $v['val'];
            else
                $tmp[$v['id']]['gpv'][]  = $v['val'];

        }
        $this->assign('attr', $tmp);

        //是否被收藏
        if (UID) {
            $is_fav = M('fav')->where("user_id=" . UID . " 
                    AND gym_id=$gymId AND pro_id=$proId"
                )->field('id')->find();
            $this->assign('is_fav', $is_fav);
        } else {
            $this->assign('is_fav', 0);
        } 

        unset($gymProAttrVal, $tmp);

        //今天起的七天
        $date = array();
        $week = array('日','一','二','三','四','五','六');
        for($i = 0; $i < 7; $i++) {
            $t = strtotime("now + $i days");
            $date[] = array(
                'd' => date('Y-m-d', $t),
                'w' => $week[date('w', $t)],
            );
        }
        $this->assign('date', $date);

        $this->assign('__BODY_ID__', 'choose-site');

        $this->display();
    }

    private function parseRefer() {

        $refer = trim($_SERVER['HTTP_REFERER']);
        $reg   = '/gid\/(\d+)\/pid\/(\d+)/';
        $match = preg_match($reg, $refer, $res);
        if (!$match || !$res[1] || !$res[2]) {
            return false;
        }
        return array(
            intval($res[1]),
            intval($res[2]),
        );
    }

    public function siteInfo() {
        $date = I('post.date', null, 'trim');
        $res  = $this->parseRefer();
        if (!$res) die('0');
        $gid = $res[0]; $pid = $res[1];

        $siteName = array();
        $tmp = M('siteName')
            ->where("gym_id=$gid
            AND pro_id=$pid")
            ->field('id,name')
            ->select();
        foreach ($tmp as $v) {
            $siteName[$v['id']] = $v['name'];
        }

        $proAllowSaleNum = intval($this->getProAllowSaleNum($pid));

        $tmp = M('site')
            ->where("gym_id=$gid
            AND pro_id=$pid
            AND date='$date'")
            ->field('id,site_id,begin_time,end_time,
                price,saled_num')
                ->select();
        $site = array();
        $time = array();
        foreach ($tmp as $v) {
            $v['begin_time'] = substr($v['begin_time'],0,-3);
            $v['end_time']   = substr($v['end_time'],0,-3);
            $time[$v['begin_time']] = array(
                $v['begin_time'],$v['end_time']
            );
            $site[$v['site_id']][$v['begin_time']] = array(
                'b' => $v['begin_time'],
                'e' => $v['end_time'],
                'p' => number_format($v['price']/100,2,'.',''),
                'i' => $v['id'],
                'a' => $proAllowSaleNum,
                'o' => intval($v['saled_num']),
            );
            $site[$v['site_id']]['n'] = $siteName[$v['site_id']];
        }

        $this->assign('site', $site);
        $this->assign('time', $time);
        unset($site, $time, $tmp);
        $this->display('Site/site_tab');
    }

    //提交订单
    public function submitOrder() {
        $res  = $this->parseRefer();
        if (!$res) die('0');
        $gid = $res[0]; $pid = $res[1];

        $price = I('post.p', 0, 'floatval');
        $num   = I('post.n', array());

        $proAllowSaleNum = intval($this->getProAllowSaleNum($pid));

        $m = new \Think\Model();
        $s = '';
        $t = array();
        foreach ($num as $sid => $selectNum) {
            $num = $proAllowSaleNum - $selectNum;
            $sql = "SELECT s.id,s.`date`,s.begin_time bt,
                    s.end_time et,s.price,sn.name n
                FROM __SITE__ s JOIN  __SITE_NAME__ sn
                    ON sn.id=s.site_id
                WHERE s.id=$sid AND s.saled_num<=$num";
            $res = $m->query($sql);
            if (!$res || !isset($res[0])) die('1');
            $res = $res[0];
            $s .= ",(" . UID . ",_OID_,$gid,$pid,$sid,
                $selectNum,'{$res['date']}',
                '{$res['bt']}','{$res['et']}',
                '{$res['price']}','{$res['n']}',
                '" . rand_string(10,1) . "')";
            $t[$sid] = $selectNum;
        }
        if (!$s) die("2");
        $s = ltrim($s, ',');

        $m->startTrans();
        $oid = M('order')->add(array(
            'order_no' => 'SP'.date('Ymd').rand(10000, 99999),
            'order_money' => $price * 100,
            'gym_id'   => $gid,
            'pro_id'   => $pid,
        	'status'   =>1, //未使用
            'user_id'  => UID,
        ));
        $oid = intval($oid);
        if (!$oid) {
            $m->rollback();
            die("3");
        }

        $sql = "INSERT INTO __ORDER_ITEM__(user_id,
            order_id,gym_id,pro_id,site_id,order_number,
            `date`,`begin_time`,`end_time`,`price`,
            `site_name`,`pwd_no`)"
            . " VALUES" . str_replace("_OID_", $oid, $s);
        $res = $m->execute($sql);
        if (!$res) {
            $m->rollback();
            die("4");
        }

        $mSite = M('site');
        foreach ($t as $tid => $selectNum) {
            $res = $mSite
                ->where("id=$tid")
                ->setInc('saled_num', $selectNum);
            if (!$res) {
                $m->rollback();
                die('5');
            }
        }
        $m->commit();

        //session('order_id',$oid);
        die("$oid");
    }

    private function getProAllowSaleNum($pid) {
        $proAllowSaleNum = M('project')
            ->where('id=' . $pid)
            ->field('number')
            ->find();
        if (!$proAllowSaleNum) return false;
        return intval($proAllowSaleNum['number']);
    }
}
