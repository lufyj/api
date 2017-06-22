<?php
namespace Admin\Model;
use Think\Model;

/**
 * 供应模型
 * @author wpf
 */
class SupplyModel extends Model {
    
    /**
     * 获取药品列表
     * @param unknown $condition
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($condition, $show_num = 0, $page_num = 0){
    	$where = '';//查询字符串
    	$limit = '';
    	$_count = 0;//总个数    	
    	$_total_page = 0;//总页数
    	
    	if(is_array($condition)){
    		$param = array();
    		if(is_array($condition)){
    			foreach ($condition as $v){
    				$param[$v['name']] = $v['value'];
    			}
    		}
    		$where = $this->_search($param);
    	}
    	
    	$_count = $this->where($where)->count();
    	
    	$_total_page = ceil($_count / $show_num);
    	if($_total_page < $page_num && $_total_page > 0){
    		$page_num = $_total_page;
    	}else if($_total_page == 0){
    		$_total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	
    	$_list = $this->where($where)
    		->order('create_time desc')
    		->limit($limit)
    		->select();
    	

    	
    	return array('_list' => $_list, '_count' => $_count,'_total_page' => $_total_page, '_page_num' => $page_num);
    } 
    
    /**
     * 根据参数生成查询字符串---后端不需要对sql攻击进行验证
     * @param unknown $condition
     */
    private function _search($condition){
    	$where = "1 = 1";
    	if(is_array($condition)){
    		foreach ($condition as $k => $v) {
    			//$v = addSlashes(stripslashes(htmlspecialchars_decode($v)));
    			switch ($k){
    				case 'cate_id':
    					if((int)$v > 0){
    						$where .= ' and find_in_set('. $v .',cate_id)';
    					}
    					break;
    				case 'goods_name':
    					if(trim($v)){
    						$where .= ' and goods_name like \'%'. $v .'%\'';
    					}    					
    					break;
    			}
    		}
    		return $where;
    	}
    }

	//处理提交供应信息中的手机号码
	public function operateData($id=0,$mobile,$contacts){

		$uid=0;
		if($id>0){
			$supplyInfo=$this->where('id='.$id)->field('id,uid')->find();
			if($supplyInfo){
				return $supplyInfo;
			}
		}else{
			$userInfo = D('User')->checkExistMobile($mobile);
			if($userInfo){
				$uid= $userInfo['id'];
				unset($userInfo);
			}else{
				$uid = $this->_registerUser($mobile,$contacts);
			}
		}

		return $uid;
	}

	//后台注册会员(仅包含初始化信息)
	private function _registerUser($mobile = '', $realname = '')
	{
		$id = 0;
		$data['realname'] = $realname;
		$data['mobile'] = $mobile;
		$data['status'] = 1;
		$data['points'] = 0;
		$data['register_from'] = 0;
		$data['register_ip'] = get_client_ip();
		$data['operator_id'] = UID;
		$data['last_login_time'] = NOW_TIME;
		$data['create_time'] = NOW_TIME;
		$data['update_time'] = NOW_TIME;
		$data['company_auth_status'] = 0;
		$id = D('User')->add($data);
		return $id;
	}

	//保存提交数据
	public function saveSupply($isExists=false,$data){
		if($isExists && $data){
			unset($data['create_time']);
			$status=$this->where('id='.$isExists)->save($data);
		}elseif($data){
			$status=$this->add($data);
			unset($data,$info);
		}else{
			return false;
		}
		return $status;
	}

