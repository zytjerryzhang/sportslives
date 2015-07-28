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
class GymController extends CommonController {

    public function __construct(){
        parent::__construct();
        $this->model = M('gymnasium');
    }

    /**
     * 场馆列表
     */
    public function gymList() {
        $kw = I('post.keywords', '' );
        $this->model->order('id DESC');
        if ($kw) $this->model->where('name LIKE "%%%s%%"', $kw);
        $data = $this->listData(true);
        if ($data) {
            $this->model = M('img');
            foreach ($data as &$v) {
                $v['img'] = $this->model
                    ->where('parent_id='.$v['id'])
                    ->field('name,id')
                    ->limit(3)
                    ->select();
                $cou = count($v['img']);
                for ($i=0; $i<3-$cou; $i++) {
                    $v['img'][] = null;
                }
            }
        }
        $data = array('aData' => $data);
        $this->ajaxReturnFormat($data);
    }

    public function saveImg() {
        $name   = $this->upload();
        if (!$name) $this->ajaxReturnFormat(null, -1, '上传失败');
        $name   = date('Ymd', NOW_TIME) . '/' . $name;

        $id     = I('get.id', 0, 'intval');
        $iid    = I('get.iid', 0, 'intval');

        $this->model = M('img');
        if ($iid) {
            $data = $this->model
                ->where('id=' . $iid )
                ->field('name')
                ->find();
            $file = dirname(APP_PATH) .
                '/Public/uploads/gym/' . $data['name'];
            if (file_exists($file)) {
                unlink($file);
            }
            $this->model->save(array(
                'id'    => $iid,
                'name'  => $name
            ));
        } else {
            $this->model->add(array(
                'parent_id' => $id,
                'name'      => $name
            ));
        }

        $this->ajaxReturnFormat(null, 1, '上传成功');
    }

    public function saveIcon() {
        $name   = $this->upload();
        if (!$name) $this->ajaxReturnFormat(null, -1, '上传失败');
        $name   = date('Ymd', NOW_TIME) . '/' . $name;

        $id     = I('get.id', 0, 'intval');
        if (!$name) $this->ajaxReturnFormat(null, -1, '上传失败');

        $data = $this->model
            ->where('id=' . $id )
            ->field('icon')
            ->find();
        $file = dirname(APP_PATH) . '/Public/uploads/gym/' . $data['icon'];
        if (file_exists($file)) {
            unlink($file);
        }

        $this->model->save(array(
            'id'    => $id,
            'icon'  => $name
        ));
        $this->ajaxReturnFormat(null, 1, '上传成功');
    }

