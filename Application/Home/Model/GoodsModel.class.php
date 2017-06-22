<?php 
namespace Home\Model;
use Think\Model;

/**
 * 商品 模型
 * @author wpf
 *
 */
class GoodsModel extends Model{
	
	/**
	 * 获取所有一级分类
	 */
	public function getAllCatsForNav(){
				
		//首先获取所有的一级分类
		$where['status'] = 1;
		
		$cates = M('goods_category')->field('id,title')
		->where($where)
		->order('length(title),sort')
		->select();
		
		$data = $this->_getAllCatsForNav($cates);		
		
		return $data;
	}
	
	/**
	 * 获取热门搜索前10位
	 */
	public function getHots(){
		
		$where['status'] = 1;
		$where['is_recom'] = 1;
		
		$data = $this->field('id,goods_name')
			->where($where)
			->order('sort desc')
			->select();
		
		return $data; 
	}
	
	/**
	 * 根据药品id获取药品对应的属性
	 * @param unknown $id 药品id
	 */
	public function getSpecs($id){		
		if((int)$id > 0){
			$sql = 'select b.id,b.attr_name from ydw_goods a LEFT JOIN ydw_spec b on FIND_IN_SET(b.ID,a.goods_attr_ids) where a.status = 1 and a.id = %d and b.status = 1 ORDER BY b.sort';
			return $this->query($sql, $id);
		}
		return false;
	}
	
	/**
	 * 根据药品名称获取药品信息
	 * @param unknown $gname
	 */
	public function getOne($gname){
		
		$condition['a.status'] = 1;
		
		if(is_numeric($gname)){
			$condition['a.id'] = $gname;
		}else{
			$condition['a.goods_name'] = array('like', $gname);
		}	
		
		$data = $this->alias('a')->field('a.id,a.cate_id,a.goods_name,a.goods_img,a.description,b.title as cate_name')
			->join('left join ydw_goods_category b on a.cate_id = b.id')
			->where($condition)
			->find();
		
		return $data;
	}			
	/**
	 * 根据所有一级分类获取没个分类下的前50条数据
	 * @param unknown $data
	 */
	private function _getAllCatsForNav($data){
			
		$where['status'] = 1;
		
		foreach ($data as $k => $v){
			$where['cate_id'] = $v['id'];
			$data[$k]['_child'] = $this->field('id,goods_name,goods_attr_ids')
				->where($where)
				->order('sort')
				->select();	
		}
		return $data;
	}
	
	/***********************处理药品折线图****************************/
	/**
	 * 获取则线图数据
	 * 
	 * @param unknown $id
	 */
	public function getLineChart($id){
		$data = M('GoodsTrend')->field('goods_avg,create_time')->where(array('goods_id' => $id))->select();
		$verticalData = array();
		$horizontalData = array();
		foreach ($data as $v){
			$verticalData[]   = $v['goods_avg'];
			$horizontalData[] = '\''.date('Y-m-d', $v['create_time']).'\'';
		}		
		//处理一下间隔，再研究highcharts后再做修改
		$interval = ceil(count($verticalData) / 5);
		return array(
			'v' => implode(',', $verticalData),
			'h' => implode(',', $horizontalData),
			'l' => $interval
		);
		//return array(implode(',', $verticalData), implode(',', $horizontalData));	
	}
	
	/***********************处理药品行情****************************/
	/**
	 * 根据药品id获取对应的行情
	 * 
	 * @param unknown $id
	 */
	public function getMarketTen($limit = 10, $id = 0){
		$condition = array(
			'goods_id' => $id,
			'status'   => 1
		);
	
		$data = M('GoodsMarket')->field('id,title,update_time')
		->where($condition)
		->order('update_time')
		->select();
	
		return $data;
	}	
}
?>