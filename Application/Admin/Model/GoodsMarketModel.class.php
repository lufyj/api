<?php
namespace Admin\Model;
use Think\Model;
/**
 * 药品行情模型
 * @author wpf
 */

class GoodsMarketModel extends Model {
	/* 自动验证 */
    protected $_validate = array(        
    	array('cate_id', ' /^\+?[1-9]\d*$/', '请选择所属分类', 1 , 'regex', 3),
    	array('goods_id', ' /^\+?[1-9]\d*$/', '请选择所属药品', 1 , 'regex', 3),
    	array('title', 'require', '标题不能为空', 1 , 'regex', 3),
    	array('content', 'require', '内容不能为空', 1 , 'regex', 3),
    );
	/* 自动添加 */
    protected $_auto = array(        
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH)        
    );     
	/**
     * 获取属性列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
    	$query = '1=1';
    	$this->_search($params, $query);//组合查询条件
    	
    	$count = $this->alias('a')->join('left join ydw_goods b on a.goods_id = b.id')->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	$list = $this->alias('a')->field('a.id,a.title,a.view,a.author,a.create_time,b.goods_name')
    		->join('left join ydw_goods b on a.goods_id = b.id')
    		->where($query)
    		->limit($limit)
    		->order('id desc')
    		->select();
    	return array(
    		'list'	 => $list,
    		'page_num' => $page_num,
    		'show_num' => $show_num,
    		'all_count'  => $count,
    		'total_page' => $total_page
    	);    	
    }    
    /**
     * 根据参数生成查询字符串---后端不需要对sql攻击进行验证
     * @param unknown $params 查询参数
     * @param unknown $query  生成的查询字符串
     */
	private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){    			
    			case 'goods_name':
    				if($value){
    					$query .= ' and b.goods_name like \'%'. $value .'%\'';
    				}
    				break;
    		}
    	}    	
    }    
    /**
     * 根据药品行情id获取一个id
     * @param unknown $id 行情id
     * @return data
     */
    public function info($id){
    	$condition = array('status', array('neq', -1));
    	return $this->where($condition)->find($id);
    }
}