<?php
/**
 * App端推送消息模型
 * Author: jingwei
 * Date: 2017/01/06
 */

namespace Api\Model;
use Think\Model;


class PushModel extends BaseModel{

	//推送消息列表
	public function pushList($uid){
		$userInfo=M('User')->where('id='.$uid)->field('id,create_time')->find();
		if(empty($userInfo)){
			return false;
		}

		$now=time();
		if(($userInfo['create_time']+10)>$now){
			return false;
		}

		//$readData=M('PushRead')->where('user_id='.$uid.' and update_time>'.$userInfo['last_login_time'])->select();
		$readData=M('PushRead')->where('user_id='.$uid)->select();
		if($readData){
			$isRead=array();
			$isDelete=array();
			foreach($readData as $k=>$v){
				if($v['status']==1){
					$isRead[]=$v['push_id'];
				}else{
					$isDelete[]=$v['push_id'];
				}
			}
		}

		//$pushData=$this->where('add_time>'.$userInfo['last_login_time'])->field('id,message,details,add_time')->order('add_time desc')->select();
		$pushData=$this->field('id,message,details,add_time')->order('add_time desc')->select();
		if($pushData){
			$deleteArr=array();
			foreach($pushData as $k=>$v){
				if($isRead && in_array($v['id'],$isRead)){
					$pushData[$k]['st']='1';//已阅读
					$pushData[$k]['infotype']='s';//消息类项为s(代表system)
					$pushData[$k]['demand_id']='0';//为保证数据格式一致，系统消息demand_id为0
					$pushData[$k]['demand_type']='0';//为保证数据格式一致，系统消息demand_type为0
				}elseif($isDelete && in_array($v['id'],$isDelete)){
					$deleteArr[]=$k;//记录已删除的推送消息
					$pushData[$k]['st']='3';//已删除
					$pushData[$k]['infotype']='s';//消息类项为s(代表system)
					$pushData[$k]['demand_id']='0';//为保证数据格式一致，系统消息demand_id为0
					$pushData[$k]['demand_type']='0';//为保证数据格式一致，系统消息demand_type为0
				}else{
					$pushData[$k]['st']='2';//未阅读
					$pushData[$k]['infotype']='s';//消息类项为s(代表system)
					$pushData[$k]['demand_id']='0';//为保证数据格式一致，系统消息demand_id为0
					$pushData[$k]['demand_type']='0';//为保证数据格式一致，系统消息demand_type为0
				}
			}

			foreach($deleteArr as $k=>$v){
				unset($pushData[$v]);
			}

			//获取系统推送的消息,消息类项为s(代表system)
			$newData=array_values($pushData);
			//unset($deleteArr,$isDelete,$isRead,$readData,$userInfo,$pushData);
			unset($deleteArr,$isDelete,$isRead,$readData,$pushData);
		}

		//获取求购状态变更的推送消息，消息类型为d(代表demand)
		$pushDemandList=D('PushDemandinfo')->pushDemandList($uid);
		if($newData && $pushDemandList){
			//合并两种类型的消息并进行时间排序
			$mergeData=$this->_mergearr($newData,$pushDemandList);
			unset($newData,$pushDemandList);
			return $mergeData;
		}elseif($newData){
			return $newData;
		}elseif($pushDemandList){
			return $pushDemandList;
		}else{
			return array();
		}
	}

	//更新推送消息的阅读状态
	public function pushStatus($uid,$pushId,$status){
		if(in_array($status,array('1','3'))){
			$userInfo=M('User')->where('id='.$uid)->field('id,create_time')->find();
			$pushInfo=$this->where('id='.$pushId)->find();
			if(empty($userInfo) || empty($pushId)){
				return false;
			}

			$pushReadInfo=M('PushRead')->where('push_id='.$pushId.' and user_id='.$uid)->find();
			if($pushReadInfo){
				$data=[
					'status'=>$status,
					'update_time'=>NOW_TIME,
				];
				$st=M('PushRead')->where('id='.$pushReadInfo['id'])->save($data);
			}else{
				$data=[
					'user_id'=>$uid,
					'push_id'=>$pushId,
					'status'=>$status,
					'update_time'=>NOW_TIME,
				];
				$st=M('PushRead')->add($data);
			}

			unset($data,$pushReadInfo,$pushInfo,$userInfo);
			return $st;
		}else{
			return false;
		}
	}

	//合并两种类型的消息数组
	private function _mergearr($arrSystem,$arrDemand){
		$merge=array_merge($arrSystem,$arrDemand);
		$addTimeArr=[];
		foreach($merge as $k=>$v){
			$addTimeArr[]=$v['add_time'];
		}
		array_multisort($addTimeArr,SORT_DESC,SORT_NUMERIC,$merge);
		return $merge;
	}

	/**
	 * 获取状态为"未读"的系统消息
	 * @param $uid 用户id
	 */
	public function pushInfoCount($uid){
		$userInfo=M('User')->where('id='.$uid)->field('id,last_login_time')->find();
		if($userInfo){
			$pushNum=$this->where('add_time>='.$userInfo['last_login_time'])->count();
			if($pushNum){
				$pushRead=M('PushRead')->where('user_id='.$uid.' and update_time>='.$userInfo['last_login_time'])->count();
				if($pushRead){
					$n=$pushNum-$pushRead;
					if($n>0){
						return $n;
					}
				}else{
					return $pushNum;
				}
			}
		}
		return 0;
	}

}
