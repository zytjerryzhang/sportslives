<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Lib\Page;
require_once 'CommonController.class.php';
/**
 * 银行维护控制器
 * @author Jerryzhang
 *2015-01-20
 */
class BankController extends CommonController {
	/**
	 * 银行列表
	 */
	public function myBank() {
		$bank = M('UserBank');
		//查询处理
		$bank_card_number = I('post.bank_card_number');
		$proCitys = C('PROCITY');
		//验证参数
		$where = array();
		$bank_list = array();
		if($bank_card_number) {
			$where['bank_card_number'] = array('like', "%{$bank_card_number}%");
		}

		//总的记录数 分页处理
		$currentPage = intval(I('post.p')) >0  ? I('post.p') : 0;
		$count       = $bank->where($where)->count();       // 查询满足要求的总记录数 $map表示查询条件
		$page        = new Page($count, C('PAGESIZE'));  // 实例化分页类 传入总记录数
		$pageLink    = $page->show();	// 分页显示输出
	    // 进行分页数据查询
    	$bank_list = $bank->where( $where )->order('id desc')->page($currentPage.','.C('PAGESIZE'))->select();

		$ret = array(
					'status' => 1,
					'info' => '',
					'data' => array(
					 	'aData' => $bank_list,
						'prCitys'=>$proCitys,
					 	'pagelink' => $pageLink,
					 	'permission'=> $this->permission)
		);
		$this->ajaxReturn($ret);
	}
	/**
	 * 修改银行卡信息
	 */
	public function modifyBank() {
		//获取参数
		$param = I('post.ekey', array(), '');
		//定义变量
		$ret = array('status' => 0, 'info' => '', 'data' => '');
		$bank = M('UserBank');
		$proCitys = C('PROCITY');
		//检验
		if( empty( $param ) || !is_array( $param ) ) {
			$ret['status'] = -1;
			$ret['info'] = 'the param is require!';
			$this->ajaxReturn($ret);
		}
		$i = 0;
		foreach($param as $key => $val) { //依次更新
			$where  = array('id' => $val['id']);
			$bank_info = $bank->where( $where )->find();
			$val['bank_prov'] = $proCitys[$val['bank_prov']]['province_name'];
			if( !empty($bank_info) ) {
				$val = array_merge($val,array('update_time'=>time()));
				$bank->where( $where )->save($val); // 根据条件更新记录
			}else {
				unset($val['id']);
				$val['user_id']= $this->user_info['id'];
				$bank->add( array_merge($val,array('update_time'=>time(),'create_time'=>time())) );
			}
			$i++;
		}
		$ret['status'] = 1;
		$ret['info'] = $i . ' records have been modified!';
		$this->ajaxReturn($ret);
	}
}