<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyProcess模型
 * Author: jingwei
 * Date: 2016/10/10
 */
class CompanyProcessModel extends Model{

	//处理提交的请求数据
	public function filterData($data){

		//验证是否可以进行添加或修改
		$chkAdd=$this->_chkadd(intval($data['pr_id']),$data['uid']);
		if(!$chkAdd) return '';
		$info=array();
		$info['id']=empty($data['pr_id'])?0:intval($data['pr_id']);
		$tmp=clearXSS($data['pr_other']);
		if(isset($data['pr_other']) && !empty($tmp)){
			$info['content']=empty($data['pr_other'])?'':clearXSS($data['pr_other']);
		}elseif(isset($data['pr_content'])&& intval($data['pr_content'])>=0){
			$method=C('processMethod');
			$num=intval($data['pr_content']);
			$info['content']=$method[$num];
		}
		$info['remarks']=empty($data['pr_remarks'])?'':clearXSS($data['pr_remarks']);

		if($data['pr_other']=='请输入其它加工方式'){
			$info['content']='';
		}
		if(strlen($info['content'])>100)  return '';
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}

	//保存代加工信息
	public function saveInfo($data=array()){
		if(empty($data))return false;

		if(isset($data['id']) && intval($data['id'])>0){
			$where['id']=intval($data['id']);
			$where['type']=1;
			unset($data['id']);
			$st=$this->where($where)->save($data);
		}else{
			unset($data['id']);
			$data['type']=1;
			$st=$this->add($data);
		}
		return $st?true:false;
	}

	//获取代加工详细信息
	public function getInfo($id=0){
		if(empty($id)) return array();
		$info=$this->where('id='.$id.' and type=1')->find();
		return empty($info)?array():$info;
	}

	//删除代加工信息
	public function delInfo($id=0){
		if(empty($id)) return false;
		$st=$this->where('id='.$id)->delete();
		$where['title_type']=4;
		$where['title_id']=$id;
		$del=M('Discover')->where($where)->delete();
		return $st?true:false;
	}

	//获取数量
	public function getCount($userId){
		$count=$this->where('user_id='.$userId.' and type=1')->count();
		return $count;
	}

	//获取代加工信息列表
	public function getList($p=0,$type=null){

		$data=array();
		if($type==1 || $type==2){
			$where['type']=$type;
		}else{
			$where='1=1';
		}
		$prePage=10;
		$total=$this->where($where)->count();//总页数
		$totalPage = ceil($total/$prePage);
		if($p==0){
			$p=1;
		}elseif($p>$totalPage){
			$p=$totalPage;
		}
		$start=($p-1)*$prePage;
		$limit=$start.','.$prePage;
		//数据
		$data['list']=$this->where($where)->limit($limit)->order('id desc')->select();
		//分页
		if($type){
			$pageUrl=U('CompanyShow/mergeList', array('t' => $type));//分页链接
			$data['t']=$type;
		}else{
			$pageUrl=U('CompanyShow/mergeList');//分页链接
		}

		if($data['list']){
			$confirm=D('CompanyConfirm');
			foreach($data['list'] as $k=>$v){
				$tmp=$confirm->contactInfo($v['user_id']);
				$data['list'][$k]['contact']=$tmp['name'];
				$data['list'][$k]['mobile']=$tmp['mobile'];
			}
		}
		if($total>$prePage){
			$pager=new \Org\Com\Page;
			$data['pageHtml']=$pager->show($total, $prePage, $p, $pageUrl . '?');
		}
		return $data;
	}

	//转换类型id为文本内容
	private function _get_content($id=null){
		$contentArr=C('processMethod');
		$id=intval($id);
		$content='';
		switch($id){
			case 0:
				$content='饮片加工/生产/包装';
				break;
			case 1:
				$content='花果茶生产';
				break;
			case 2:
				$content='中药打粉';
				break;
			case 3:
				$content='中药饮品加工/生产';
				break;
			case 4:
				$content='成品配方生产';
				break;
			case 5:
				$content='保健药品包装生产';
				break;
			case 6:
				$content='custom';
				break;
		}
		return $content;
	}

	//获取加工信息列表
	public function processList(){
		$confirmModel=D('CompanyConfirm');
		$list=$this->field('user_id,content,remarks')->select();
		foreach($list as $k=>$v){
			$info=array();
			$info=$confirmModel->contactInfo($v['user_id']);
			$list[$k]['contact']=$info['name'];
			$list[$k]['mobile']=$info['mobile'];
			unset($list[$k]['user_id']);
		}
		return $list;
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
