<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyNews 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyNewsModel extends Model{

	//处理提交的数据
	public function filterData($data){

		$info=array();
		$info['id']=empty($data['id'])?0:intval($data['id']);
		$info['title']=empty($data['title'])?'':clearXSS($data['title']);
		$info['content']=empty($data['content'])?'':clearXSS($data['content']);
		$info['add_time']=NOW_TIME;
		if(isset($data['img']) && !empty($data['img'])){
			$info['img']=clearXSS($data['img']);
		}

		if($data['title']=='请输入标题' || strlen($info['title'])>60){
			return array();
		}
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}

	//保存公司动态信息
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

	//获取公司动态详细信息
	public function getInfo($id=0){
		if(empty($id)){
			return array();
		}

		$info=$this->where('id='.$id)->find();
		return empty($info)?array():$info;
	}

	//删除公司动态信息
	public function delInfo($id=0){
		if(empty($id)){
			return false;
		}

		$st=$this->where('id='.$id)->delete();
		return $st?true:false;
	}

}
