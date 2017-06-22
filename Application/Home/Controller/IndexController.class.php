<?php
namespace Home\Controller;

class IndexController extends HomeController {

	
	/**
	 * 网站首页展示
	 */
    public function index(){  
    	$cacheData = $this->getIndex_Cache();  	
    	$cates   = $cacheData['cates'];
    	$banners = $cacheData['banners'];
    	$notices = $cacheData['notices'];
    	/* $cacheData = S('Index_Cache');
    	if(!$cacheData){
    		//获取所有一级分类以及子分类
    		$cates = D('Goods')->getAllCatsForNav();    		
    		//取出相应的banner信息
    		$banners = D('Banner')->getAllBanners();
    		//获取最新公告
    		$notices = D('Market')->getLatestInfo();
    		
    		S('Index_Cache',array(
    			'cates'   => $cates,
    			'banners' => $banners,
    			'notices' => $notices
    		));
    	}else{  		
    		$cates   = $cacheData['cates'];    		
    		$banners = $cacheData['banners'];
    		$notices = $cacheData['notices'];
    	} */    	

    	//获取最新求购的10条数据、
    	$demands = D('demand')->getDemandTen();    	
    	$this->assign('demands', $demands);

        //获取最新供应的10条数据、
        $supplys = D('supply')->getSupplyTen();   
        $this->assign('supplys', $supplys);

		//获取最新物流的5条数据、
		$companyShow=A('CompanyShow');
		$delivery = $companyShow->getDeliveryIndex(5);
		$this->assign('delivery',$delivery);

		//获取最新仓库的5条数据、
		$store = $companyShow->getStoreIndex(5);
		$this->assign('store',$store);

		$cateJson=json_encode($this->_simplefield($cates));
        $a = json_decode($cateJson,true);
        $t = $a;
        // var_dump($a[0]);
        foreach ($a as $k => $v) {
            foreach ($v['c'] as $key => $val) {
                $t[$k]['c'][$key]['cg'] = iconv("utf-8","gbk", $val['cg']);
               
            }  
        }
        foreach ($t as $t_k => $t_v) {
            asort($t[$t_k]['c']);
            foreach ($t_v['c'] as $t_key => $t_val) {
                $t[$t_k]['c'][$t_key]['cg'] = iconv("gbk","utf-8", $t_val['cg']);
            }  
        }
        foreach ($t as $k => $v) {
            $t[$k]['c'] = array_values($v['c']);
        }
        $cateJsons=json_encode($t);
		$this->assign('cateJson',$cateJsons);
    	$this->assign('cates',$cates);
    	$this->assign('banners', $banners);
    	$this->assign('notices', $notices);
    	$this->display();
    }
    
    /**
     * 异步获取首页需求列表------回头优化再搞
     */
    public function ajaxDemand(){
    	
    	if(IS_AJAX){
    		$p = I('get.p',1);//0，代表1-5条；1，代表6-10条
    		$list = D('demand')->getDemandByPage($p);
    		 
    		if ($list) {    			
    			$this->assign('list', $list);
    			//返回某分类下的商品的html文本
    			$liHtml = $this->fetch('index_demand');
    			$this->ajaxReturn(array(
    				'code' => 1,
    				'msg'  => '',
    				'html' => $liHtml
    			));    			
    		}else{
    			$this->ajaxReturn(array(
    				'code' => 0,
    				'msg' => '网络故障，获取数据失败~',
    			));
    		}	
    	}    	
    }   
    /**
     * 生成验证码
     */
    public function verify(){
    	$config =  array(
    		'codeSet'  =>  '2346789abcdehkmnpqtvy',
    		'length'      =>   4,     // 验证码位数
    		'fontttf' =>'4.ttf',
    		'useNoise'    =>    false, // 关闭验证码杂点
    		'useCurve'  => false,
    		'fontSize' => 15
    	);
    	$verify = new \Think\Verify($config);
    	$verify->entry();
    } 
    
