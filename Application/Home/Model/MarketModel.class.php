<?php 
namespace Home\Model;
use Think\Model;

/**
 * 行情资讯 模型
 * @author wpf
 *
 */
class MarketModel extends Model{
	
	protected $tableName = 'goods_market';
	
	/**
	 * 返回符合条数的行情数据 
	 * @param unknown $condition
	 */
	public function countList($condition = array()){
		
		return $this->alias('a')->field('a.id,a.title,a.description,a.view,a.author,a.update_time')
			->join('left join ydw_goods b on a.goods_id = b.id')
			->where($condition)
			->count();		
	}
	/**
	 * 获取列表
	 * @param unknown $condition
	 * @param number $offset 当前偏移量
	 * @param number $perpage 当前显示的数量
	 * @param string $orderby 排序
	 * @return array 返回所有结果
	 */
	public function getList($condition = array(), $offset = 0, $perpage = 10, $orderby = '') {
		$perpage = empty($perpage) ? 10 : abs(intval($perpage));
		$orderby = empty($orderby) ? 'a.create_time desc' : $orderby;			

		$this->alias('a')->field('a.id,a.title,a.description,a.view,a.author,a.update_time')
			->join('left join ydw_goods b on a.goods_id = b.id')
			->where($condition)
			->order($orderby);
	
		// 分页处理 <= 0 则 返回所有记录
		if ($offset >= 0 ) $this->limit($offset.','.$perpage);
		$list = $this->select();
	
		return $list;
	}
	/**
	 * 根据分类获取最新的信息
	 * @param number $cate_id 行情分类id
	 * @param number $limit 前10条数据
	 * @return unknown
	 */
	public function getLatestInfo($cate_id = 43, $limit = 10){		
		$condition = array(
			'status'  => 1,
			'cate_id' => $cate_id						
		);		
		$data = M('Articles')->field('id,title')			
			->where($condition)
			->order('id desc')
			->limit($limit)
			->select();		
		return $data;
	}
	/**
	 * 根据行情id获取数据
	 * @param unknown $id
	 */
	public function getMarketDetailById($id){
		//给该行情加1		
		$this->where(array('id'=>$id))->setInc('view');
		return $this->find($id);
	}
}
?>