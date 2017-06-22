<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyInfo 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyInfoModel extends Model{

	//保存公司基本信息
	public function saveInfo($data=array()){

		if(empty($data)){
			return false;
		}

		$where['user_id']=$data['user_id'];
		$info=$this->where($where)->find();
		if(!empty($info)){
			$where['id']=intval($info['id']);
			unset($where['user_id'],$data['user_id']);
			$st=$this->where($where)->save($data);
		}else{
			$st=$this->add($data);
		}
		return true;
	}

}
