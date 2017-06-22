<?php
namespace Home\Controller;

/**
 * 前台需求控制器
 * @author wpf
 *
 */
class DemandController extends HomeController {
	
	private $model;
	
	/* 初始化 */
	protected function _initialize(){
		
		parent::_initialize();
		
		$this->model = D('Demand');		
	}
	/* 我的求购---暂时不用 */
    public function index02(){
    	
    	$this->home_login();
    	
    	$p = I('get.p', 0, 'intval'); // 当前页码
    	$limitRecord = 10; //单页最多几条数据
    	$condition = array( 'a.uid' => $this->uid);//查询列表条件
    	$searchParams = array('cure=true'); //搜索条件
    	 
    	$count = $this->model->countList($condition);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	 
    	$list = $this->model->getList($condition, $offset, $limitRecord);    	
    	
    	//生成分页html
    	$pageModel = new \Org\Com\Page;
    	$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false, 3);
    	 
    	$this->assign('pageHtml', $pageHtml);
    	$this->assign('list', $list);
    	$this->assign('act', 'dem');
    	$this->meta_title = '我的求购';
    	$this->display();    	
    }
    /* 我的求购列表 */
    public function index(){
    	$this->home_login();
    	$condition = array( 'a.uid' => $this->uid);
    	
    	//$orderCnt = $this->model->getOrderCntByStatus();
 		$list =  $this->model->getList($condition);    	
 		
 		$this->assign('list', $list);
 		//dump($list);
    	//$this->assign('orderCnt',$orderCnt);
    	$this->assign('act', 'dem');
    	$this->meta_title = '我的求购';
    	$this->display();
    }
    /* 发布求购 */
    public function publish(){    	
    	if(IS_AJAX && IS_POST){    		
    		$custom_name = clearXSS(I('post.custom_name'));     		
    		$data = array();
    		if($custom_name){
    			$data['custom_name'] = $custom_name;
    		}else{
    			$data['cate_id']  	   = I('post.cate_id', 0, 'intval');
    			$data['goods_id'] 	   = I('post.goods_id', 0, 'intval');
    			$data['cate_name']     = clearXSS(I('post.cate_name'));
    			$data['goods_name']    = clearXSS(I('post.goods_name'));
    			$data['goods_attr_id'] = I('post.goods_id', 0, 'intval');
    			$data['goods_attr_name'] = clearXSS(I('post.goods_attr_name'));
    		} 
    		$data['qq'] 	  	 = clearXSS(I('post.qq'));
    		$data['num'] 	  	 = clearXSS(I('post.num'));    
    		$data['uid']      	 = session('user_sign.id');
    		$data['mobile']   	 = I('post.mobile');
    		$data['details']  	 = clearXSS(I('post.details'));
    		$data['contacts'] 	 = clearXSS(I('post.contacts'));    	
    		$data['pay_type']   	 = I('post.pay_type', 0, 'intval');
    		$data['origin_type'] = I('post.origin_type', 0 , 'intval');    		
    		if($data['origin_type'] == 3){
    			$prov_select = I('post.prov_select', 0 , 'intval');
    			$city_select = I('post.city_select', 0 , 'intval');
    			$area_select = I('post.area_select', 0 , 'intval');
    			//在这里组合要保存的地区code值，以及保存的地区全称
    			if($area_select > 0){
    				$data['origin_code'] = $area_select;
    			}elseif ($city_select > 0){
    				$data['origin_code'] = $city_select;
    			}else{
    				$data['origin_code'] = $prov_select;
    			}
    			$data['origin_area'] = clearXSS(I('post.origin_area'));
    		}
    		
    		if((int)$data['uid'] <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '请先登录'));
    		if(!trim($custom_name)){
    			if($data['cate_id'] <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '请选择药品分类')); }
    			if($data['goods_id'] <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '请选择药品')); }
    		}
    		if(!trim($data['num'])){ $this->ajaxReturn(array('code' => 0,'msg' => '请填写求购数量')); }
    		if($data['origin_type'] <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '请选择产地类型')); }
    		if($data['origin_type'] == 3){ 
    			if($prov_select <= 0){ $this->ajaxReturn(array('code' => 0,'msg' => '请选择产地')); }
    		}    		
    		if(!trim($data['contacts'])){ $this->ajaxReturn(array('code' => 0,'msg' => '请输入联系人')); }
    		if(!check_mobile($data['mobile'])){ $this->ajaxReturn(array('code' => 0, 'msg' => '请输入联系电话')); }    		
    		if(!trim($data['details'])){ $this->ajaxReturn(array('code' => 0,'msg' => '请输入详情')); }   
    		
    		$res = $this->model->addData($data);
    		if((int)$res > 0){ $this->ajaxReturn(array('code' => 1,'msg' => '发布成功')); }
    		$this->ajaxReturn(array('code' => 0,'msg' => '发布失败'));
    	}else{
    		$this->home_login();
    		
    		//先判断缓存中是否存在
    		$cacheData = $this->getIndex_Cache();
    		$cates = $cacheData['cates'];
			$cateJson = json_encode($this->_simplefield($cates));
						
			
			$this->assign('cateJson',$cateJson);			
    		$this->assign('cates', $cates);
    		$this->assign('act', 'dem_pub');
    		$this->meta_title = '发布求购';
    		$this->display();
    	}    	
    }
    /* 求购详情页 */
	public function detail(){		
		//验证id是否存在
		$id = I('get.id', 0, 'intval');
		if(!$id || (int)$id <= 0){
			$this->redirect('/');
		}
		//判断求购是否存在
		$info = $this->model->getOne(array('id' => $id));		
		if(!$info){
			$this->redirect('/');
		}
		
		//判断当前登录与发布求购是否为同一个人
		$tenderModel = D('Tender');
		$uid = session('user_sign.id');
		
		if((int)$uid > 0){
			if($uid == $info['uid']){
				//若为同一个人则读取所有投标（回头可以写成异步的）
				$p = I('get.p', 0, 'intval'); // 当前页码				
				$limitRecord = 16; // 单页最多几条数据
				$condition = array( 'demand_id' => $id);//查询列表条件				
				
				$count = $tenderModel->countList($condition);
				$totalPage = ceil($count / $limitRecord);
				if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
				$offset = ($p-1) * $limitRecord;
				$limit = " {$offset},{$limitRecord}";
				
				$list = $tenderModel->getList($condition, $offset, $limitRecord);

				foreach ($list as $k => $v){
					$list[$k]['imgs'] = getImgs($v['imgs']);
				}				
				if($count > $limitRecord){
					//生成分页html
					$pageModel = new \Org\Com\Page;
					$pageHtml = $pageModel->show($count, $limitRecord, $p);
					$this->assign('pageHtml', $pageHtml);
				}						
				$this->assign('list', $list);				
				$this->assign('same', 1);
			}else{
				//若不为一个人则读取自己（根据药品id和用户id）
				$tender = $tenderModel->getOne(array('demand_id' => $id,'uid' => $uid));
				//在这里处理一下投标图片，只读取第一个图片
				if($tender['imgs']){
					$tender['imgs'] = explode(',', $tender['imgs'])[0];	
				}				
				$this->assign('tender', $tender);
				$this->assign('same', 0);
			}
		}		
		$this->assign('info', $info);
		$this->assign('act', 'dem');
		$this->meta_title = '求购'.$info['goods_name'];
		$this->display();
	}
	/* 求购列表 */
	public function lst(){		
		$p = I('get.p', 0, 'intval'); // 当前页码
		$gid = I('get.id', 0, 'intval');//获取药品id，根据药品id获取对应的求购信息
		$q = get_filter_param(I('get.q', ''));
		$sub = get_filter_param(I('get.sub', ''));
		//检测访问者的ip
        if(isset($_SERVER)){
            if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        }else {
            if(getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            }else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            }else{
                $realip = getenv("REMOTE_ADDR");
            }
        }
        //调用getIpNum方法 获得访问ip 10秒内请求的次数
        $num = getIpNum($realip);

		if($sub){
			$this->assign('alias_name',$sub);
            $datas['goods_name'] = $sub;
            $datas['alias_name'] = $q;
        }else{
        	$list =  M('SearchHot')->where(['goods_name' => $q])->getField('alias_name');
            if($list){
                $this->assign('alias_name',$q);
                $datas['goods_name'] = $q;
                $datas['alias_name'] = $list;
                $q = $list;
            }else{
                $datas['goods_name'] = $q;
                $datas['alias_name'] = '';
            }    
        }
        if($q){
            $goods_hot = M('SearchHot')->where($datas)->getField('goods_hot');
            if($goods_hot){
                $data['goods_hot'] = $goods_hot + 1 ;
                M('SearchHot')->where($datas)->save($data);
            }else{
            	if($num < 3){
	            	$goods_hots = M('SearchCustom')->where($datas)->getField('goods_hot');
	            	if($goods_hots){
	            		$data['goods_hot'] = $goods_hots + 1 ;
	                	M('SearchCustom')->where($datas)->save($data);
	            	}else{
	            		$data['goods_name'] = $q;
	                	$data['goods_hot'] = 1;
	                	M('SearchCustom')->add($data);
	            	}
	            } 
            }
        }	
		$limitRecord = 16; // 单页最多几条数据
    	$condition = array( 'a.status' => array('neq',-1));//查询列表条件    	
    	$searchParams = array('cure=true'); //搜索条件
    	    	
    	if($gid > 0){
    		$condition['a.goods_id'] = $gid;
    	}
    	if($q){
    		$condition['a.goods_name'] = array('like', '%'. $q .'%');
    		$searchParams[] = 'q='.$q;
    	}    	
    	
		$count = $this->model->countList($condition);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	
		$list = $this->model->getList($condition, $offset, $limitRecord);
    	
		if($count > $limitRecord){
			//生成分页html
			$pageModel = new \Org\Com\Page;
			$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams));
			$this->assign('pageHtml', $pageHtml);
		}    	
		//获取对应的求购分类和求购药品
		if($gid > 0){
			if(count($list) > 0){
				$this->assign('cate_name', $list[0]['cate_name']);
				$this->assign('goods_name', $list[0]['goods_name']);
			}else{
				$goods = D('Goods')->getOne($gid);
				if($goods){
					$this->assign('cate_name', $goods['cate_name']);
					$this->assign('goods_name', $goods['goods_name']);
				}
			}	
		}		
		//先判断缓存中是否存在
		$cacheData = $this->getIndex_Cache();
		$cates = $cacheData['cates'];
				
		$this->assign('list', $list);
		$this->assign('q', $q);
		$this->assign('cates', $cates);
		$this->assign('act', 'dem');
		$this->meta_title = '求购列表';
		$this->display();
	}
	/* 投标 */
	public function tender(){
		if(IS_AJAX && IS_POST){
			//首先判断价格，联系人，联系电话是否输入正确
			$data = array();			
			$data['uid']       = session('user_sign.id');
			$data['imgs']	   = implode(',', I('post.imgs'));
			$data['price'] 	   = clearXSS(I('post.price'));
			$data['mobile']    = I('post.mobile');
			$data['remarks']   = clearXSS(I('post.remarks'));
			$data['contacts']  = clearXSS(I('post.contacts'));
			$data['demand_id'] = I('post.id', 0, 'intval');
			
			if((int)$data['uid'] <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '请先登录'));						
			if(!$data['contacts']) $this->ajaxReturn(array('code' => 0, 'msg' => '请输入联系人'));			
			if(!check_mobile($data['mobile'])) $this->ajaxReturn(array('code' => 0, 'msg' => '请输入联系电话'));
						
			$res = D('Tender')->addData($data);
			$this->ajaxReturn($res);
		}else{
			$this->home_login();
			
			$p = I('get.p', 0, 'intval'); // 当前页码
			$limitRecord  = 16; // 单页最多几条数据
			$condition    = array( 'a.uid' => $this->uid);//查询列表条件
			$searchParams = array('cure=true'); //搜索条件
			$tenderModel = D('Tender');
			
			$count = $tenderModel->countList($condition);
			$totalPage = ceil($count / $limitRecord);
			if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
			$offset = ($p-1) * $limitRecord;
			$limit = " {$offset},{$limitRecord}";
			
			$list = $tenderModel->getManyList($condition, $offset, $limitRecord);
			
			if($count > $limitRecord){
				//生成分页html
				$pageModel = new \Org\Com\Page;
				$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false, 3);
				$this->assign('pageHtml', $pageHtml);
			}
			
			$this->assign('list', $list);
			$this->assign('act', 'tender');
			$this->meta_title = '我的投标';
			$this->display();
		}
	}	
	/* 中标 */
	public function win(){
		if(IS_AJAX && IS_POST){
			$data = array();
			$data['uid'] = session('user_sign.id');
			$data['demand_id'] = I('post.id', 0, 'intval');
			$data['tender_id'] = I('post.tid', 0, 'intval');
			
			if((int)$data['uid'] <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '请先登录'));
			
			$tender = D('Tender');
			$res = $tender->updateStatus($data);
			$this->ajaxReturn($res);
		}
	}	
	/* 处理流程状态信息 */
	public function doDemand(){
		if(IS_AJAX && IS_POST){
			$id    = I('post.id', 0, 'intval');			
			$inx   = I('post.inx', 0, 'intval');
			$uid   = session('user_sign.id');			
			$data  = array( 'id' => $id, 'uid' => $uid );
			if($uid <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '请先登录'));
			if($id <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '非法操作'));
			
			switch ($inx){
				case 1://处理支付托管资金
					$type = I('post.type');
					if(!in_array($type, [0,1])){
						$this->ajaxReturn(array('code' => 0, 'msg' => '非法操作'));
					}
					$data['type'] = $type;
					break;
				case 2://处理发货
					$tender_id = I('post.tid', 0, 'intval');
					if($tender_id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '非法操作'));
					$data['tender_id'] = $tender_id;			
					break;
				case 3://处理签收
					break;
			}
			$model = D('Demand');
			$code  = $model->updateField($data, $inx);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
		}
	}
	/* 异步获取支付模块信息 */
	public function ajaxPayModal(){
		if(IS_AJAX && IS_GET){
			$id    = I('get.id', 0, 'intval');
			$uid   = session('user_sign.id');
			$data  = array( 'id' => $id, 'uid' => $uid );
				
			if($uid <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '请先登录'));
			if($id <= 0) $this->ajaxReturn(array('code' => 0, 'msg' => '非法操作'));
				
			$model = D('Demand');
			$code = $model->payModal($data);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
		}
	}
	/* 根据商品获取该商品的所有需求 */
	public function ajaxGetGoodsByCateId(){
		if(IS_AJAX && IS_GET){
			$cate_id = I('get.cate_id', 0, 'int');
			$gid = I('get.id', 0, 'int');
			if($cate_id <= 0){
				$this->ajaxReturn(array( 'code' => 0, 'msg' => '网络连接超时，请您稍后重试'));				
			}
			//先判断缓存中是否存在
			$cacheData = $this->getIndex_Cache();
			$cates = $cacheData['cates'];
			foreach ($cates as $v){
				if($cate_id == $v['id']){
					$goods = M('GoodsHot')->where(['cate_id' =>$v ['id']])->getfield('goods_id');
           			$goods = explode(',',$goods);
            		$map['id'] = array('in', $goods);
            		$goods_name= M('goods')->where($map)->getfield('id,cate_id,goods_name',true);
            		$res['hot']= array_values($goods_name);
					$res['goods'] = $v['_child'];
					break;
				}
			}
			if($res){				
				//返回某分类下的商品的html文本
				$module = I('get.type') ?  'supply' : 'demand';
				$this->assign( array( 'res' => $res['goods'], 'cate_id' => $cate_id, 'gid' => $gid, 'module' => $module ) );
				//只让返回id和药材名称  所以暂时注释掉
				$liHtml = $this->fetch('demand-goods');
				$liHtmls['hot'] = $res['hot'];
				foreach ($res['goods'] as $k => $v) {
					$liHtmls['goods'][$k] = array('goods_name' =>$v['goods_name'],'id' =>$v['id']);
				}
				foreach ($liHtmls['goods'] as $k => $v) {
		          $liHtmls['goods'][$k]['goods_name'] = iconv("utf-8","gbk", $v['goods_name']);
		        }

		        asort($liHtmls['goods']);
		        foreach ($liHtmls['goods'] as $k => $v){
		            $liHtmls['goods'][$k]['goods_name'] = iconv("gbk","utf-8", $v['goods_name']);
		        }
		        $liHtmls['goods'] = array_values($liHtmls['goods']);
				$this->ajaxReturn(array( 'code' => 1, 'data' => $liHtmls ));
			}else{
				$this->ajaxReturn(array( 'code' => 0, 'msg' => '该分类下暂无药品' ));	
			}	
		}
	}
	/* 异步上传图片 */
	public function ajaxUploadImg(){
		if(IS_POST){
			$img_config = C('PICTURE_UPLOAD');
			$img_config['savePath'] = 'Tender/';
			
			$res = uploadImg('file', $img_config);			
			$this->ajaxReturn($res);
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
}