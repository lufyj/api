<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyPacking 模型
 * Author: jingwei
 * Date: 2016/9/29
 */
class CompanyPackingModel extends Model{

	//处理提交的请求数据
	public function filterData($data){

		//验证是否可以进行添加或修改
		$chkAdd=$this->_chkadd(intval($data['pa_id']),$data['uid']);
		if(!$chkAdd) return '';
		$info=array();
		$info['id']=empty($data['pa_id'])?0:intval($data['pa_id']);
		$info['packing_method']=empty($data['pa_method'])?'':clearXSS($data['pa_method']);
		$info['packing_material']=empty($data['pa_material'])?'':clearXSS($data['pa_material']);
		if(is_array($data['pa_img']) && !empty($data['pa_img'])){
			$info['img']=clearXSS(implode(',',$data['pa_img']));
		}

		if($data['pa_method']=='请输入包装方式'){
			$info['packing_method']='';
		}
		if($data['pa_material']=='请输入包装材料'){
			$info['packing_material']='';
		}
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}

	//保存包装信息
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

	//获取包装详细信息
	public function getInfo($id=0){
		if(empty($id)){
			return array();
		}

		$info=$this->where('id='.$id)->find();
		return empty($info)?array():$info;
	}

	//删除包装信息
	public function delInfo($id=0){
		if(empty($id)){
			return false;
		}

		$st=$this->where('id='.$id)->delete();
		return $st?true:false;
	}

	//获取数量
	public function getCount($userId){
		$count=$this->where('user_id='.$userId)->count();
		return $count;
	}

	//获取包装信息列表
	public function getList($p=0){

		$data=array();
		$p=empty($get['p'])?0:intval($get['p']);//当前页
		$where='1=1';
		$prePage=8;
		$total=$this->where($where)->count();//总页数
		$totalPage = ceil($total / $prePage);
		if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
		$start=($p-1)*$prePage;
		$limit=$start.','.$prePage;
		//数据
		$data['list']=$this->where($where)->limit($limit)->order('id desc')->select();
		if($data['list']){
			foreach($data['list'] as $k=>$v){
				$tmp=array();
				if($v['img']){
					$tmp=explode(',',$v['img']);
				}else{
					$tmp[0]='/Public/Home/images/noimg.png';
				}
				$data['list'][$k]['img']=$tmp[0];
			}
		}
		//分页
		$pageUrl=U('CompanyShow/packingList');//分页链接
		if($total>$prePage){
			$pager=new \Org\Com\Page;
			$data['pageHtml']=$pager->show($total,$prePage,$p,$pageUrl.'?');
		}
		return $data;
	}

	//非编辑页面获取详情
    public function getInfoByIndex($id=0){
        if(empty($id))return array();
        $info=$this->where('id='.$id)->find();
        if($info)$info['imgs']=explode(',',$info['img']);
        return empty($info)?array():$info;
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