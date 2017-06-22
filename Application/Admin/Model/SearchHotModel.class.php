<?php
namespace Admin\Model;
use Think\Model;
/**
 * 商品模型
 * @author wpf
 */

class SearchHotModel extends Model {
	
    /* 自动验证 */
	protected $_validate = array(        
    	array('cate_id', ' /^\+?[1-9]\d*$/', '请选择所属分类', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('goods_name', '', '药品名称已经存在', self::MUST_VALIDATE, 'unique', 1),
        array('goods_name', 'require', '药品名称不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH)    	
    );	
	/* 自动提交 */
    protected $_auto = array(    	
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),    	
    	array('goods_attr_ids','arrtostr', 3 ,'callback'),
    ); 
    /**
     * 根据药品id获取其信息
     * @param unknown $id
     */
   	public function info($id, $field = ''){   		
   		$this->field($field);
   		if(is_array($id)){
   			return $this->where($id)->find();
   		}
   		return $this->find($id);  
   	}    
    /**
     * 获取药品列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
    	$query = '1=1';
    	$this->_search($params, $query);//组合查询条件
    	$count = $this->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	$list = $this
    		->where($query)
    		->limit($limit)
    		->order('goods_hot desc')
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
     * 根据参数生成查询字符串---后端暂时不对sql攻击进行验证
     * @param unknown $params 查询参数
     * @param unknown $query  生成的查询字符串
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'goods_name':
    				if($value){
    					$query .= ' and goods_name like \'%'. $value .'%\'';
    				}
    				break;
    		}
    	}
    }
    /* 将数据转换为字符串 */
    public function arrtostr($arr){
    	array_pop($arr);
    	return implode(',', $arr);
    }
    /**
     * 更新某个字段
     * @param unknown $data
     */
    public function updateField($id, $fieldName, $fieldValue){
    	$conditon = array('id' => $id);
    	switch ($fieldName){
    		case 'goods_img':
    			$old_img = $this->where($conditon)->getField('goods_img');
    			if(trim($old_img)){
    				$paths = explodeImg($old_img);
    				if($paths !== false){
    					foreach ($paths as $v){
    						//回头在这里还要判断一下文件是否存在
    						unlink('.'.$v);
    					}
    				}    				
    			}
    			break;
    	}
    	$this->where($conditon)->setField($fieldName, $fieldValue);
    }    
    /**
     * 根据药品分类id获取所有药品
     * @param unknown $cate_id 分类id
     */
    public function getListByCateId($cate_id){
    	
    	$condition = array(
    		'cate_id' => $cate_id,
    		'status'  => 1	
    	);
    	
    	return $this->field('id,goods_name')->where($condition)->select();
    }
    /************************开始处理药品曲线图数据************************/
    /**
     * 异步获取曲线图数据
     * 
     * @param unknown $id
     */
    public function getLineChart($id){
    	$data = M('GoodsTrend')->field('goods_avg,create_time')->where(array('goods_id' => $id))->select();
    	$retData = array();
    	foreach ($data as $v){
    		$retData[] = array(
    			$v['create_time'].'000'+0,
    			(float)$v['goods_avg']
    		);
    	}
    	return $retData;    
    }
    /************************结束处理药品曲线图数据************************/
    /************************开始处理自定义药品************************/
    public function getCustomGoods(){
    	
    }
    /************************结束处理自定义药品************************/


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
		$cacheData = array(
			'cates'   => $data,
		);
		S('Data_Cache', $cacheData);
		return $data;
	}

	/**
	 * 根据药品id获取药品对应的属性
	 * @param unknown $id 药品id
	 */
	public function getSpecs($id){
		if(intval($id) > 0){
			$sql = 'select b.id,b.attr_name from ydw_goods a LEFT JOIN ydw_spec b on FIND_IN_SET(b.ID,a.goods_attr_ids) where a.status = 1 and a.id = %d and b.status = 1 ORDER BY b.sort';
			return $this->query($sql, $id);
		}
		return false;
	}
	// 修改前
	protected function _before_update(&$data, $option)	{
		//处理上传图片
    	if(!empty($_FILES['main_img']['name'])) { 		
    		if($_FILES['main_img']['error'] != 0) {
    			$this->error = '请选择图片';
    			return false;
    		}
    		$imgConfig = C('PICTURE_UPLOAD');
    		$imgConfig['savePath'] = 'Goods/';
    		$res = uploadImg('main_img', $imgConfig);
    		if($res['code'] == 1){
    			$data['goods_img'] = $res['file'][1];    		
    			//处理原有图片
    			$info = $this->info(I('post.id'), 'goods_img');    			
    			$old_img = '.'.$info['goods_img'];
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
