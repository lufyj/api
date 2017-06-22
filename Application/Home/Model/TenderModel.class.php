<?php 
namespace Home\Model;
use Think\Model;

/**
 * 投标 模型
 * @author wpf
 */
class TenderModel extends Model{
	
	/**
	 * 根据用户id和药品id获取投标信息
	 * 
	 * @param int $gid 商品id
	 * @return 
	 */
	public function getOne($condition = array(), $field = true){
		if(is_array($condition)){
			return $this->alias('a')->field($field)->where($condition)->find();
		}
		return $this->alias('a')->field($field)->find($condition);
	}
	/**
	 * 统计符合条件记录数
	 *  
	 * @param unknown $condition
	 * @return 返回符合条件记录数
	 */
	public function countList($condition = array()) {
				
		return $this->alias('a')->where($condition)->count();
	}
	/**
	 * 获取列表 
	 * @param unknown $condition
	 * @param number $offset 当前偏移量
	 * @param number $perpage 当前显示的数量
	 * @param string $orderby 排序
	 * @return array 返回所有结果
	 */
	public function getList($condition = array(), $offset = 0, $perpage = 10, $orderby = 'id desc') {
		//$perpage = empty($perpage) ? 10 : abs(intval($perpage));		
			
		$this->field('id,price,contacts,mobile,status,remarks,create_time,imgs')
			->where($condition)
			->order($orderby);
	
		// 分页处理 <= 0 则 返回所有记录
		if ($offset >= 0 ) $this->limit($offset.','.$perpage);
		$list = $this->select();
	
		return $list;
	}
	public function getManyList($condition = array(), $offset = 0, $perpage = 10, $orderby = 'id desc'){		
		$this->alias('a')->field('a.id,a.demand_id,a.price,a.contacts,a.mobile,a.status,a.create_time,b.goods_name,b.trading_type,b.status as d_status')
			->join('left join ydw_demand b on a.demand_id = b.id')
			->where($condition)
			->order($orderby);	
		if ($offset >= 0 ) $this->limit($offset.','.$perpage);
		$list = $this->select();		
		return $list;
	}
	/**
	 * 添加一个投标信息
	 * @param unknown $data
	 * @return multitype:number string |number
	 */
	public function addData($data){	
		
		$demand = D('Demand');		
		
		//判断该商品的求购信息状态是否支持发布
		$demandInfo = $demand->getOne(array('id' => $data['demand_id']));
				
		if(!$demandInfo){
			return array('code' => 0, 'msg' => '求购信息不存在');	
		}
		if($demandInfo['status'] == -1 || $demandInfo['status'] > 0){
			return array('code' => 0, 'msg' => '求购信息已失效');
		}	
		//判断投标人与发布人是否为同一个人
		if($demandInfo['uid'] == $data['uid']){		
			return array('code' => 0, 'msg' => '发布人本人不能为自己投标');
		}		
		//首先判断该用户是否对这个商品已经投过标
		$already = $this->where(array('uid' => $data['uid'], 'demand_id' => $demandInfo['id']))->find();
		if($already){
			return array('code' => 0, 'msg' => '您已经投标，不能再次投标');
		}
		//暂时就这些验证，有就再补充
		
		$data['goods_id'] = $demandInfo['goods_id'];		
		$data['create_time'] = time();
		$data['status'] = 0;	
		
		$res = $this->data($data)->add();
		if((int)$res > 0){
			return array('code' => 1, 'msg' => '投标成功');
		}
		return array('code' => 0, 'msg' => '网络连接超时，请您稍后重试');
	}
	/**
	 * 更新投标状态
	 * 
	 * @param unknown $data
	 */
	public function updateStatus($data){
		
		$demand = D('Demand');
		
		//判断当前求购信息是否存在
		$demandInfo = $demand->getOne(array('id' => $data['demand_id']));
		
		if(!$demandInfo){
			return array('code' => 0, 'msg' => '求购信息不存在');
		}
		if($demandInfo['status'] == -1 || $demandInfo['status'] > 2){
			return array('code' => 0, 'msg' => '求购信息已失效');
		}
		if($demandInfo['uid'] != $data['uid']){
			return array('code' => 0, 'msg' => '该求购信息不属于您');
		}
		//判断投标信息是否存在
		$condition = array(
			'id' => $data['tender_id'],
			'demand_id' => $data['demand_id']
		);
		$already = $this->where($condition)->find();
		if(!$already){
			return array('code' => 0, 'msg' => '该投标信息不存在');
		}
		//投标成功则写成1
		$res = $this->where($condition)->save(array('status' => 1,'choose_time' => time()));
		if($res !== false){
			//将其他投标人的状态写成2			
			$this->where(array('demand_id' => $data['demand_id'], array('id' => array('neq',$data['tender_id']) )))->setField('status', 2);
			//将求购信息中的status写成1
			$demand->updateField2(array('id' => $data['demand_id'],'status' => 1));
			return array('code' => 1, 'msg' => '选择成功');
		}
		return array('code' => 0, 'msg' => '网络连接超时，请您稍后重试');
	}	
}
?>