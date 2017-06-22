<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyDelivery 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyDeliveryModel extends Model{

	//处理提交的请求数据
	public function filterData($data){

		//验证是否可以进行添加或修改
		$chkAdd=$this->_chkadd(intval($data['de_id']),$data['uid']);
		if(!$chkAdd)  return '';
		$info=array();
		$info['id']=empty($data['de_id'])?0:intval($data['de_id']);
		if(isset($data['de_begin_dist'])&& strlen($data['de_begin_dist'])==6 && ctype_digit($data['de_begin_dist'])){
			$info['begin']=formatZoneCode($data['de_begin_dist']);
		}elseif(isset($data['de_begin_city'])&& strlen($data['de_begin_city'])==4 && ctype_digit($data['de_begin_city'])){
			$info['begin']=formatZoneCode($data['de_begin_city']);
		}elseif(isset($data['de_begin_provice'])&& strlen($data['de_begin_provice'])==2 && ctype_digit($data['de_begin_provice'])){
			$info['begin']=formatZoneCode($data['de_begin_provice']);
		}else{
			$info['begin']=0;
		}

		if(isset($data['de_end_dist'])&& strlen($data['de_end_dist'])==6 && ctype_digit($data['de_end_dist'])){
			$info['end']=formatZoneCode($data['de_end_dist']);
		}elseif(isset($data['de_end_city'])&& strlen($data['de_end_city'])==4 && ctype_digit($data['de_end_city'])){
			$info['end']=formatZoneCode($data['de_end_city']);
		}elseif(isset($data['de_end_provice'])&& strlen($data['de_end_provice'])==2 && ctype_digit($data['de_end_provice'])){
			$info['end']=formatZoneCode($data['de_end_provice']);
		}else{
			$info['end']=0;
		}

		$info['type']=empty($data['de_type'])?0:intval($data['de_type']);
		$info['desc']=empty($data['de_desc'])?'':clearXSS($data['de_desc']);
		$info['add_time']=NOW_TIME;
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}

	//保存物流信息
	public function saveInfo($data=array()){
		if(empty($data)){
			return false;
		}

		if(isset($data['id']) && intval($data['id'])>0){
			$where['id']=intval($data['id']);
			unset($data['id']);
			$st=$this->where($where)->save($data);
		}else{
			unset($data['id']);
			$st=$this->add($data);
		}
		return $st?true:false;
	}

	//获取物流详细信息
	public function getInfo($id=0){
		if(empty($id)){
			return array();
		}

		$info=$this->where('id='.$id)->find();
		return empty($info)?array():$info;
	}

	//删除物流信息
	public function delInfo($id=0){
		if(empty($id)){
			return false;
		}

		$st=$this->where('id='.$id)->delete();
		$where['title_type']=2;
		$where['title_id']=$id;
		$del=M('Discover')->where($where)->delete();
		return $st?true:false;
	}

	//获取数量
	public function getCount($userId){
		$count=$this->where('user_id='.$userId)->count();
		return $count;
	}

	//验证是否达到添加上限
	private function _chkadd($id,$userId){
		if($id>0){
			$info=$this->getInfo($id);
			return empty($info)?false:true;
		}else{
			$count=$this->getCount($userId);
			return $count<5?true:false;
		}
	}
}
