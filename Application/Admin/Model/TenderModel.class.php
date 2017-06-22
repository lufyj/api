<?php
namespace Admin\Model;
use Think\Model;

/**
 * 投标模型
 * @author wpf
 */
class TenderModel extends Model {
	/**
	 * 根据id获取投标信息 
	 * @param unknown $id
	 */
	public function info($id, $field = true){
		if(is_array($id)){
			return $this->field($field)->where($id)->find(); 
		}
		return $this->field($field)->find($id);
	}
	/**
	 * 处理中标和删除投标信息
	 * @param unknown $data
	 */
	public function doTender($data){
		$info = $this->alias('a')->field('a.imgs,a.status as a_status,a.demand_id,b.status as b_status')
			->join('left join ydw_demand b on a.demand_id = b.id')
			->where(array('a.id' => $data['id'],'b.id' => $data['did']))
			->find();		
		if(!info){ 
			$this->error = '投标信息不存在'; return 0;
		}		
		if($info['a_status'] == 0 || $info['b_status'] == -1){			
		}else{
			$this->error = '雇主已选标，不能中标或删除'; return 0;
		}
		$date = time();
		if($data['type'] == 1){
			//代表删除投标信息
			$imgs = $info['imgs'];//先删除图片
			if($imgs){
				delImgs($imgs);
			}
			unset($data['type']);
			$res = $this->where($data)->delete();
			if($res !== false){
				$this->error = '删除成功'; return 1;
			}
			$this->error = '删除失败'; return 0;			
		}else {
			unset($data['type']);
			//代表中标
			$res = $this->where($data)->save(array('status' => 1, 'choose_time' => $date));
			if($res !== false){
				//将其他投标人的状态写成2
				$this->where(array('demand_id' => $data['did'], array('id' => array('neq',$data['id']) )))->setField('status', 2);
				//将求购信息中的status写成1，并记录修改状态的人
				M('Demand')->where(array('id' => $data['did']))->setField('status', 1);
				//记录日志
				$logData = array(
						'uid'	     => UID,
						'memo'		 => '管理员'.session('user_auth.username').'投标成功',
						'demand_id'  => $data['did'],
						'create_time'=> $date
				);
				M('DemandLog')->data($logData)->add();
				$this->error = '投标成功'; return 1;
			}
			$this->error = '投标失败'; return 0;
		}
	}	
}
