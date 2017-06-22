<?php
/**
 * App端推送消息设备模型
 * Author: jingwei
 * Date: 2017/02/06
 */

namespace Api\Model;
use Think\Model;


class PushDeviceModel extends BaseModel{

	//保存推送设备信息
	public function saveDevice($pushId,$userId){
		if($this->where("device='".$pushId."'")->count()){
			$st1=$this->where("device='".$pushId."'")->delete();
		}

		if($this->where("user_id='".$userId."'")->count()){
			$st2=$this->where("user_id='".$userId."'")->delete();
		}

		$data['device']=$pushId;
		$data['user_id']=$userId;
		$data['updatetime']=time();
		$status=$this->add($data);
		return $status;
	}
}
