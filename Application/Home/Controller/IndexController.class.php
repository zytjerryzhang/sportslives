<?php
namespace Home\Controller;
use Think\Controller;
use Common\Lib\Message;
class IndexController extends CommonController {
    public function index(){
    	//检索参数
    	$param = array('area_id'=>'','subway_id'=>'');
    	$area_id = I('get.area_id');
    	$subway_id = I('get.subway_id');
    	$keywords = I('get.keywords');
    	//区域/地铁
        $index_city_area = M('CityArea')->where(array('status'=>1,'city_id'=>CITYID))->select();
        $this->assign('__CITY__AREA__',$index_city_area);
        $index_city_subway = M('CitySubway')->where(array('status'=>1,'city_id'=>CITYID))->select();
        $this->assign('__CITY__SUBWAY__',$index_city_subway);

        //场馆
        $tA = "sl_gymnasium";
        $tB ="sl_gym_pro";//项目表
		//排序
        $orderby	 = !empty( $orderby ) ? "$tA.$orderby" :  "$tA.id desc";

        //条件
        $where = array("$tA.status"=>1,"$tA.city_id"=>CITYID);
        $where["$tA.city_id"] = CITYID;
    	$where["$tB.pro_id"] = CPROID;
    	if($area_id){
    		$where["$tA.area_id"] = $area_id;
    		$param['area_id'] = $area_id;
    	}
    	if($subway_id) {
    		$where["$tA.subway_id"] = $subway_id;
    		$param['subway_id'] = $subway_id;
    	}
		if($keywords) {
			$map["$tA.name"] = array('like',"%{$keywords}%");
			$map["$tA.addr"] = array('like',"%{$keywords}%");
			$map['_logic'] = 'or';
			$where['_complex'] = $map;
		}
        $count = M('Gymnasium')->join("LEFT JOIN $tB on $tA.id = $tB.gym_id")
						->where($where)
						->count();
		//初始化分页类
		$Page = new  \Think\Page($count, 10, $param);  // 实例化分页类 传入总记录数
		$gym_list = M('Gymnasium')->join("LEFT JOIN $tB on $tA.id = $tB.gym_id")
						->where($where)
						->order($orderby)
						->limit($Page->firstRow . ',' . $Page->listRows)
						->select();
		//推荐
		$rcmd_list = M('Gymnasium')->join("LEFT JOIN $tB on $tA.id = $tB.gym_id")
					->order("$tB.is_recommend desc")
					->field("$tA.name,$tA.icon,$tB.gym_id,$tB.pro_id,$tB.sale")
					->limit(4)
					->select();
		//热门
		$hot_list = M('Gymnasium')->join("LEFT JOIN $tB on $tA.id = $tB.gym_id")
					->order("$tB.sale desc")
					->field("$tA.name,$tA.icon,$tB.gym_id,$tB.pro_id,$tB.sale")
					->limit(4)
					->select();
		$pageLink 	= $Page->show(); // 分页显示输出
		$this->assign('pageLink', $pageLink); // 赋值分页输出
		$this->assign('gym_list',$gym_list);
		$this->assign('rcmd_list',$rcmd_list);//推荐
		$this->assign('hot_list',$hot_list); //热门
		$this->assign('param',$param);
    	$this->display();
    }
}