    public function test(){
    	if(IS_POST){
    		$post = I('post.');
    		
    		if(clearXSS($post['custom_name'])){
    			 
    		}else{
    			if(!intval($post['cate_id']) || !clearXSS($post['cate_name'])){ $this->dieMsg('请选择药品分类~');	}
    			if(!intval($post['goods_id']) || !clearXSS($post['goods_name'])){	$this->dieMsg('请选择药品~'); }
    			// if(!$post['goods_attr_id'] || !$post['goods_attr_name']){ $this->dieMsg('请选择药品规格~'); }
    		}
    		if((int)$post['num'] <= 0){ $this->dieMsg('请填写求购数量~'); }
    		
    		if(!clearXSS($post['origin_type'])){
    			$this->dieMsg('请选择产地类型~');
    		}else{
    			if(intval($post['origin_type']) == 3){
    				if(!clearXSS($post['prov_select'])){
    					$this->dieMsg('请选择产地~');
    				}
    			}
    		}
    		if(!clearXSS($post['contacts'])){
    			$this->dieMsg('请输入联系人~');
    		}
    		if(!clearXSS($post['mobile'])){
    			$this->dieMsg('请输入正确的手机号~');
    		}
    		if(!clearXSS($post['details'])){
    			$this->dieMsg('请输入详情~');
    		}

			foreach($post as $k=>$v){
				$post[$k]=clearXSS($v);
			}
    		$res = D('Demand')->savePublish($post);
    		if((int)$res > 0){
    			$this->dieMsg('发布成功', 1);
    		}else{
    			$this->dieMsg('发布失败');
    		}
    	}
    	//先判断缓存中是否存在
    	$cacheData = S('Index_Cache');
    	if(!$cacheData){
    		//获取所有一级分类以及子分类
    		$cates = D('Goods')->getAllCatsForNav();
    	}else{
    		$cates = $cacheData['cates'];
    	}
    	
    	$this->assign('cates', $cates);
    	$this->display();
    }
    // 供应
    public function tests(){
         if(IS_POST){
            $post = I('post.');
            if(trim($post['custom_name'])){
    			 
    		}else{
    			if(!intval($post['cate_id']) || !clearXSS($post['cate_name'])){ $this->dieMsg('请选择药品分类~');	}
    			if(!intval($post['goods_id']) || !clearXSS($post['goods_name'])){	$this->dieMsg('请选择药品~'); }
    			// if(!$post['goods_attr_id'] || !$post['goods_attr_name']){ $this->dieMsg('请选择药品规格~'); }
    		}                
                if((int)!$post['num']){ $this->dieMsg('请填写求购数量~'); }                    
                if(!intval($post['price_type'])){
                    $this->dieMsg('请选择价格类型~');                  
                }else{
                    if(intval($post['price_type'] == 1)){//1代表单价
                        if((float)$post['price'] <= 0){
                            $this->dieMsg('请输入单价~');                            
                        }
                    }
                }
                if(!clearXSS($post['origin_type'])){
                    $this->dieMsg('请选择产地类型~');
                }else{
                    if(intval($post['origin_type']) == 3){
                        if(!$post['prov_select']){
                            $this->dieMsg('请选择产地~');
                        }
                    }
                }
                if(!clearXSS($post['supply_prvo'])){
                    $this->dieMsg('请选择货源地~');
                }
                if(!clearXSS($post['contacts'])){
                    $this->dieMsg('请输入联系人~');
                }
                if(!clearXSS($post['mobile'])){
                    $this->dieMsg('请输入正确的手机号~');
                }
                if(!clearXSS($post['details'])){
                    $this->dieMsg('请输入详情~');
                }
			 	foreach($post as $k=>$v){
					$post[$k]=clearXSS($v);
				}
                //qq号不用必填 
                $res = D('Supply')->savePublish($post);
                if((int)$res > 0){
                    $this->dieMsg('发布成功', 1);
                }else{
                    $this->dieMsg('发布失败');
                }
            }else{
            //先判断缓存中是否存在
            $cacheData = S('Index_Cache');
            if(!$cacheData){
                //获取所有一级分类以及子分类
                $cates = D('Goods')->getAllCatsForNav();
            }else{
                $cates = $cacheData['cates'];
            }
            $this->assign('cates', $cates);
            $this->display();
        } 
    }
     public function pic_updata(){
        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'Supply/';
        $res = uploadImg('file', $img_config, array(array(200,200)));
         if($res['code']==1)
        {
            $data['status']=1;
            $data['path']=$res['file'][1];
            $data['msg']='上传成功';
        }else{
            $data['status']=0;
            $data['msg']=$res['msg'];
        }

         $this->ajaxReturn($data);
    }

    public function ceshi(){
    	$list = M('supply')->where('id>650')->field('id,pic')->select();
    	foreach ($list as $key => $value) {
    		var_dump($value['id']);
    		$pic= str_replace('/m', '/thumb_150x150_', $value);
    		$data['pic'] = $pic;
    		if(M('supply')->where(['id' => $value['id']])->save($data)){
    			echo  1;
    		}else{
    			echo 0 ;
    		}
    		var_dump($pic);
    	}
    	// var_dump($list);
    }

	//精简分类字段名字(仅供处理分类字段信息使用)(jingwei)
	private function _simplefield($cate){
		$newCate=array();
		foreach($cate as $k=>$v){
			$tmp=array();
			$tmp['i']=$v['id'];
			$tmp['t']=$v['title'];
            $goods = M('GoodsHot')->where(['cate_id' =>$v ['id']])->getfield('goods_id');
            $goods = explode(',',$goods);
            $map['id'] = array('in', $goods);
            $goods_name= M('goods')->where($map)->getfield('id,cate_id,goods_name',true);
            $tmp['hot']= array_values($goods_name);
			if(is_array($v['_child'])){
				$c_tmp=array();
				foreach($v['_child'] as $c_k=>$c_v){
					$children=array();
                    $children['cg']=$c_v['goods_name'];
					$children['ci']=$c_v['id'];
					$c_tmp[]=$children;
				}
				$tmp['c']=$c_tmp;
			}
			$newCate[]=$tmp;
		}
		return $newCate;
	}

}