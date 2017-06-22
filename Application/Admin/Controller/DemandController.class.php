<?php

namespace Admin\Controller;

/**
 * 求购列表
 * @author wpf
 */
class DemandController extends AdminController {
	
	/* 显示求购列表 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		$data = D('Demand')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;			
		}
		$this->getCates(false);		
		$this->meta_title = '需求列表';
		$this->display();
    }
    /* 查看求购详情 */
    public function detail(){
    	$id = I('get.id', 0, 'intval');
    	$model = D('Demand');
    	//获取求购信息
    	$info = $model->info($id);
    	//获取投标列表，先不分页
    	$tenders = M('Tender')->field('id,price,contacts,mobile,status,remarks,choose_time,create_time,imgs')
			->where(array('demand_id' => $id))
			->order('id desc')
    		->select();
    	//对立面的图片进行处理
    	foreach ($tenders as $k => $v){
    		$tenders[$k]['imgs'] = getImgs($v['imgs'], -1, -1);
    	}
    	//等于2为线下支付
    	if($info['trading_type'] != 2){
    		if($info['status'] > 1){
    			//假如支付状态大于1获取对应的ydw_pay中支付类型
    			$payInfo = M('Pay')->where('order_number='.$info['order_number'])->find();
    		}
    	}  		
    	$this->assign('payInfo', $payInfo);
    	$this->assign('tenders', $tenders);
    	$this->assign('info', $info);
    	$this->meta_title = '求购详情';
    	$this->display();
    }
    /** ************************处理投标（为了权限）******************/    
    public function doTender1(){
    	//删除中标
    	$this->doTender();
    }
    /* 中标 */
    public function doTender(){
    	if(IS_AJAX && IS_POST){
    		$data = array();
    		$data['id'] = I('post.id', 0, 'intval');
    		$data['did'] = I('post.did', 0, 'intval');
    		$data['type'] = I('post.type', 0, 'intval');    		    		    		
			$model	   = D('Tender');			
    		if($data['did'] <= 0 || $data['id'] <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));	
    		$code = $model->doTender($data);    		
    		$this->ajaxReturn(array('code' => $code, 'msg' => $model->getError()));
    	}
    }
    /** ************************结束投标******************************/
    /** ************************处理求购状态的改变********************/
    public function doDemand0(){
    	$this->doDemand();
    }
    public function doDemand1(){
    	$this->doDemand();
    }
    public function doDemand2(){
    	$this->doDemand();
    }
    public function doDemand3(){
    	$this->doDemand();
    }
    public function doDemand10(){
    	$this->doDemand();
    }    
    public function doDemand9(){
    	//代表处理作废
    	$this->doDemand();
    }   
    /* 处理求购的一些ajax */
    public function doDemand(){
    	if(IS_AJAX && IS_POST){
    		$demand_id = I('post.id', 0, 'intval');
    		$inx 	   = I('post.inx', 0, 'intval');
    		$model	   = D('Demand');
    		$data	   = array( 'id' => $demand_id );
    		if($demand_id <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '无效操作')); }
    		switch ($inx){
    			//处理作废状态
    			case -1:    				
    				break;
    			//处理交易类型
    			case 0:
    				$ttype = I('post.ttype', 1, 'intval');    				
    				if($ttype != 1 && $ttype != 2){ $this->ajaxReturn(array('code' => 0,'msg' => '无效操作')); }
    				$data['trading_type'] = $ttype;		
    				break;
    			//处理托管资金支付
    			case 1:
    				$pay_money = I('post.pay_money');
    				$pay_memo  = I('post.pay_memo');
    				if((float)$pay_money <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '托管金金额不能小于0')); }
    				$data['pay_money'] = $pay_money;
    				$data['pay_memo'] = $pay_memo;
    				break;
    			//处理确认发货 
    			case 2:    				
    				break;
    			//处理确认发货
    			case 3:    			
    				break;
    			//处理支付保证金
    			case 10:
    				$deposit = I('post.deposit', 0, 'floatval');
    				$pay_memo = I('post.pay_memo');
    				if($deposit <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '保证金金额不能小于0')); }
    				$data['deposit']  = $deposit;
    				$data['pay_memo'] = $pay_memo;
    				break;  				
    		}	
    		$code = $model->updateField($data, $inx);
    		$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
    	}    	
    }
    /** **********************结束处理求购状态的改变******************************/
    /* 获取求购日志 */
    public function ajaxLookLog(){
    	if(IS_AJAX && IS_GET){
    		$demand_id = I('get.id', 0, 'intval');
    		if($demand_id <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '无效操作')); }
    		   		
    		$data = M('DemandLog')->alias('a')->field('a.memo,a.create_time,b.nickname,b.mobile')
    			->join('left join ydw_member b on a.uid = b.uid')
    			->where(array('demand_id' => $demand_id))
    			->order('a.id desc')->select();
    		foreach ($data as $k => $v){
    			$data[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);    			  
    		}
    		$this->ajaxReturn($data);
    	}
    }
    /* 求购统计 */
    public function analysis(){    	
    	$this->meta_title = '求购统计';
    	$this->display();
    }
	/**************************添加/编辑求购信息 开始**********************************/
	//添加求购信息
	public function add(){
		if(IS_AJAX && IS_POST){
			$data  = array();
			$error = '';
			$model = D('Demand'); 
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
		}else{
			$cates = $this->getCates();
			$cateJson = json_encode($this->_simplefield($cates));
						
			$this->assign('cateJson',$cateJson);			
			$this->meta_title = '添加求购';
			$this->display();
		}		
	}
	/* 编辑求购信息 */
	public function edit(){
		if(IS_AJAX && IS_POST){
			$data  = array();
			$error = '';
			$model = D('Demand');
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError(),'url' => U('index')));
		}else{
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->error('无效参数');
			$model = D('Demand');
			$info = $model->info($id, 'a.*');
			if(!$info) $this->error('无效参数');
				
			//获取商品对应的规格信息
			$info['specs_list'] = D('Goods')->getSpecs($info['goods_id']);	
			$cates = $this->getCates();
			$cateJson = json_encode($this->_simplefield($cates));
			
			$this->assign('cateJson', $cateJson);
			$this->assign('info', $info);
			$this->meta_title = '编辑求购';
			$this->display();
		}
	}
	/* 处理提交的数据--暂时不考虑过滤 */
	private function _operPostData(&$data, &$error){
		$post = I('post.');
		$custom_name = trim($post['custom_name']);
		if($custom_name){
			$data['custom_name'] = $custom_name;
		}else{
			$data['cate_id']  	   = (int)$post['cate_id'];
			$data['goods_id'] 	   = (int)$post['goods_id'];
			$data['cate_name']     = trim($post['cate_name']);
			$data['goods_name']    = trim($post['goods_name']);
			$data['goods_attr_id'] = (int)$post['goods_attr_id'];
			$data['goods_attr_name'] = trim($post['goods_attr_name']);
		}
		$data['id']			 = (int)$post['id'];
		$data['qq'] 	  	 = trim($post['qq']);
		$data['num'] 	  	 = (int)$post['num'];		
		$data['mobile']   	 = trim($post['mobile']);
		//$data['deposit']  	 = (int)$post['deposit'];
		$data['details']  	 = trim($post['details']);
		$data['contacts'] 	 = trim($post['contacts']);		
		$data['origin_type'] = (int)$post['origin_type'];
		if($data['origin_type'] == 3){
			$prov_select = (int)$post['prov_select'];
			$city_select = (int)$post['city_select'];
			$area_select = (int)$post['area_select'];
			//在这里组合要保存的地区code值，以及保存的地区全称
			if($area_select > 0){
				$data['origin_code'] = $area_select;
			}elseif ($city_select > 0){
				$data['origin_code'] = $city_select;
			}else{
				$data['origin_code'] = $prov_select;
			}
			$data['origin_area'] = trim($post['origin_area']);
		}

		if(!$custom_name){
			if($data['cate_id'] <= 0) $error = '请选择药品分类';
			elseif($data['goods_id'] <= 0) $error = '请选择药品';			
		}
		if(!$error){
			if($data['num'] <= 0) $error = '请填写求购数量';
			elseif($data['origin_type'] <= 0) $error = '请选择产地类型';
			elseif($data['origin_type'] == 3){
				if($prov_select <= 0) $error = '请选择产地';
			}
			if(!$error){
				if(!$data['contacts']) $error = '请输入联系人';
				elseif(!check_mobile($data['mobile'])) $error = '请输入正确的手机号码';
				elseif(!$data['details']) $error = '请输入详情';
				//elseif($data['deposit'] != 0 && $data['deposit'] != -1) $error = '请选择支付类型';
			}			
		}	
	}
	//精简分类字段名字(仅供处理分类字段信息使用)(jingwei)
	private function _simplefield($cate){
		$newCate=array();
		foreach($cate as $k=>$v){
			$tmp=array();
			$tmp['i']=$v['id'];
			$tmp['t']=$v['title'];
			if(is_array($v['_child'])){
				$c_tmp=array();
				foreach($v['_child'] as $c_k=>$c_v){
					$children=array();
					$children['ci']=$c_v['id'];
					$children['cg']=$c_v['goods_name'];
					$c_tmp[]=$children;
				}
				$tmp['c']=$c_tmp;
			}
			$newCate[]=$tmp;
		}
		return $newCate;
	}
	/**************************添加/编辑求购信息 结束**********************************/
}