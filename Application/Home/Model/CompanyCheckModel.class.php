<?php 
namespace Home\Model;
use Think\Model;

/**
 * CompanyCheck 模型
 * Author: jingwei
 * Date: 2016/10/10
 */
class CompanyCheckModel extends Model{

	//处理提交的请求数据
	public function filterData($data){

		//验证是否可以进行添加或修改
		$chkAdd=$this->_chkadd(intval($data['ch_id']),$data['uid']);
		if(!$chkAdd)  return '';
		$info=array();
		$info['id']=empty($data['ch_id'])?0:intval($data['ch_id']);
		$tmp_other=clearXSS($data['ch_other']);
		if(isset($data['ch_other']) && !empty($tmp_other)){
			$info['content']=empty($data['ch_other'])?'':clearXSS($data['ch_other']);
		}elseif(isset($data['ch_content'])&& intval($data['ch_content'])>=0){
			$method=C('checkMethod');
			$num=intval($data['ch_content']);
			$info['content']=$method[$num];
		}
		$info['remarks']=empty($data['ch_remarks'])?'':clearXSS($data['ch_remarks']);

		if($data['ch_other']=='请输入其它检测方法'){
			$info['content']='';
		}
		if(strlen($info['content'])>100)  return '';
		$info['user_id']=$data['uid'];
		unset($data);
		return $info;
	}
	//保存检测信息
	public function saveInfo($data=array()){
		if(empty($data)) return false;
		if(isset($data['id']) && intval($data['id'])>0){
			$where['id']=intval($data['id']);
			$where['type']=2;
			unset($data['id']);
			$st=M('CompanyProcess')->where($where)->save($data);
		}else{
			unset($data['id']);
			$data['type']=2;
			$st=M('CompanyProcess')->add($data);
		}

		return $st?true:false;
	}

	//获取代检测详细信息
	public function getInfo($id=0){
		if(empty($id)) return array();
		$info=M('CompanyProcess')->where('id='.$id.' and type=2')->find();
		return empty($info)?array():$info;
	}

	//删除代检测信息
	public function delInfo($id=0){
		if(empty($id))return false;
		$st=M('CompanyProcess')->where('id='.$id.' and type=2')->delete();
		$where['title_type']=3;
		$where['title_id']=$id;
		$del=M('Discover')->where($where)->delete();
		return $st?true:false;
	}

	//获取数量
	public function getCount($userId){
		$count=M('CompanyProcess')->where('user_id='.$userId.' and type=2')->count();
		return $count;
	}

	//获取检测信息列表
	public function getList($p=0,$type=null){

		$data=array();
		$content=$this->_get_content($type);
		if(empty($content) || $type==null){
			$where='1=1';
		}elseif($content=='custom'){
			$where['content']=array('not in ',C('checkMethod'));
		}else{
			$where['content']=$content;
		}
		$prePage=8;
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
		if(!empty($type)){
			$pageUrl=U('CompanyShow/checkList', array('t' => $type));//分页链接
			$data['t']=$type;
		}else{
			$pageUrl=U('CompanyShow/checkList');//分页链接
			$data['t']=9999;
		}

		if($total>$prePage){
			$pager=new \Org\Com\Page;
			$data['pageHtml']=$pager->show($total, $prePage, $p, $pageUrl . '?');
		}
		return $data;
	}

	//转换类型id为文本内容
	private function _get_content($id=null){
		$contentArr=C('checkMethod');
		$id=intval($id);
		$content='';
		switch($id){
			case 0:
				$content='中药材含量检验';
				break;
			case 1:
				$content='中药材常规三检';
				break;
			case 2:
				$content='中药材硫磺检验';
				break;
		}
		return $content;
	}

	//获取检测信息列表
	public function checkList(){
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

