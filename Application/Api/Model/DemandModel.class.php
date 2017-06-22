<?php 
namespace Api\Model;
use Think\Model;

/**
 * 求购 模型
 * @author wpf
 *
 */
class DemandModel extends Model{
		
	/**
	 * 返回一个求购信息
	 * 
	 * @param unknown $condition
	 * @return 返回一个求购信息
	 */
	public function getOne($condition = array()){
		return $this->where($condition)->find();
	}	
	/**
	 * 统计符合条件记录数
	 *
	 * @param string $filter
	 * @return 返回符合条件记录数
	 */
	public function countList($condition = array()) {
		
		return $this->alias('a')->where($condition)->count();
	}
	/**
	 * 获取列表
	 *
	 * @param unknown $condition
	 * @param number $offset 当前偏移量
	 * @param number $perpage 当前显示的数量
	 * @param string $orderby 排序
	 * @return array 返回所有结果
	 */
	public function getList($condition = array(), $offset = 0, $perpage = 10, $orderby = '') {
		$perpage = empty($perpage) ? 10 : abs(intval($perpage));
		$orderby = empty($orderby) ? 'a.id desc' : $orderby;
			
		/* $this->field('id,cate_name,goods_name,goods_attr_name,contacts,mobile,create_time,end_time')
			->where($condition)
			->order($orderby); */
		$this->alias('a')->field('a.id,a.cate_name,a.goods_name,a.goods_attr_name,a.num,a.contacts,a.mobile,a.create_time,a.end_time,b.goods_img')
			->join('left join ydw_goods b on a.goods_id = b.id')
			->where($condition)
			->order($orderby);
	
		// 分页处理 <= 0 则 返回所有记录
		if ($offset >= 0 ) $this->limit($offset.','.$perpage);
		$list = $this->select();
	
		return $list;
	}
	
	/**
	 * 获取最新的前10条数据
	 * @param number $limit
	 * @return unknown
	 */
	public function getDemandTen($limit = 10, $gid = 0){
		$condition = array(
			'status' => array('neq', -1)
		);
		if((int)$gid > 0){
			$condition['goods_id'] = $gid;
		}
		
		$data = $this->field('id,goods_name,num,goods_attr_name,contacts,mobile')
			->where($condition)
			->order('update_time desc')
			->limit($limit)
			->select();
		
		return $data;
	}

	/**
	 * 保存发布求购信息
	 * @param unknown $data
	 */
	public function savePublish($data){
			
		$clean_data = array(
			'uid' => session('user_sign.id'),			
			'num' => urldecode($data['num']),			
			'origin_type'=> $data['origin_type'],
			'origin_area'=> urldecode($data['origin_area']),
			'contacts'	 => urldecode($data['contacts']),
			'mobile'	 => $data['mobile'],
			'qq' => $data['qq'],
			'details' 	 => urldecode($data['details']),
			'create_time'=> time(),
			'update_time'=> time()	
		);
		//在这里判断是否是自定义
		if(trim($data['custom_name'])){
			//开始向custom_goods中插入数据
			$custom_data = array(
				'uid' => session('user_sign.id'),
				'goods_name' => $data['custom_name'],				
				'create_time'=> time()
			);
			$custom_id = M('custom_goods')->data($custom_data)->add();
			$clean_data['goods_id'] = $custom_id;
			$clean_data['goods_name'] = urldecode($data['custom_name']);;
		}else{
			$clean_data['cate_id'] 	  = $data['cate_id'];
			$clean_data['cate_name']  = urldecode($data['cate_name']);
			$clean_data['goods_id']	  = $data['goods_id'];
			$clean_data['goods_name'] = urldecode($data['goods_name']);
			$clean_data['goods_attr_id'] = $data['goods_attr_id'];
			$clean_data['goods_attr_name'] = urldecode($data['goods_attr_name']);
		}			
		
		if($data['origin_type'] == 3){
			//在这里组合要保存的地区code值，以及保存的地区全称
			if($data['area_select']){
				$clean_data['origin_code'] = $data['area_select'];
			}elseif ($data['city_select']){
				$clean_data['origin_code'] = $data['city_select'];
			}else{
				$clean_data['origin_code'] = $data['prov_select'];
			}
		}

		$res = $this->data($clean_data)->add();
		
		return $res;
	}
	/**
	 * 保存一个数据
	 * 
	 * @param unknown $data
	 */
	public function addData($data){
		if($data['custom_name']){
			//开始向custom_goods中插入数据
			$custom_data = array(
				'uid' => $data['uid'],
				'goods_name' => $data['custom_name'],
				'create_time'=> time()
			);
			$custom_id = M('custom_goods')->data($custom_data)->add();
			$data['goods_id'] = $custom_id;
			$data['goods_name'] = $data['custom_name'];
		}
		$data['create_time'] = time();
		$data['update_time'] = time();		
		$data['end_time']    = strtotime($data['end_time']);
		return $this->data($data)->add();
	}
	
