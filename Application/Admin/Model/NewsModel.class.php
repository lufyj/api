<?php
namespace Admin\Model;
use Think\Model;
/**
 * 属性模型
 * @author wpf
 */

class NewsModel extends Model {
    protected $_validate = array(        
        array('attr_name', '', '属性名称已经存在', self::VALUE_VALIDATE, 'unique', 1),
        array('attr_name', 'require', '属性名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    	array('attr_value', 'require', '属性值不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

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
    	$where = '';//查询字符串
    	
    	if($show_num > 0){
    		if(is_array($condition)){
    			$param = array();
    			if(is_array($condition)){
    				foreach ($condition as $v){
    					$param[$v['name']] = $v['value'];
    				}
    			}
    			$where = $this->_search($param);
    		}
    		$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	}
    	    	
    	$_count = $this->field(true)
    		->where($where)    		
    		->count();
    	
    	$_list = $this->field(true)
    		->where($where)
    		->select();
    	
    	return array('_list' => $_list, '_count' => $_count);
    } 
    
    /**
     * 根据参数生成查询字符串---后端不需要对sql攻击进行验证
     * @param unknown $condition
     */
    private function _search($condition){
    	
    	if(is_array($condition)){
    		$where = "1=1";
    		foreach ($condition as $k => $v) {
    			//$v = addSlashes(stripslashes(htmlspecialchars_decode($v)));
    			switch ($k){    				
    				case 'attr_name':
    					if(trim($v)){
    						$where .= ' and attr_name like \'%'. $v .'%\'';
    					}
    					break;
    			}
    		}
    		return $where;
    	}
    	return '';
    }

    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author wpf
     */
    public function info($id, $field = true){
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