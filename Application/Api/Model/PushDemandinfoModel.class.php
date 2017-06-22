<?php

namespace Api\Model;
use Think\Model;

require_once VENDOR_PATH.'/rabbitmq/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 求购状态变更推送消息模型
 * @author jingwei
 * @time 2017-02-10
 */
class PushDemandinfoModel extends BaseModel{

	/**
	 * 场景：A用户发布求购，B用户投标
	 * 	1===>B用户进行投标，此时向A用户发送消息内容是：B用户进行投标
	 * 	2===>A用户选标完成后，此时向B用户发送消息内容是：B用户中标（包括后台操作）
	 * 	3===>B（后台发货）发货后，此时向A用户发送消息内容是：B用户已经发货
	 * 	4===>A用户（后台）收到货后进行签售：此时向B用户发送内容是：A用户已经签收
	 */

	/**
	 * 状态信息推送到消息队列服务器
	 * @param $type  推送消息类型
	 * @param $demandId 求购id
	 * @param $uid 接受消息的目标用户id
	 */
	public function pushInfo($type,$demandId,$uid){
		$config=C('rabbitmq');
		$infoArr=C('pushdemandinfo');

		$message=[
			'type'=>$type,
			'title'=>$infoArr[$type]['title'],
			'content'=>$infoArr[$type]['content'],
			'add_time'=>time(),
			'uid'=>$uid
		];

		$connection=new AMQPStreamConnection($config['host'],$config['port'],$config['login'],$config['password']);
		$channel=$connection->channel();
		$channel->queue_declare($config['queue'],false,false,false,false);
		$msg=new AMQPMessage(json_encode($message,JSON_UNESCAPED_UNICODE)."\n");
		$channel->basic_publish($msg,'',$config['queue']);
		$channel->close();
		$connection->close();
		$data=[
			'demand_id'=>$demandId,
			'push_type'=>$type,
			'title'=>$message['title'],
			'content'=>$message['content'],
			'add_time'=>time(),
			'uid'=>$uid,
			'status'=>'2'//标识信息初始为未读状态
		];
		$status=$this->add($data);
		return $status;
	}

	//求购状态变更推送消息
	public function pushDemandList($uid){

		$data=$this->where('uid='.$uid)->order('add_time desc')->select();
		if($data){
			$newData=array();
			foreach($data as $k=>$v){
				$tmp=[
					'id'=>$v['id'],
					'demand_id'=>$v['demand_id'],
					'demand_type'=>$v['push_type'],
					'message'=>$v['title'],
					'details'=>$v['content'],
					'add_time'=>$v['add_time'],
					'st'=>$v['status'],
					'infotype'=>'d',
				];
				$newData[]=$tmp;
				$tmp=array();
			}
			unset($data);
			return $newData;
		}else{
			return array();
		}
	}

	/**
	 * 更新推送的求购信息登录状态/删除
	 * @param $uid 用户id
	 * @param $pushId 消息id
	 * @param $status 消息状态 2 未读 1 已读 3 删除（该状态直接删除记录）
	 */
	public function pushStatus($uid,$pushId,$status){
		if($this->where('uid='.$uid.' and id='.$pushId)->count()){
			if($status==1){
				$data=[
					'status'=>1
				];
				$st=$this->where('uid='.$uid.' and id='.$pushId)->save($data);
			}else{
				$st=$this->where('uid='.$uid.' and id='.$pushId)->delete();
			}
			return $st;
		}else{
			return false;
		}
	}

	/**
	 * 获取状态为未读的求购类系统消息
	 * @param $uid 用户id
	 */
	public function pushNoReadCount($uid){
		$n=$this->where('uid='.$uid.' and status=2')->count();
		if($n){
			return $n;
		}else{
			return 0;
		}
	}
}
?>