    /**
     * 位置信息查看
     */
    public function posTipContent() {
        $gym_id = I('get.id', 0, 'intval');

        $data = array();

        //城市名称
        $tmp = $this->model
            ->field('city_name name')
            ->join('__CITY__ ON __CITY__.id=city_id
            AND __GYMNASIUM__.id='.$gym_id)
            ->find();
        //echo $this->model->getLastSql();
        $data['city'] = $tmp['name'];

        $this->model = M('GymArea');
        $tmp = $this->model
            ->field('name')
            ->join('__CITY_AREA__ ON __CITY_AREA__.id=area_id
            AND __GYM_AREA__.gym_id='.$gym_id)
            ->select();
        if ($tmp) {
            foreach ($tmp as $v)
                $data['area'][] = $v['name'];
            $data['area'] = join('，', $data['area']);
        }

        $this->model = M('GymSubway');
        $tmp = $this->model
            ->field('name')
            ->join('__CITY_SUBWAY__ ON __CITY_SUBWAY__.id=subway_id
            AND __GYM_SUBWAY__.gym_id='.$gym_id)
            ->select();
        if ($tmp) {
            foreach ($tmp as $v)
                $data['subway'][] = $v['name'];
            $data['subway'] = join('，', $data['subway']);
        }

        $this->assign($data);
        $this->display();
    }

    /**
     * 新增场地
     */
    public function saveGym(){
        $data['name']   = I('post.name', '', 'trim');
        $data['addr']   = I('post.addr', '', 'trim');
        $data['tele']   = I('post.tele', '', 'trim');
        $data['phone']  = I('post.phone', '', 'trim');
        $data['opentime'] = I('post.opentime', 0);
        $data['closetime']	= I('post.closetime', 0);
        $data['status']     = I('post.status', 0, 'intval');
        $reg = '/\d\d:\d\d:\d\d/';
        if (!preg_match($reg, $data['opentime']))
            $data['opentime'] = '00:00:00';
        if (!preg_match($reg, $data['closetime']))
            $data['closetime'] = '00:00:00';
        $id             = I('post.id', 0, 'intval');
        if ($id) {
            $data['id'] = $id;
            $data['update_time'] = date('Y-m-d H:i:s');
        } else $data['create_time'] = date('Y-m-d H:i:s');

        if( !$data['name']) {
            $ret['status'] = -1;
            $ret['info'] = '场馆名不能为空！';
            $this->ajaxReturn($ret);
        }
        if( !$data['addr']) {
            $ret['status'] = -1;
            $ret['info'] = '场馆地址不能为空！';
            $this->ajaxReturn($ret);
        }
        if( !$data['tele'] && !$data['phone']) {
            $ret['status'] = -1;
            $ret['info'] = '固话和手机不能都为空！';
            $this->ajaxReturn($ret);
        }
        if( !$data['opentime']) {
            $ret['status'] = -1;
            $ret['info'] = '开馆时间不能为空！';
            $this->ajaxReturn($ret);
        }
        if( !$data['closetime']) {
            $ret['status'] = -1;
            $ret['info'] = '闭馆时间不能为空！';
            $this->ajaxReturn($ret);
        }
        $this->add($data);
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
     * 编辑场馆
     */
    public function editGym() {
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
     * 编辑场馆地图信息
     */
    public function editMap() {
        $id = I('get.id', 0, 'intval');
        $data = $this->model
            ->field('map_x,map_y,city_name name')
            ->where($this->model->getTableName() . '.id='.$id)
            ->join('LEFT JOIN __CITY__ ON __CITY__.id=city_id')
            ->find();
        $data['json'] = json_encode($data);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 编辑场馆位置信息
     */
    public function editPos() {
        $id = I('get.id', 0, 'intval');
        $city_id = I('get.cid', 0, 'intval');
        $this->assign('city_id',$this->model
            ->where('id=' . $id)
            ->field('city_id')
            ->find()
        );

        $this->model = M('city');
        $this->assign('city', $this->model
            ->field('id,city_name name')
            ->select()
        );

        $this->model = M('gymArea');
        $tmp = array();
        $area = $this->model->where('gym_id=' . $id)->field('area_id')->select();
        if ($area) foreach ($area as $v) $tmp[] = $v['area_id'];
        $this->assign('area',join(',', $tmp));

        $this->model = M('gymSubway');
        $tmp = array();
        $subway= $this->model->where('gym_id=' . $id)->field('subway_id')->select();
        if ($subway) foreach ($subway as $v) $tmp[] = $v['subway_id'];
        $this->assign('subway',join(',', $tmp));

        $this->display();
    }

    /**
     * 保存地图信息
     */
    public function saveMap() {
        $data['id']     = I('post.id', 0, 'intval');
        $data['map_x']  = I('post.map_x', 0, 'floatval');
        $data['map_y']  = I('post.map_y', 0, 'floatval');
        $this->add($data);
    }

    /**
     * 保存位置信息
     */
    public function savePos() {
        $city_id    = I('post.city_id', 0, 'intval');
        $area       = I('post.area', null);
        $subway     = I('post.subway', null);
        $id         = I('post.id', 0, 'intval');

        //保存城市信息
        $this->model->save(array(
            'id'        => $id,
            'city_id'   => $city_id
        ));

        //保存区域信息，先删除原信息
        $this->model = M('gymArea');
        $this->model->where('gym_id=' . $id)->delete();
        $dataList = array();
        if ($area) {
            foreach ($area as $v)
                $dataList[] = array(
                    'gym_id' => $id,
                    'area_id' => intval($v)
                );
            if (!$this->model->addAll($dataList))
                $this->ajaxReturnFormat(
                    array(),
                    -2,
                    $this->model->getError()
                );
        }


        //保存地铁线信息，先删除原信息
        $this->model = M('gymSubway');
        $this->model->where('gym_id=' . $id)->delete();
        $dataList = array();
        if ($subway) {
            foreach ($subway as $v)
                $dataList[] = array(
                    'gym_id' => $id,
                    'subway_id' => intval($v)
                );
            if (!$this->model->addAll($dataList))
                $this->ajaxReturnFormat(
                    array(),
                    -3,
                    $this->model->getError()
                );
        }

        $this->ajaxReturnFormat();
    }

    /**
     * 获取城市下的地铁线及区域
     */
    public function getCitySubwayArea() {
        $city_id = I('get.id', 0, 'intval');

        $this->model = M('cityArea');
        $data['area'] = $this->model
            ->where('status = 1 AND city_id='.$city_id)
            ->field('id,name')
            ->select();

        $this->model = M('citySubway');
        $data['subway'] = $this->model
            ->field('id,name')
            ->where('status = 1 AND city_id='.$city_id)
            ->select();

        $this->ajaxReturn($data);
    }

    /**
     * 我的场馆
     */
    public function myGym() {
        $kw = I('post.keywords', '');
        $this->model->order('id DESC');
        $where = array();
        if ($kw) $this->model->where('name LIKE "%%%s%%"', $kw);
        $this->model->where("seller_uid = {$this->user_info['id']}");
        $data = $this->listData(true);
        if ($data) {
            $this->model = M('img');
            foreach ($data as &$v) {
                $v['img'] = $this->model
                    ->where('parent_id='.$v['id'])
                    ->field('name,id')
                    ->limit(3)
                    ->select();
                $cou = count($v['img']);
                for ($i=0; $i<3-$cou; $i++) {
                    $v['img'][] = null;
                }
            }
        }
        $data = array('aData' => $data);
        $this->ajaxReturnFormat($data);
    }
}