	//过滤提交的供应信息
	public function filterSupplyInfo($post){

		$data=array();
		if(clearXSS($post['custom_name']))
		{
			$CustomGoods=M('CustomGoods');
			$tmpArr['goods_name']=clearXSS(urldecode($post['custom_name']));
			if(intval($post['gid'])>150000){
				$tmpId=intval($post['gid']);
				$tmpSt=$CustomGoods->where('id='.$tmpId)->save($tmpArr);
				if($tmpSt){
					$data['goods_id'] = $tmpId;
				}else{
					//开始向custom_goods中插入数据
					$custom_data = array(
						'uid' =>$post['uid'],
						'goods_name' => $tmpArr['goods_name'],
						'create_time'=> NOW_TIME,
						'create_type'=> 2
					);
					$custom_id = $CustomGoods->add($custom_data);
					$data['goods_id'] = $custom_id;
				}
			}else{
				//开始向custom_goods中插入数据
				$custom_data = array(
					'uid' =>$post['uid'],
					'goods_name' => $tmpArr['goods_name'],
					'create_time'=> NOW_TIME,
					'create_type'=> 2
				);
				$custom_id = $CustomGoods->add($custom_data);
				$data['goods_id'] = $custom_id;
			}

			$data['goods_name'] = $tmpArr['goods_name'];
			$data['cate_id']='';
			$data['cate_name']= '';
			$data['goods_attr_id'] = 0;
			$data['goods_attr_name'] = '无';
		}else{
			if(!intval($post['cid']) || !clearXSS($post['c_name']) || !intval($post['gid']) || !clearXSS($post['g_name']))
			{
				return '药品数据不正确';
			}else{
				$data['cate_id']=intval($post['cid']);
				$data['cate_name']= clearXSS(urldecode($post['c_name']));
				$data['goods_id']= intval($post['gid']);
				$data['goods_name'] = clearXSS(urldecode($post['g_name']));
				$data['goods_attr_id'] = intval($post['levels']);
				$data['goods_attr_name'] = empty($post['attrName'])?'无':clearXSS($post['attrName']);
			}
		}

		if ((int)!$post['num']) {
			return '请填写求购数量';
		}else{
			$data['num']=intval($post['num']);
		}

		if(in_array(intval($post['priceType']),array(1,2))){
			$data['price_type']=intval($post['priceType']);
			if($data['price_type']==1){
				$data['price']=floatval($post['price']);
				if($data['price']<=0){
					return '请输入单价';
				}
			}else{
				$data['price']=0;
			}
		}else{
			return '请选择价格类型';
		}

		if(in_array(intval($post['originType']),array(1,2,3))){
			$data['origin_type']=intval($post['originType']);
			if($data['origin_type']==3){
				$data['origin_area']=clearXSS($post['origin_area']);
				if(isset($post['area_select'])&& strlen($post['area_select'])==6 && ctype_digit($post['area_select'])){
					$data['origin_code']=trim($post['area_select']);
				}elseif(isset($post['city_select'])&& strlen($post['city_select'])==4 && ctype_digit($post['city_select'])){
					$data['origin_code']=trim($post['city_select']);
				}elseif(isset($post['prov_select'])&& strlen($post['prov_select'])==2 && ctype_digit($post['prov_select'])){
					$data['origin_code']=trim($post['prov_select']);
				}else{
					return '请选择产地';
				}
			}else{
				$data['origin_area']='';
				$data['origin_code']='';
			}
		}else{
			return '请选择产地类型';
		}

		if(isset($post['supply_area'])&& strlen($post['supply_area'])==6 && ctype_digit($post['supply_area'])){
			$data['supply_code']=trim($post['supply_area']);
			$data['supply_area']=clearXSS($post['goods_area']);
		}elseif(isset($post['supply_city'])&& strlen($post['supply_city'])==4 && ctype_digit($post['supply_city'])){
			$data['supply_code']=trim($post['supply_city']);
			$data['supply_area']=clearXSS($post['goods_area']);
		}elseif(isset($post['supply_prvo'])&& strlen($post['supply_prvo'])==2 && ctype_digit($post['supply_prvo'])){
			$data['supply_code']=trim($post['supply_prvo']);
			$data['supply_area']=clearXSS($post['goods_area']);
		}else{
			return '请选择货源所在地';
		}

		$data['supply_detail']=clearXSS($post['supplyDetail'])?clearXSS($post['supplyDetail']):'';
		if(clearXSS($post['details'])){
			$data['details']=clearXSS($post['details']);
		}else{
			return '请输入详情';
		}

		$data['uid']=$post['uid'];
		$data['contacts']=clearXSS($post['contacts']);
		$data['mobile']=trim($post['mobile']);
		$data['qq']=clearXSS($post['qq']);
		$data['status']=0;
		$data['create_time']=NOW_TIME;
		$data['update_time']=NOW_TIME;
		$data['pic']=empty($post['imgPath'])?'':clearXSS($post['imgPath']);

		return $data;
	}
}