	/**
	 * 更新一个字段
	 * 
	 * @param unknown $condition
	 */
	public function updateField($condition = array()){
		return $this->save($condition);
	}
	public function payMoadl($data){
			//判断求购id是否存在，且状态等于1
			$info = M('demand')->where(['id' => $data['id']])->field('status,uid')->find();
			if(!$info || $info['status'] != 1 || $info['uid'] != $data['uid']){
				$this->error = '非法操作';return 0;
			}
			//根据求购id获取对应的投标信息
			$condition = array('demand_id' => $data['id'], 'status' => 1);
			$tender = D('Tender')->getOne($condition, 'uid,price,contacts,mobile,remarks');
			if(!$tender || $tender['uid'] == $data['uid']){
				$this->error = '非法操作';return 0;
			}
			$this->error = $tender;return 1;			
		}
	/**
	 * 根据求购id更新其字段
	 * @param unknown $data
	 * @param unknown $inx
	 */
	public function updataField($data, $inx){		
		$this->error = '操作失败';
		$code = 0;//错误标示
		$realname = M('user')->where(['id' => $data['uid']])->getField('realname');
		$memo = '雇主'.$realname;//操作记录
		$cleanData = array('id' => $data['id']);//要保存的数据	
		switch ($inx){
			case 1:
				//判断求购状态信息
				$code = $this->payMoadl($data);
				if(!$code) return 0;
				//判断支付方式
				if($data['type'] == 0){
					$data['trading_type'] = 2;//代表线下交易
					$data['status'] = 4;
					$memo .= '在支付托管资金时选择了线下交易';//将当前交易方式设置为线下交易（后台）
				}else if($data['type'] == 1){
					$data['trading_type'] = 1;//代表线上交易
					$data['order_amount'] = -1;
					$memo .= '在支付托管资金时选择了转账';//将当前交易方式设置为线上交易（后台）
				}
				$res = $this->save($data);
				if($res !== false){
					$this->error = '操作成功';
					$code = 1;
				}
				break;
			case 2:
			$info = $this->getS($data['tender_id']);
			if(!$info || $info['status'] != 2 || $info['uid'] != $data['uid'] || $info['demand_id'] != $data['id']) return 0;				
			$cleanData['status'] = 3;
			$cleanData['delivery_time'] = $date;
			$res = $this->save($cleanData);
			if($res !== false){

				//推送投标信息(通知求购人已经发货)
				$demandInfo=$this->where('id='.$info['demand_id'])->field('id,uid')->find();
				D('PushDemandinfo')->pushInfo(3,$info['demand_id'],$demandInfo['uid']);
				$this->error = '操作成功';
				$code = 1;
				$memo .= '确认发货';
			}
			break;
			case 3:
				$info =M('demand')->where(['id' => $data['id'],'uid' => $data['uid']])->field('status')->find();
				if($info['status'] != 3) return 0;
				$data['status'] = 4;
				$data['sign_time'] = time();
				$res = $this->save($data);
				if($res !== false){

					//推送投标信息(通知中标人雇主已经签收货物)
					$tenderInfo=M('Tender')->where('demand_id='.$data['id'].' and status=1')->field('id,uid,demand_id')->find();
					D('PushDemandinfo')->pushInfo(4,$tenderInfo['demand_id'],$tenderInfo['uid']);
					$this->error = '操作成功';
					$code = 1;
					$memo .= '确认签收';
				}
				break;		
		}		
		//记录日志
		if($code == 1){
			$logData = array(
				'uid'	     => $data['uid'],
				'memo'		 => $memo,
				'demand_id'  => $data['id'],
				'create_time'=> time()
			);
			M('DemandLog')->data($logData)->add();
		}
		return $code;
	}
	/**
	 * 根据投标id获取
	 * @param unknown $id
	 */
	private function getS($id){
		$condition = array('a.id' => $id,'a.status' => 1);
		return M('Tender')->alias('a')->field('a.uid,a.demand_id,b.status')
				->join('left join ydw_demand b on a.demand_id = b.id')
				->where($condition)
				->find();
	}

}
?>