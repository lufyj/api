<?php
namespace Admin\Model;
use Think\Model;
/**
 * 文章模型
 * @author wpf
 */

class ArticlesModel extends Model {
	
	/* 自动验证 */
	protected $_validate = array(		
		array('cate_id', ' /^\+?[1-9]\d*$/', '请选择所属栏目', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		array('title', 'require', '文章标题不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),		
		array('content', 'require', '内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
		
	);
	/* 自动登录 */
	protected $_auto = array(
		array('create_time', NOW_TIME, self::MODEL_INSERT),
		array('update_time', NOW_TIME, self::MODEL_BOTH)
	);	
 	/**
     * 获取药品列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
    	$query = ' b.status = 1 ';
    	$this->_search($params, $query);//组合查询条件
    	 
    	$count = $this->alias('a')->join('left join ydw_category b on a.cate_id = b.id')->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	$list = $this->alias('a')
    		->field('a.id,a.title,a.description,a.view,a.author,a.create_time,b.title as cate_title')
    		->join('left join ydw_category b on a.cate_id = b.id')
    		->where($query)
    		->limit($limit)
    		->order('a.update_time desc')
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
    			case 'cate_id':
    				if((int)$value > 0){
    					$query .= ' and find_in_set('. $value .',a.cate_id)';
    				}
    				break;
    			case 'title':
    				if($value){
    					$query .= ' and a.title like \'%'. $value .'%\'';
    				}
    				break;
    		}
    	}
    }
	/**
     * 获取分类详细信息
     * @param int $id 药品id
     * @param string $field 药品字段     
     */
    public function info($id, $field = true){
    	/* 获取分类信息 */
    	$map = array();
    	if(is_numeric($id)){ //通过ID查询
    		$map['id'] = $id;
    	} else { //通过标识查询
    		$map['title'] = $id;
    	}
    	return $this->field($field)->where($map)->find();
    }
    /**
     * 添加数据之前判断是否包含有图片
     * 
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(&$data, $option)	{    	
    	//处理上传图片
    	if(!empty($_FILES['img']['name'])) {
    		if($_FILES['img']['error'] != 0) {
    			$this->error = '请选择图片';
    			return  false;
    		}
    		$imgConfig = C('PICTURE_UPLOAD');
    		$imgConfig['savePath'] = 'Articles/';
    		$res = uploadImg('img', $imgConfig);
    		if($res['code'] == 1){
    			$data['thumb'] = $res['file'][1];
    		}else{
    			$this->error = $res['msg'];
    			return false;
    		}
    	}
    }
    /**
     * 更新数据之前判断是否包含图片
     * 
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(&$data, $option)	{
    	//处理上传图片
    	if(!empty($_FILES['img']['name'])) {    		
    		if($_FILES['img']['error'] != 0) {
    			$this->error = '请选择图片';
    			return  false;
    		}
    		$imgConfig = C('PICTURE_UPLOAD');
    		$imgConfig['savePath'] = 'Articles/';
    		$res = uploadImg('img', $imgConfig);
    		if($res['code'] == 1){
    			$data['thumb'] = $res['file'][1];
    			
    			//处理原有图片
    			$id = I('post.id', 0, 'intval');
    			$old_img = '.'.$this->where(array('id' => $id))->getField('thumb');
    			if(is_file($old_img)){
    				//删除图片文件
    				$delImgs = getImgs($old_img, -1);
    				foreach ($delImgs as $v){
    					unlink($v);
    				}    				
    			}   			
    		}else{
    			$this->error = $res['msg'];
    			return false;
    		}
    	}
    }
}
