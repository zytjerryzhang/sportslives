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
class SiteController extends CommonController {
    public function __construct(){
        parent::__construct();
        $this->model = M('site');
    }

    /**
     * 场地编辑页面
     */
    public function siteList() {
        $id = I('post.iid', 0, 'intval');

        //场馆
        $this->model = M('gymnasium');
        $this->model
            ->field('id,name')
            ->order('id DESC');
        if ($id) $this->model->where('id=' . $id);
        $data = $this->listData(true);

        //项目
        $this->model = M('project');
        $this->model->field('id,name')->order('id DESC');

        $data = array(
            'gym' => $data,
            'id'  => $id,
            'pro' => $this->listData(true) 
        );

        //包括今天在内的一周
        $week = array('日','一','二','三','四','五','六');
        for ($i = 0; $i < 7; $i++) {
            $time = NOW_TIME + $i*86400;
            $date[] = array(
                'value' => date('Y-m-d', $time),
                'label' => date('m/d', $time),
                'week'  => $week[date('w', $time)]
            );
        }
        $data['date'] = $date;
        $this->ajaxReturnFormat($data);
    }

    /* 指定场馆项目下的场地信息*/
    public function siteInfo() {
        $gym_id = I('post.gid', 0, 'intval'); 
        $pro_id = I('post.pid', 0, 'intval'); 
        $date   = I('post.date', '', 'trim'); 

        if (!$date)
            $this->ajaxReturnFormat('', -1, '请指定请求的日期！' );

        $siteInfo = $this->model
            ->where('gym_id=' . $gym_id . 
            ' AND pro_id=' . $pro_id .
            ' AND date="' . $date . '"')
            ->field('id,site_id,is_onetime,begin_time,end_time,price,saled_num')
            ->select();
        $site = array();
        $is_saled  = false;
        $opentime  = false;
        $closetime = false;
        $mode      = 0;
        $usedSite  = array();
        foreach ($siteInfo as $v) {
            if (!$opentime || $opentime > $v['begin_time'])
                $opentime = $v['begin_time'];

            if (!$closetime || $closetime < $v['end_time'])
                $closetime = $v['end_time'];

            if ($v['saled_num']) $is_saled = true;

            $site[$v['site_id']][] = array(
                $v['begin_time'],
                $v['end_time'],
                $v['price']/100,
                $v['id'],
            );

            $mode = $v['is_onetime'];
        }
        unset($siteInfo);

        $data = array(
            'site'      => $site,
            'is_saled'  => $is_saled,
            'mode'      => $mode,
            'ot'        => $opentime,
            'ct'        => $closetime,
        );
        $this->ajaxReturnFormat($data);
    }

    /**
     * 指定场馆下的项目的基本信息
     */
    public function gymProInfo() {
        $gym_id = I('post.gid', 0, 'intval'); 
        $pro_id = I('post.pid', 0, 'intval'); 

        $where = 'gym_id=' . $gym_id . ' AND 
            pro_id=' . $pro_id;

        //场地名称
        $sn = M('siteName')
            ->where($where . ' AND status=1')
            ->field('id,name')
            ->select();
        $siteName = array();
        foreach ($sn as $v) $siteName[$v['id']] = $v['name'];

        //场地项目信息
        $gpInfo = M('gymPro')
            ->where($where)
            ->field('opentime,closetime,duration,price')
            ->find();
        if ($gpInfo) {
            $data = array(
                'ot'    => $gpInfo['opentime'],
                'ct'    => $gpInfo['closetime'],
                'du'    => floatval($gpInfo['duration']),
                'price' => floatval($gpInfo['price'])/100,
            );
        }
        $data['sn'] = $siteName;

        $this->ajaxReturnFormat($data);
    }

    /**
     * 添加场地 
     */
    public function addSite() {
        $gym_id = I('get.gid', 0, 'intval');
        $pro_id = I('get.pid', 0, 'intval');

        if (!$gym_id || !$pro_id) {
            $this->display();
            return;
        }

        $name = I('post.name', '', 'trim');
        $id = M('siteName')->add(array(
            'gym_id'    => $gym_id,
            'pro_id'    => $pro_id,
            'name'      => $name,
        ));
        if ($id) {
            $this->ajaxReturnFormat(
                array(
                    'id'    => $id,
                    'name'  => $name,
                )
            );
        } else $this->ajaxReturnFormat(array(), -1, '场地保存失败');
    }

