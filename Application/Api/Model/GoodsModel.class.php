<?php
// +----------------------------------------------------------------------
// | 版权：(c) 2017 http://www.yaoduwang.com All rights reserved.
// +----------------------------------------------------------------------
// | 作者: wpf <871927431@qq.com>
// +----------------------------------------------------------------------
// | 日期: 2017-03-01 10:40:46
// +----------------------------------------------------------------------
// | 描述: 前台药材模型
// +----------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class GoodsModel extends  BaseModel{
	
	/* 获取所有一级分类 */
	public function getAllCatsForNav(){
		$condition['status'] = 1;
		
		$cates = M('GoodsCategory')->field('id,title')
			->where($condition)
			->order('length(title),sort')
			->select();
		
		$this->_getAllCatsForNav($cates);		
		$this->_getAllCatsForHot($cates);
		return $cates;
	}	
	/* 获取所有分类对应的热点药材 */
	private function _getAllCatsForHot(&$cates){
		$condition = array();
		foreach ($cates as $k => $v){
			$condition['a.cate_id'] = $v['id'];
			$condition['_string'] = "find_in_set(a.id, b.goods_id)";
			$cates[$k]['_hot'] = $this->alias('a')->field('a.id,a.goods_name as gn')
				->join('left join ydw_goods_hot b on a.cate_id = b.cate_id')
				->where($condition)
				->select();
		}
	}
	/** 获取所有分类对应的药材 */
	private function _getAllCatsForNav(&$cates){
			
		$condition['status'] = 1;
		
		foreach ($cates as $k => $v){
			$condition['cate_id'] = $v['id'];
			$child = $this->field('id,goods_name as gn')//,goods_attr_ids
				->where($condition)
				->order('CONVERT( goods_name USING gbk ) COLLATE gbk_chinese_ci ASC')				
				->select();
			$clean = array();
			$max = 0;
			foreach ($child as $m => $n){
				$len = mb_strlen($n['gn'], 'utf8');
				if($len == 1 || $len == 2){
					$clean['g2'][] = $n;
				}else {
					$clean['g'. $len][] = $n;
				}
				if($len > $max) $max = $len;
			}
			$child = array();
			for ($i = 2; $i <= $max; $i++){
				if(isset($clean['g'. $i])){
					$child = array_merge($child, $clean['g'. $i]);
				}
			}
			$cates[$k]['_child'] = $child;			
		}		
	}
}
?>