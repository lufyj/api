<?php
namespace Admin\Model;
use Think\Model;
/**
 * 后台认证信息管理模型
 * Author jingwei
 * Date 2016-10-24
 */

class CompanyConfirmModel extends Model {
		
    /**
     * 获取认证信息列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
    	
    	$query = 'confirm_status!=1 and step=3';
    	$this->_search($params, $query);//组合查询条件
    		
    	$count = $this->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	$list = $this ->field('id,user_id,name,certificate_type,confirm_status,license_number,organization_code,social_credit_code')
    		->where($query)
    		->limit($limit)
    		->order('id desc')
    		->select();
    	return array(
    		'list'	 => $list,
    		'page_num' => $page_num,
    		'show_num' => $show_num,
    		'all_count'  => $count,
    		'total_page' => $total_page
    	);    	
    }

    /**
     * 根据参数生成查询字符串---后端不需要对sql攻击进行验证
     * @param unknown $params
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'status':
    				if($value != ''){
    					$query .= ' and (name like \'%'. $value .'%\')';
    				}
    				break;
    			case 'confirmStatus':
    				if((int)$value > 0){
    					$query .= ' and confirm_status = '. $value;
    				}
    				break;
    		}
    	}
    }

	/**
	 * 获取已认证用户信息列表
	 * @param unknown $params
	 * @param number $show_num 每页要显示的个数
	 * @param number $page_num 要显示第几页数
	 */
	public function getConfirmList($params, $showNum = 0, $pageNum = 0){

		$userList=M('User')->where('company_auth_status=2')->field('id')->select();
		if($userList){
			$ids=array();
			$where=array();
			foreach($userList as $k=>$v){
				$ids[]=intval($v['id']);
			}

			$where['user_id']=array('in',$ids);
			$where['confirm_status']=3;
			$count=$this->where($where)->count();//获取总条数
			$totalPage=ceil($count/$showNum);//获取总页数
			if($totalPage > 0 && $totalPage < $pageNum){
				$pageNum = $totalPage;
			}elseif ($totalPage == 1){
				$totalPage = 1;
			}
			$limit = ($pageNum - 1) * $showNum . ',' .$showNum;
			$list = $this ->field('id,user_id,name,mobile,address,confirm_time')
				->where($where)
				->limit($limit)
				->order('confirm_time desc')
				->select();

			return array(
				'list'	 => $list,
				'page_num' => $pageNum,
				'show_num' => $showNum,
				'all_count'  => $count,
				'total_page' => $totalPage
			);
		}else{
			return array();
		}
	}

	/**
	 * 获取一条认证用户信息
	 * @param unknown $id 用户id或查询数组
	 * @param string $field 要查询的字段，默认为全部
	 */
	public function info($id, $field = ''){
		$field='id,user_id,name,mobile,address,confirm_time,business';
		$this->field($field);
		if(is_array($id)){
			$data=$this->where($id)->find();
		}else{
			$data=$this->find($id);
		}
		$data['businessArr']=$this->getBusinessData($data['business']);
		$data['confirm_time']=date('Y-m-d H:i:s',$data['confirm_time']);
		return $data;
	}

	//处理业务类型数据
	public function getBusinessData($busines){
		$busArr=C('business');
		$arr=explode(',',$busines);
		$newArr=array();
		foreach($busArr as $k=>$v){
			$tmp=array();
			$tmp['content']=$v;
			$tmp['code']=$k;
			$tmp['status']=in_array($k,$arr)?1:0;
			$newArr[]=$tmp;
		}
		return $newArr;
	}

	/**
	 * 处理提交的数据
	 * @param unknown $data
	 */
	public function operateData(){
		$data=$this->_filterGet();
		if($data){
			$business['business']=$data['business'];
			$status=$this->where('id='.$data['id'])->save($business);
			$this->error='编辑成功';
			return 1;
		}else{
			$this->error='编辑失败';
			return 0;
		}
	}

	//过滤提交的数据
	private function _filterGet(){

		$id=I('get.id',0,'intval');
		$arr=explode(',',clearXSS(I('get.business')));
		if($id<=0){
			return null;
		}else{
			$data['id']=$id;
			if(empty($arr[0])){
				$data['business']='';
			}else{
				$businessArr=C('business');
				$keys=array_keys($businessArr);
				foreach($arr as $k=>$v){
					if(!in_array($v,$keys)){
						return null;
					}
				}
				$data['business']=implode(',',$arr);
			}
			return $data;
		}
	}

	//处理二级域名信息
	public function domainInfo($domain){
		$data=[
			'status'=>4,
			'domain'=>''
		];
		if($domain){
			$data['status']=substr($domain,0,1);
			$data['domain']=substr($domain,2);
		}

		return $data;
	}
}
