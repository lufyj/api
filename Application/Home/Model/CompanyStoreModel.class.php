<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyStore 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyStoreModel extends Model{

	//处理提交的请求数据
	public function filterData($data){

		//验证是否可以进行添加或修改
		$chkAdd=$this->_chkadd(intval($data['st_id']),$data['uid']);
		if(!$chkAdd) return '';
		$info=array();
		$info['id']=empty($data['st_id'])?0:intval($data['st_id']);
		$info['type']=empty($data['st_type'])?0:intval($data['st_type']);
		$info['size']=empty($data['st_size'])?0:clearXSS($data['st_size']);
		$info['height']=empty($data['st_height'])?0:clearXSS($data['st_height']);

		if(isset($data['st_dist'])&& strlen($data['st_dist'])==6 && ctype_digit($data['st_dist'])){
			$info['zone']=formatZoneCode($data['st_dist']);
		}elseif(isset($data['st_city'])&& strlen($data['st_city'])==4 && ctype_digit($data['st_city'])){
			$info['zone']=formatZoneCode($data['st_city']);
		}elseif(isset($data['st_provice'])&& strlen($data['st_provice'])==2 && ctype_digit($data['st_provice'])){
			$info['zone']=formatZoneCode($data['st_provice']);
		}else{
			$info['zone']=0;
		}

		$info['address']=empty($data['st_address'])?'':clearXSS($data['st_address']);
		$info['desc']=empty($data['st_desc'])?'':clearXSS($data['st_desc']);
		$info['contacts']=empty($data['st_contacts'])?'':clearXSS($data['st_contacts']);
		$info['mobile']=empty($data['st_mobile'])?0:trim($data['st_mobile']);
		if(is_array($data['st_img']) && !empty($data['st_img'])){
			$info['img']=clearXSS(implode(',',$data['st_img']));
		}
		if($data['st_address']=='请输入详细地址'){
			$info['address']='';
		}
		if($data['st_contacts']=='请输入联系人'){
			$info['contacts']='';
		}
		if($data['st_mobile']=='请输入联系电话'){
			$info['mobile']='';
		}

		if(strlen($info['address'])>90)	return '';
		if(!check_mobile($info['mobile'])) return '手机号码格式不正确';
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}

	//保存仓库信息
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

	//获取仓库详细信息
	public function getInfo($id=0){
		if(empty($id)){
			return array();
		}

		$info=$this->where('id='.$id)->find();
		return empty($info)?array():$info;
	}

	//删除仓库信息
	public function delInfo($id=0){
		if(empty($id)){
			return false;
		}

		$st=$this->where('id='.$id)->delete();
		$where['title_type']=1;
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