<?php
namespace Admin\Model;
use Think\Model;

/**
 * 需求模型
 * @author wpf
 */
class DemandModel extends Model {
	
	/**
	 * 获取求购信息
	 * @param int $id 求购id
	 * @param string $field 所需字段，默认为全部
	 */
	public function info($id, $field = ''){
		$condition = array(	'a.id' => $id );
		$field = $field ? $field : 'a.uid,a.cate_name,a.goods_name,a.goods_attr_name,a.num,a.origin_type,a.origin_area,a.contacts,a.mobile,a.status,a.deposit,a.trading_type,a.details,a.order_type,a.order_number,a.order_amount,a.create_time,b.realname,b.mobile as u_mobile,b.head_pic'; 
		$data = $this->alias('a')->field($field)
			->join('left join ydw_user b on a.uid = b.id')
			->where($condition)
			->find();
		
		return $data;		
	}	
    /**
     * 获取药品列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
    	$query = '1=1';
    	$this->_search($params, $query);//组合查询条件
    	 
    	$count = $this->where($query)->count();//获取总条数
    	$total_page = ceil($count / $show_num);//获取总页数
    	if($total_page > 0 && $total_page < $page_num){
    		$page_num = $total_page;
    	}elseif ($total_page == 1){
    		$total_page = 1;
    	}
    	$limit = ($page_num - 1) * $show_num . ',' .$show_num;
    	$list = $this->where($query)
    		->order('id desc')
    		->limit($limit)
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
     * 根据参数生成查询字符串---后端暂时不对sql攻击进行验证
     * @param unknown $params 查询参数
     * @param unknown $query  生成的查询字符串
     */
    private function _search($params, &$query){
    	foreach ($params as $v){
    		$value = trim($v['value']);
    		switch ($v['name']){
    			case 'cate_id':
    				if((int)$value > 0){
    					$query .= ' and cate_id ='.$value;
    				}
    				break;
    			case 'realname':
    				if(ctype_digit($value)){
    					$query .= ' and mobile = '. $value;
    				}elseif($value){
    					$query .= ' and contacts like \'%'. $value .'%\'';
    				}    			
    				break;
    		}
    	}    	
    }
    /**
     * 处理提交的数据
     * @param unknown $data
     */
    public function operateData($data){
    	//首先判断求购id是否存在，若存在即为更新，否则为新增
    	if((int)$data['id'] > 0){
    		$this->error = '更新失败';
    		$info = $this->info($data['id'], 'uid,goods_id');
    		if(!$info) return 0;
    		//判断是否是自定义药品
    		if($data['custom_name']){
    			$customC = array(
    				'id'  => $info['goods_id'],
    				'uid' => $info['uid']
    			);
    			$res = M('CustomGoods')->where($customC)->setField('goods_name', $data['custom_name']);
    			if($res === false) return 0;
    			$data['goods_name'] = $data['custom_name'];
    			unset($data['custom_name']);	
    		}
    		//更新求购表信息
    		$data['update_time'] = time();
    		$res = $this->save($data);
    		if($res !== false){
    			$this->error = '更新成功';return 1;
    		}
    		return 0;
    	}else{
    		//新增，先去新增一个用户    		    		
    		$this->error = '新增失败';
    		$this->_regUser($data);
    		if((int)$data['uid'] <= 0) return 0;
    		//判断是否是自定义药品
    		if($data['custom_name']){
    			$customData = array(
    				'uid' => $data['uid'],
    				'goods_name' => $data['custom_name'],
    				'create_type'=> 2,
					'create_time'=> time()    				
    			);
    			$customId = M('CustomGoods')->data($customData)->add();
    			$data['goods_id'] = $customId;
    			$data['goods_name'] = $data['custom_name'];    			
    			unset($data['custom_name']);
    		}
    		$data['create_time'] = time();
    		$data['update_time'] = time();    		
    		$data['order_number'] = get_order_sn();
    		$res = $this->data($data)->add();
    		if((int)$res > 0){
    			$this->error = '新增成功'; return 1;
    		}
    		return 0;
    	}
    }
    /**
     * 添加一个用户，返回id
     * @param unknown $data
     */
    private function _regUser(&$data){
    	$userModel = D('User');
    	$cleanData = array(
    		'mobile'   => $data['mobile'],	
    		'realname' => $data['contacts'],    		
    		'create_time'   => time(),
    		'update_time'   => time(),
    		'register_ip' => get_client_ip()
    	);
    	//先根据手机号判断是否存在
    	$info = $userModel->checkExistMobile($data['mobile']);
    	if($info){
    		$data['uid'] = $info['id'];
    	}else{
    		$res = $userModel->data($cleanData)->add();
    		if((int)$res > 0) $data['uid'] = $res;
    	}    	
    }
    /**
     * 根据求购id更新其字段
     * 
     * @param int $data 要处理的一些数据
     * @param int $inx 不同数字代表处理不同内容
     */
    public function updateField($data, $inx){
    	//判断求购信息是否存在
    	$info = $this->info($data['id'], 'a.id,a.order_number');
    	if(!$info){
    		$this->error = '求购信息不存在';
    		return 0;
    	}
    	$this->error = '操作失败';
    	$code = 0;//错误标示    	
    	$memo = '管理员';//操作记录  
    	$date = time();  	
    	switch ($inx){
    		case -1:
    			$data['status'] = -1;
    			$res = $this->save($data);
    			if($res !== false){		
    				$this->error = '操作成功';
    				$code = 1;
    				$memo .= '将当前求购状态设置为作废';
    			}
    			break;			
    		case 0:
    			//首先判断交易方式，若选择线下交易，流程直接结束    			
    			if($data['trading_type'] == 2){
    				$data['status'] = 4;
    			}
    			$res = $this->save($data);
    			if($res !== false){    				
    				$this->error = '操作成功';
    				$code = 1;
    				$memo .= '将当前交易方式设置为'.($data['trading_type']==2?'线下交易':'线上交易');
    			}
    			break;		
    		case 1:		
    			$data['status'] = 2;
    			$data['pay_time'] = $date;
    			$data['order_amount'] = $data['pay_money'];    			
    			$res = $this->save($data);
    			if((int)$res > 0){
    				//将数据保存到ydw_pay表中
    				$payData = array(
    					'pay_memo'	   => $data['pay_memo'],
    					'pay_type'	   => 2,
    					'pay_money'	   => $data['pay_money'],
    					'pay_status'   => 1,
    					'creator_id'   => UID,
    					'create_time'  => $date,
    					'order_number' => $info['order_number']
    				);
    				M('pay')->data($payData)->add();
    				$this->error = '支付成功';
    				$code = 1;
    				$memo .= '成功支付托管资金'.$data['pay_money'].'元';
    			}
    			break;
    		case 2:
    			$data['status'] = 3;
    			$data['delivery_time'] = $date;
    			$res = $this->save($data);
    			if($res !== false){
    				$this->error = '发货成功';
    				$code = 1;
    				$memo .= '发货成功';
    			}
    			break;
    		case 3:
    			$data['status'] = 4;
    			$data['sign_time'] = $date;
    			$res = $this->save($data);
    			if($res !== false){
    				$this->error = '签收成功';
    				$code = 1;
    				$memo .= '签收成功';
    			}
    			break;
    		case 10:
    			$data['pay_deposit_time'] = $date;
    			$res = $this->save($data);
    			if($res !== false){
    				//在支付保证金时给ydw_pay_deposit里填充一条数据    				
    				$payData = array(
    					'pay_memo'	   => $data['pay_memo'],
    					'pay_type'	   => 2,
    					'pay_money'	   => $data['deposit'],
    					'pay_status'   => 1,
    					'creator_id'   => UID,
    					'create_time'  => $date,
    					'order_number' => $info['order_number']
    				);
    				M('PayDeposit')->data($payData)->add();
    				$this->error = '支付成功';
    				$code = 1;
    				$memo .= '成功支付保证金'.$data['deposit'].'元';
    			}
    			break;
    	}
    	//记录日志
    	if($code == 1){
    		$logData = array(
    			'uid'	     => UID,
    			'memo'		 => $memo,
    			'demand_id'  => $data['id'],
    			'create_time'=> $date
    		);
    		M('DemandLog')->data($logData)->add();
    	}    	
    	return $code;
    }
}