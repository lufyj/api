<?php
namespace Admin\Model;
use Think\Model;

/**
 * 前台用户管理模型
 * @author wpf
 */
class UserModel extends Model {
	/**
	 * 获取一条用户信息
	 * @param unknown $id 用户id或查询数组
	 * @param string $field 要查询的字段，默认为全部
	 */
	public function info($id, $field = ''){
		$this->field($field);
		if(is_array($id)){
			return $this->where($id)->find();
		}
		return $this->find($id);
	}
    /**
     * 获取会员列表
     * @param unknown $params  查询参数
     * @param number $show_num 当前页显示条数
     * @param number $page_num 页码
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
    	$list = $this->field(true)
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
     * @param unknown $params
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'register_from':
    				if($value != ''){
    					$query .= ' and register_from = '. $value;
    				}
    				break;
    			case 'realname':
    				if(ctype_digit($value)){
    					$query .= ' and mobile = '. $value;
    				}elseif($value){
    					$query .= ' and realname like \'%'. $value .'%\'';
    				}    				
    				break;
    		}
    	}    	
    }
    /**
     * 处理提交的数据
     * @param unknown $data
     */
	public function operateData($data){
		if((int)$data['id'] > 0){
			$data['update_time'] = time();
			$res = $this->save($data);
			if($res !== false){
				$this->error = '更新成功';return 1;
			}
			$this->error = '更新失败';return 0;
		}else{
			//判断手机号是否已经存在
			$exist = $this->checkExistMobile($data['mobile']);
			if($exist){
				$this->error = '该手机号已被注册';return 0;
			}
			$data['operator_id'] = UID;
			$data['register_ip'] = get_client_ip();
			$data['create_time'] = time();
			$data['update_time'] = time();
			$res = $this->data($data)->add();
			if((int)$res > 0){
				$this->error = '新增成功';return 1;
			}
			$this->error = '新增失败';return 0;
		}
	}
	/**
	 * 验证手机是否存在
	 * @param unknown $mobile
	 */
	public function checkExistMobile($mobile){
		return $this->info(array('mobile' => $mobile));
		/* $data = $this->where(array('mobile' => $mobile))->find();
		if($data){
			return $data;
		}
		return false; */
	}
}
