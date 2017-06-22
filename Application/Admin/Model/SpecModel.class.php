<?php
namespace Admin\Model;
use Think\Model;
/**
 * 属性模型
 * @author wpf
 */

class SpecModel extends Model {
	/* 自动验证 */
    protected $_validate = array(        
        array('attr_name', '', '属性名称已经存在', self::VALUE_VALIDATE, 'unique', 1),
        array('attr_name', 'require', '属性名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),    	
    );
	/* 自动提交 */
    protected $_auto = array(        
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH)        
    );
	/**
     * 获取属性列表
     * @param unknown $condition
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($condition, $show_num = 0, $page_num = 0){
    	$query = '1 = 1';
    	$this->_search($params, $query);//组合查询条件
    	
    	$count = $this->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	if($show_num > 0){
    		$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	}    	
    	$list = $this->where($query)
    		->order('length(attr_name)')  		
    		->limit($limit)
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
     * @param unknown $condition
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'attr_name':
    				if((int)$value > 0){
    					$query .= ' and attr_name like \'%'. $value .'%\'';
    				}
    				break;    			
    		}
    	}
    }
    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息    
     */
    public function info($id, $field = 'id,attr_name,sort,status'){
    	/* 获取分类信息 */
    	$map = array();
    	if(is_numeric($id)){ //通过ID查询
    		$map['id'] = $id;
    	} else { //通过标识查询
    		$map['attr_name'] = $id;
    	}
    	return $this->field($field)->where($map)->find();
    }
}