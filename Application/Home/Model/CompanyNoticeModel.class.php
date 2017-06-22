<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyNotice 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyNoticeModel extends Model{

	//保存通知信息
	public function saveInfo($data=array()){

		$where['user_id']=intval($data['user_id']);
		$info=$this->where($where)->find();
		if(!empty($info)){
			unset($data['user_id']);
			$st=$this->where($where)->save($data);
		}else{
			$st=$this->add($data);
		}

		return true;
	}

	//获取通知详细信息
	public function getInfo($id=0){
		if(empty($id)){
			return array();
		}

		$info=$this->where('id='.$id)->find();
		return empty($info)?array():$info;
	}

	//删除通知信息
	public function delInfo($id=0){
		if(empty($id)){
			return false;
		}

		$st=$this->where('id='.$id)->delete();
		return $st?true:false;
	}

}
