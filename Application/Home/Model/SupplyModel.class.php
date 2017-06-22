<?php 
namespace Home\Model;
use Think\Model;

/**
 * 供应 模型
 * @author wpf
 *
 */
class SupplyModel extends Model{
	
	/**
	 * 获取一个供应详情
	 * 
	 * @param int 	  $id    供应id
	 * @param string $filter 是否登录用户查看
	 * @param string $field  要过滤的字段
	 */
	public function info($id, $filter = false, $field = ''){
		
		$condition = array( 
			'id' => $id 
		);
		if($filter){ 
			$condition['uid'] = session('user_sign.id');
		}
		if($field){
			$this->field($field);
		}
		
		return $this->where($condition)->find();
	}
	/**
	 * 获取一个求购信息
	 * 
	 * @param unknown $condition
	 * @return
	 */
	public function getOne($condition = array()){
		
		$data = $this->where($condition)->find();
		$data['imgs'] = getImgs($data['pic'], -1, -1);
		return $data;
	}
	/**
	 * 统计符合条件记录数
	 * 
	 * @param string $filter
	 * @return 返回符合条件记录数
	 */
	public function countList($condition = array(), $filter = false) {
		
		if($filter){
			$condition['uid'] = session('user_sign.id');
			$this->where($condition);
		}
		return $this->where($condition)->count();
	} 
	/**
	 * 获取列表	
	 * 
	 * @param unknown $condition
	 * @param number $offset 当前偏移量
	 * @param number $perpage 当前显示的数量
	 * @param string $filter 是否过滤登录用户
	 * @param string $orderby 排序
	 * @return array 返回所有结果
	 */
	public function getList($condition = array(), $offset = 0, $perpage = 10, $filter = false, $orderby = '') {
		$perpage = empty($perpage) ? 10 : abs(intval($perpage));
		$orderby = empty($orderby) ? 'id desc' : $orderby;
		
		if($filter){
			$condition['uid'] = session('user_sign.id');
		}		
		$this->field('id,cate_name,goods_name,goods_attr_name,num,price_type,price,origin_type,origin_area,supply_area,contacts,mobile,create_time,pic')
			->where($condition)
			->order($orderby);	
		
		// 分页处理 <= 0 则 返回所有记录
		if ($offset >= 0 ) $this->limit($offset.','.$perpage);
		$list = $this->select();
	
		return $list;
	}
	/**
	 * 获取最新的前10条数据
	 * 
	 * @param number $limit
	 * @return unknown
	 */
	public function getSupplyTen($limit = 10, $gid = 0){
	
		if((int)$gid > 0){
			$condition['goods_id'] = $gid;
		}
		return $this->getList($condition, 0, $limit);
	}
	/**
	 * 根据求购id删除其信息
	 * 
	 * @param unknown $id 求购id
	 */
	public function del($id){
		
		$condition = array(
			'id'  => $id,
			'uid' => session('user_sign.id')
		);
		
		return $this->where($condition)->delete();
	}
	/**
	 * 保存发布求购信息
	 * 
	 * @param unknown $data
	 */
	public function savePublish($data){
		$clean_data = array(
			'uid' => session('user_sign.id'),
			'num' => urldecode($data['num']),
			'price_type' => $data['price_type'],
			'origin_type'=> $data['origin_type'],
			'origin_area'=> urldecode($data['origin_area']),
			'contacts'	 => urldecode($data['contacts']),
			'mobile'	 => $data['mobile'],
			'supply_detail'	 => urldecode($data['supply_details']),
			'supply_area'=> urldecode($data['supply_area']),
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
				'goods_name' => urldecode($data['custom_name']),				
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
		//暂时判断用
		if($data['img1']){
			$clean_data['pic'] = $data['img1'];
		} 
		if($data['img2']){
			$clean_data['pic'] .= ','.$data['img2'];
		} 
		if($data['img3']){
			$clean_data['pic'] .= ','.$data['img3'];
		} 
		if($data['img4']){
			$clean_data['pic'] .= ','.$data['img4'];
		} 
		if($data['img5']){
			$clean_data['pic'] .= ','.$data['img5'];
		}

		if($data['supply_area_select']){
			$clean_data['supply_code'] = $data['supply_area_select'];
		}else if ($data['supply_city']){
			$clean_data['supply_code'] = $data['supply_city'];
		}else{
			$clean_data['supply_code'] = $data['supply_prvo'];
		}		
		if($data['price_type'] == 1){
			$clean_data['price'] = $data['price'];
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

}

?>