    /**
     * 更新场地信息 
     */
    public function updateSite() {
        $data['gym_id'] = I('post.gym_id', 0, 'intval');
        $data['pro_id'] = I('post.pro_id', 0, 'intval');
        $data['opentime'] = I('post.ot', '00:00:00', 'trim');
        $data['closetime'] = I('post.ct', '00:00:00', 'trim');
        $data['duration']  = I('post.du', 0, 'floatval');
        $data['price'] = intval(I('post.pr', 0.0, 'floatval') * 100);

        $where = 'gym_id=' . $data['gym_id'] . '
            AND pro_id=' . $data['pro_id'];

        //更新场馆项目信息
        M('gymPro')->where($where)->save($data);

        if (!isset($_POST['saled']) 
            || !isset($_POST['date'])
            || !isset($_POST['reset'])
            || !isset($_POST['mode'])
        ){
            $this->ajaxReturnFormat(array(), -1, '参数缺失！');
        }

        $saled  = I('post.saled');
        $date   = I('post.date');
        $reset  = I('post.reset');
        $mode   = I('post.mode');
        $sid    = I('post.sid', array());
        $osid   = I('post.osid', array());
        $prs    = I('post.price', array());
        $ots    = I('post.opentime', array());
        $cts    = I('post.closetime', array());
        $del    = I('post.del', array());
        $sid    = I('post.sid', array());
        $id     = I('post.id', array());

        //只修改当前日期或每周这日
        if ($saled) {
            //已出售，则只能修改或新增
            $dataList = array();
            foreach ($sid as $k => $v) {
                foreach ($prs[$k] as $k2 => $v2) {
                    $tmpList = array(
                        'date'       => $date,
                        'is_onetime' => $mode,
                        'site_id'    => $v,
                        'gym_id'     => $data['gym_id'],
                        'pro_id'     => $data['pro_id'],
                        'begin_time' => $ots[$k][$k2],
                        'end_time'   => $cts[$k][$k2],
                        'price'      => intval(floatval($v2)*100),
                    );
                    if ($osid[$k]) {
                        $tmpList['update_time'] = date('Y-m-d H:i:s');
                        $this->model->where("id={$id[$k][$k2]}")->save($tmpList);
                        //echo $this->model->getLastSql() , "\n", "<br />";
                    } else $dataList[] = $tmpList;
                }
            }
            if (count($dataList)) $this->model->addAll($dataList);
        } else {
            //还没有出售，先删除，再新增
            M('site')->where($where . " AND date='$date'")->delete();
            foreach ($sid as $k => $v) {
                foreach ($prs[$k] as $k2 => $v2) {
                    $dataList[] = array(
                        'date'       => $date,
                        'is_onetime' => $mode,
                        'site_id'    => $v,
                        'gym_id'     => $data['gym_id'],
                        'pro_id'     => $data['pro_id'],
                        'begin_time' => $ots[$k][$k2],
                        'end_time'   => $cts[$k][$k2],
                        'price'      => intval(floatval($v2)*100),
                    );
                }
            }
            $this->model->addAll($dataList);
        }

        $this->ajaxReturnFormat(array(
            'date'  => $date
        ), 1, '数据保存成功');
    }

    /**
     * 场地信息的初始化保存
     */
    public function saveInitSite() {
        //保存场馆项目信息
        $data['gym_id'] = I('post.gym_id', 0, 'intval');
        $data['pro_id'] = I('post.pro_id', 0, 'intval');
        $data['opentime'] = I('post.ot', '00:00:00', 'trim');
        $data['closetime'] = I('post.ct', '00:00:00', 'trim');
        $data['duration']  = I('post.du', 0, 'floatval');
        $data['price'] = intval(I('post.pr', 0.0, 'floatval') * 100);
        $this->model = M('gymPro');
        if (!$this->model->add($data)) {
            $this->ajaxReturnFormat(array(), -1, '场馆项目信息保存失败');
        }

        //保存场地信息,已过去的7天+包括今天在内的未来7天共十四天数据
        $sid  = I('post.sid', array());
        $prs = I('post.price', array());
        $ots    = I('post.opentime', array());
        $cts    = I('post.closetime', array());

        $dataList = array();
        $begin = strtotime('-7 days'); 
        $end   = strtotime('+6 days'); 
        $site  = array();
        for($time = $begin; $time <=$end; $time += 86400) {
            $date = date('Y-m-d', $time);
            foreach ($sid as $k => $v) {

                foreach ($prs[$k] as $k2 => $v2) {
                    $dataList[] = array(
                        'date'       => $date,
                        'site_id'    => $sid[$k],
                        'gym_id'     => $data['gym_id'],
                        'pro_id'     => $data['pro_id'],
                        'begin_time' => $ots[$k][$k2],
                        'end_time'   => $cts[$k][$k2],
                        'price'      => intval(floatval($v2)*100),
                    );
                }
            }
        }
        $this->model = M('site');
        if (!$this->model->addAll($dataList)) {
            $this->ajaxReturnFormat(array(), -2, '场地信息保存失败');
        } else {
            $this->ajaxReturnFormat(array(
                'date'  => date('Y-m-d')
            ), 1, '数据保存成功');
        }
    }

