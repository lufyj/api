<?php
namespace Admin\Model;
use Think\Model;

/**
 * 后台系统消息模型
 * @Author jingwei
 * @Date 2016/11/15
 */
class PushModel extends Model {
	
    /**
     * 获取系统消息列表
     * @param unknown $params  查询参数
     * @param number $show_num 当前页显示条数
     * @param number $page_num 页码
     */
	public function getList($condition, $show_num = 0, $page_num = 0){

		$where = '1=1';//查询字符串
		$limit = '';
		$_count = 0;//总个数
		$_total_page = 0;//总页数

		if(is_array($condition)){
			$param = array();
			if(is_array($condition)){
				foreach ($condition as $v){
					$param[$v['name']] = $v['value'];
				}
			}
			$where = $this->_search($param);
		}

		$_count = $this->where($where)->count();

		$_total_page = ceil($_count / $show_num);
		if($_total_page < $page_num && $_total_page > 0){
			$page_num = $_total_page;
		}else if($_total_page == 0){
			$_total_page = 1;
		}
		$limit = ($page_num - 1) * $show_num . ',' .$show_num;

		$_list = $this->where($where)
			->order('add_time desc')
			->limit($limit)
			->select();

		return array(
			'list' => $_list,
			'all_count' => $_count,
			'total_page' => $_total_page,
			'page_num' => $page_num);

	}

	/**
	 * 根据参数生成查询字符串---后端不需要对sql攻击进行验证
	 * @param unknown $condition
	 */
	private function _search($condition){
		$where='';
		if(is_array($condition)){
			foreach ($condition as $k => $v) {
				switch ($k){
					case 'platform':
						if((int)$v > 0){
							$where .= ' platform='. $v;
						}
						break;
				}
			}
			return $where;
		}
	}

	//删除已发送的推送消息
	public function pushDelete($id){
		$st=M('PushRead')->where('push_id='.$id)->delete();
		$st=$this->where('id='.$id)->delete();
		return $st;
	}

}
