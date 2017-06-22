<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyConfirm 模型
 * Author: jingwei
 * Date: 2016/10/20
 */
class CompanyConfirmModel extends Model{

	//保存公司认证信息
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

	//获取已认证用户的业务权限数组
	public function businessAuth($userId=0){
		$auth=array();
		$auth[1]=0;//检测
		$auth[2]=0;//物流
		$auth[3]=0;//包装
		$auth[4]=0;//加工
		$auth[5]=0;//仓库
		if($userId>0){
			$where['user_id']=$userId;
			$where['confirm_status']=3;
			$confirmInfo=$this->where($where)->field('user_id,business')->find();
			if($confirmInfo['business']){
				$arr=explode(',',$confirmInfo['business']);
				foreach($arr as $k=>$v){
					$auth[$v]=1;
				}
			}
		}

		return $auth;
	}

	//验证已认证用户是否被分配业务权限
	public function isBusinessAuth($userId=0){
		$isBusiness=false;
		if($userId>0){
			$where['user_id']=$userId;
			$where['confirm_status']=3;
			$confirmInfo=$this->where($where)->field('user_id,business')->find();
			$isBusiness=empty($confirmInfo['business'])?false:true;
		}
		$businessAuth=$isBusiness?1:0;
		session('business_auth',$businessAuth);
	}

	//获取指定用户的企业名称和电话
	public function contactInfo($uid){
		$info=$this->where('user_id='.$uid)->field('name,mobile')->find();
		return $info;
	}

	//验证用户的二级域名审核状态
	public function isPass($info){
		$status=substr($info['domain'],0,1);
		return ($status==1 || $status==2)?false:true;
	}

	//处理二级域名
	//return: error 域名不合法,  exists 域名已存在
	public function checkDomain($domain,$userId){
		if(!preg_match("/[0-9A-Za-z]{4,18}/",$domain)) return 'error';
		//唯一性验证
		$where['domain']=array('like','%|'.strtolower($domain));
		$where['user_id']=array('neq',$userId);
		$where['confirm_status']=3;
		$info=$this->where($where)->field('user_id,domain')->select();
		if($info) return 'exists';
		$domain="1|".strtolower($domain);
		return $domain;
	}

	//获取域名信息以及审核状态
	public function domainStatus($userId){
		$info=$this->where('user_id='.$userId)->field('user_id,domain,domain_remark')->find();
		$data=[
			'status'=>4,//4 表示二级域名信息为空
			'domain'=>''
		];
		if($info['domain']){
			$data['status']=substr($info['domain'],0,1);
			if($data['status']==3){
				$data['remark']=$info['domain_remark'];
				$data['domain']=substr($info['domain'],2);
			}else{
				$data['domain']="http://".substr($info['domain'],2).".yaoduwang.com";
			}
		}
		return $data;
	}
}