    /**
     * 场馆项目属性信息保存
     */
    public function saveAttr() {
        //保存场馆项目信息
        $gid = I('post.gid', 0, 'intval');
        $pid = I('post.pid', 0, 'intval');
        $aid = I('post.aid', 0, 'intval');
        $val = I('post.val');
        $typ = I('post.typ');

        $this->model = M('gymProAttrVal');
        //先删除
        $this->model->where(
            'gym_id=' . $gid . '
            AND pro_id=' . $pid . '
            AND attr_id=' . $aid
        )->delete();

        if ($typ == 'checkbox') {
            $dataList = array();
            foreach ((array)$val as $v) {
                $dataList[] = array(
                    'gym_id'    => $gid,
                    'pro_id'    => $pid,
                    'attr_id'   => $aid,
                    'val'       => $v,
                );
            }
            if ($dataList) $this->model->addAll($dataList);
        } else {
            $dataList = array(
                'gym_id'    => $gid,
                'pro_id'    => $pid,
                'attr_id'   => $aid,
                'val'       => $val,
            );
            $this->model->add($dataList);
        }

        $this->ajaxReturnFormat(null);
    }

    public function saveRecommend() {
        $this->model = M('gymPro'); 
        $this->update();
    } 

    /**
     * 场地项目推荐管理 
     */
    public function gymPro() {
        $kw = I('post.keywords', '' );
        $this->model = M('gymPro');
        if ($kw) $this->model->where('(gym.name LIKE "%%%s%%" OR pro.name LIKE "%%%s%%")', $kw, $kw);
        $data = $this->model
            ->field('gym_id gid,pro_id pid,is_recommend,gym.name gname,pro.name pname,
                ' . $this->model->getTableName() . '.id')
            ->join('JOIN __GYMNASIUM__ gym ON gym.id=gym_id')
            ->join('JOIN __PROJECT__ pro ON pro.id=pro_id')
            ->select();

        //echo $this->model->getLastSql(), "";
        $this->ajaxReturnFormat(array('aData' =>$data));
    }

    /**
     * 场地项目的属性
     */
    public function attrList() {
        $gid = I('get.gid', 0, 'intval');
        $pid = I('get.pid', 0, 'intval');

        $attr_ids = M('project')
            ->where('id=' . $pid)
            ->field('attr_ids')
            ->find();
        if (!$attr_ids || !$attr_ids['attr_ids']) {
            $this->display();
            return;
        }

        $attr_ids = M('attr')
            ->where('id IN (' . $attr_ids['attr_ids'] . ') 
            AND status=1')
            ->field('type,name,value,id')
            ->select();

        //当前场馆当前项目的属性
        $gpav = M('gymProAttrVal')
            ->where("gym_id=$gid AND pro_id=$pid")
            ->field("attr_id,val")
            ->select();
        foreach ($gpav as $v) {
            $garr[$v['attr_id']][$v['val']] = $v['val'];
            $garr[$v['attr_id']][0] = $v['val'];
        }
        unset($gpav);

        $tmp = array();
        foreach ($attr_ids as $v) {
            if ($v['type'] != 0) {
                $arr = explode(',', $v['value']);
                $v['value'] = array();
                foreach ($arr as $v2) {
                    $v['value'][] = array(
                        'v' => $v2,
                        's' => isset($garr[$v['id']][$v2]),
                    );
                }
            } else $v['value'] = $garr[$v['id']][0];

            $tmp[$v['id']] = $v;
        }
        unset($attr_ids);
        $this->assign('attr', $tmp);
        $this->display();
    }
}
