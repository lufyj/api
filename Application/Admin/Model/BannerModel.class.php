<?php
namespace Admin\Model;
use Think\Model;
/**
 * 前台轮播图管理模型
 * @author wpf
 */
class BannerModel extends Model {	
	//protected $insertFields = array('title','platform','link_url','img_url','sort','key_words','status');	
	//验证规则
	protected $_validate = array(
		array('platform', 'number', '请选择展示平台', 1),
		array('title', 'require', '标题不能为空', 1),		
	);	
	//自动添加值
	protected $_auto = array(
		array('status', 1),
		array('creator_id', UID),
        array('create_time', 'time_format', 1, 'function')               
    );
	/**
	 * 获取banner列表
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
		$list = $this ->field('id,title,platform,img_url,link_url,sort,key_words,status')    		
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
     * 根据参数生成查询字符串---后端暂时不对sql攻击进行验证
     * @param unknown $params 查询参数
     * @param unknown $query  生成的查询字符串
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'platform':
    				if((int)$value > 0){
    					$query .= ' and platform ='. $value;
    				}
    				break;    			
    		}
    	}
    } 	
	// 添加前
	protected function _before_insert(&$data, $option)	{
		/**********拼凑一些添加的数据**********/		
		//处理上传图片
		if (!empty($_FILES['img']['name'])) {
			if ($_FILES['img']['error'] != 0) {
				$this->error = '请选择图片';
				return false;
			}
			$imgConfig = C('PICTURE_UPLOAD');
			$imgConfig['savePath'] = 'Banner/';
			$res = D('Picture')->uploadOne('img', $_FILES, $imgConfig);
			if($res){
				$data['img_url'] = $res;
				//暂时不考虑xss过滤
			}else{
				return false;
			}
		}else{
			$this->error = '请上传图片';
			return false;
		}	
	}
	// 修改前
	protected function _before_update(&$data, $option)	{
		//处理上传图片
		if (!empty($_FILES['img']['name'])) {
			//如果上传图片了
			if ($_FILES['img']['error'] != 0) {
				$this->error = '请选择图片';
				return false;
			}
			$imgConfig = C('PICTURE_UPLOAD');
			$imgConfig['savePath'] = 'Banner/';
			$res = D('Picture')->uploadOne('img', $_FILES, $imgConfig);
			if($res){
				$data['img_url'] = $res;
				
				//修改的图片上传成功了
				//那就删除原来的图片
				$old_img = $this->where(array('id' => I('post.id')))->getField('img_url');
				//删除图片文件
				unlink('.'.$old_img);
				//暂时不考虑xss过滤
			}else{
				return false;
			}			
		}	
	}
}
