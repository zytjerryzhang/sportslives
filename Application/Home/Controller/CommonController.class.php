<?php
namespace Home\Controller;
use Think\Controller;
use Think\Cache;

class CommonController extends Controller{
    static protected $redis = null;

    public function __construct(){
        parent::__construct();
        if (is_null(self::$redis)) {
            self::$redis = Cache::getInstance('Redis');
        }
        $cityId = I('get.cityId');
        $cityId = ($cityId>0) ? $cityId : 1; //默认深圳
        $cproId = I('get.proid'); //当前项目ID
        $keywords = I('get.keywords');
//        if($cityId){
//            $local_city =  M('City')->field('id,city_name')->where(array('id'=>$cityId))->find();
//        }else{
//            $local = getIpLookup('14.17.32.211'); //查询客户端IP 上线传空  默认qq的  貌似很慢
//            $w['city_name'] = array('like',"%{$local['city']}%");
//            $local_city = M('City')->field('id,city_name')->where($w)->find();
//        }
        //@todo 缓存数据
        //顶部菜单
        $where = array('type'=>1,'status'=>1,'pro_id'=>I('get.pro_id') ? I('get.pro_id') : 1);
        $index_menu=M('Nav')->where($where)->order('idx desc')->select();
        $this->assign('__HEADER_MENU__',$index_menu);
        //城市
        $sql = "SELECT * , ELT( INTERVAL( CONV( HEX( LEFT( CONVERT( `city_name` USING gbk ) , 1 ) ) , 16, 10 ) , 0xB0A1, 0xB0C5, 0xB2C1, 0xB4EE, 0xB6EA, 0xB7A2, 0xB8C1, 0xB9FE, 0xBBF7, 0xBFA6, 0xC0AC, 0xC2E8, 0xC4C3, 0xC5B6, 0xC5BE, 0xC6DA, 0xC8BB, 0xC8F6, 0xCBFA, 0xCDDA, 0xCEF4, 0xD1B9, 0xD4D1 ) , 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K','L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W', 'X', 'Y', 'Z' ) AS py FROM sl_city WHERE status=1 ORDER BY py ASC";
        $index_city = M('City')->query($sql);
        $this->assign('__HEADER_CITY__',$index_city);
        //@todo 缓存数据
		//项目名称
        $pro_list = M('Project')->where(array('status'=>1))->order('idx desc')->select();

        //当前城市
        $this->assign('__CITY__',M('City')->where(array('id'=>$cityId))->find());
        //底部菜单
        $where = array('type'=>3,'status'=>1);
        //        $index_menu=M('Nav')->where($where)->order('idx desc')->select();
        $this->assign('__FOOTER_MENU__',$index_menu);
        $user = session('user');
        $this->assign('__USER__',$user);

        //检索text
        $this->assign('__KEYS__',$keywords);
        //项目列表
        $this->assign('__PROLIST__',$pro_list);
        $cproId = ($cproId>0 ? $cproId : $pro_list[0]['id']);
        $this->assign('CPROID',$cproId); //默认项目
        $this->assign('__PRO__',M('Project')->where(array('id'=>$cproId))->find());

        define('CPROID',$cproId);
        define('CITYID',$cityId);
        define('UID',$user['id']);
    }
}
