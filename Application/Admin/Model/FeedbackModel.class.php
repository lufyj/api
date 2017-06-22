<?php
namespace Admin\Model;
use Think\Model;
/**
 * 意见反馈模型
 * @author wpf
 */
class FeedbackModel extends Model {	
	/**
	 * 获取意见反馈列表
	 * @param unknown $params  查询参数
	 * @param number $show_num 当前页显示条数
	 * @param number $page_num 页码
	 */
	public function getList($params, $show_num = 0, $page_num = 0){
		 
		$query = '1=1';
		$this->_search($params, $query);//组合查询条件
		 
		$count = $this->alias('a')->where($query)->count();//获取总条数
		$total_page = ceil($count / $show_num);//获取总页数
		if($total_page > 0 && $total_page < $page_num){
			$page_num = $total_page;
		}elseif ($total_page == 1){
			$total_page = 1;
		}
		$limit = ($page_num - 1) * $show_num . ',' .$show_num;
		$list = $this->alias('a')->field('a.*,b.realname,b.mobile')
			->join('left join ydw_user b on a.uid = b.id')
			->where($query)
			->limit($limit)
			->order('update_time desc')
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
				case 'status':
					if($value != ''){
						$query .= ' and a.status = '. $value;
					}
					break;				
			}
		}
	}   
	/**
     * 获取一个意见反馈详细信息（这里需要进行关联查询）
     * @param int $id feedback id
     * @param string $field 所需字段     
     */
    public function info($id, $field = true){    	
    	$map = array();
    	if(is_numeric($id)){ //通过ID查询
    		$map['id'] = $id;
    	} 
    	return $this->field($field)->where($map)->find();
    }
    /**
     * 回复反馈
     * @param unknown $data
     */
    public function reply($data){
    	$data['status'] = 1;
    	$data['update_time'] = time();
    	$res = $this->save($data);
    	if($res !== false){
    		$this->error = '回复成功';return 1;
    	}
    	$this->error = '回复成功';return 0;
    }
}