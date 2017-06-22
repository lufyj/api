<?php
namespace Home\Controller;

/**
 * 前台供应控制器
 * @author wpf
 *
 */
class SupplyController extends HomeController {
	
	private $model;
	/* 初始化 */
	protected function _initialize(){
	
		parent::_initialize();
		
		$this->model = D('Supply');
		
	}
	/*2016-12-5新增  市场价格*/
	public function trend(){
		$this->assign('act','tre');
		$this->display();
	}
	/*2016-12-5新增 历史价格*/
	public function history(){
		$this->assign('act','his');
		$this->display();
	}
	/* 供应列表 */	
    public function index(){
    	
    	$this->home_login();
    	
    	$p = I('get.p', 0, 'intval'); // 当前页码    	    	
    	$limitRecord = 16; //单页最多几条数据
    	$condition = array( 'uid' => $this->uid);//查询列表条件
    	$searchParams = array('cure=true'); //搜索条件
    	
    	$count = $this->model->countList($condition);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	
    	$list = $this->model->getList($condition, $offset, $limitRecord);
    	foreach ($list as $k => $v){
    		$list[$k]['imgs'] = getImgs($v['pic']);
    		unset($list[$k]['pic']);
    	}
 		if($count > $limitRecord){
 			//生成分页html
 			$pageModel = new \Org\Com\Page;
 			$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false, 3); 			 
 			$this->assign('pageHtml', $pageHtml);
 		}
    	
    	$this->assign('list', $list);
    	$this->assign('act', 'sup');
    	$this->meta_title = '我的供应';
    	$this->display();    	
    }    
    /* 发布供应 */
    public function publish(){
        $this->home_login();
        if(IS_POST){
            $post = I('post.');
            if(trim($post['custom_name'])){
                 
            }else{
                if(!intval($post['cate_id']) || !clearXSS($post['cate_name'])){ $this->dieMsg('请选择药品分类~');    }
                if(!intval($post['goods_id']) || !clearXSS($post['goods_name'])){ $this->dieMsg('请选择药品~'); }
                // if(!$post['goods_attr_id'] || !$post['goods_attr_name']){ $this->dieMsg('请选择药品规格~'); }
            }
                if($post['num_sel'] == '1'){
                    if(!intval($post['num'])){ $this->dieMsg('请填写供应数量~'); } 
                }else if($post['num_sel'] == '2'){
                     $post['num'] = '-1';
                }                
                                   
                if(!clearXSS($post['price_type'])){
                    $this->dieMsg('请选择价格类型~');                  
                }else{
                    if(intval($post['price_type']) == 1){//1代表单价
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
			$cateJson=json_encode($this->_simplefield($cates));
			$this->assign('cateJson',$cateJson);
            $this->assign('cates', $cates);
            $this->display();
        } 
    }
    /* 供应详情 */
    public function detail(){
    	//验证id是否存在
    	$id = I('get.id',0,'intval');
    	if(!$id || (int)$id <= 0){
    		$this->redirect('/');
    	}
    	//判供应是否存在
    	$info = $this->model->getOne(array('id' => $id));
    	if(!$info){    		
    		$this->redirect('/');
    	}    	
        $data['goods_name'] = $info['goods_name'];
        $data['id'] = array('neq',$info['id']);
        $list = M('supply')->where($data)->Field('id,goods_name,price_type,price,goods_attr_name,num')->order('id desc')->limit('5')->select();
        foreach($list as $k => $v){
            if($v['price_type'] == '2'){
                $list[$k]['price'] = '面议';
            }
        }
        $this->assign('list', $list);
    	$this->assign('info', $info);
    	$this->assign('act', 'sup');
    	$this->meta_title = '供应'.$info['goods_name'];
    	$this->display();
    }
    /* 供应列表 */
    public function lst(){
    	$p = I('get.p', 0, 'intval'); // 当前页码  	    	
    	$gid = I('get.id', 0, 'intval');//获取药品id，根据药品id获取对应的求购信息
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
        $q = get_filter_param(I('get.q', ''));
    	$sub = get_filter_param(I('get.sub', ''));
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
            }       
        }
        if($q){
            $goods_hot = M('SearchHot')->where($datas)->getField('goods_hot');
            if($goods_hot){
                $data['goods_hot'] = $goods_hot + 1 ;
                M('SearchHot')->where($datas)->save($data);
            }else{
                //如果数量大于3 代表该用户ip  在10秒内请求3次   那么将不执行 添加数据库操作
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
    	$limitRecord = 16; //单页最多几条数据
    	$condition = array();//查询列表条件
    	$searchParams = array('cure=true'); //搜索条件
    	if($gid > 0){
    		$condition['goods_id'] = $gid;
    	}    	
    	if($q){
    		$condition['goods_name'] = array('like', '%'. $q .'%');
    		$searchParams[] = 'q='.$q;
    	}
    	
    	$count = $this->model->countList($condition);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	
    	$list = $this->model->getList($condition, $offset, $limitRecord);
    	foreach ($list as $k => $v){
    		$list[$k]['imgs'] = getImgs($v['pic']);
    		unset($list[$k]['pic']);
    	}   	
    	
    	//生成分页html
    	$pageModel = new \Org\Com\Page;
    	//$pageHtml = $pageModel->show($count, $limitRecord, $p, '/supply/lst.html?cu=true'.$url);
    	$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams));
 
    	//获取对应的供应分类和供应药品
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
    	
    	$this->assign('pageHtml', $pageHtml);
    	$this->assign('list', $list);
        $this->assign('cates', $cates);
    	$this->assign('q', $q);
        $this->assign('act', 'sup');
    	$this->meta_title = '供应列表';
    	$this->display();
    }
    /* 删除一个供应 */
    public function del(){    	
    	if(IS_AJAX && IS_GET){
    		$id  = I('get.id', 0 , 'intval');
    		$uid = session('user_sign.id');
    		
    		if($id <= 0){ $this->assign(array('code' => 0,'msg' => '无效提交')); }
    		if((int)$id <= 0){ $this->assign(array('code' => 0,'msg' => '请先登录')); }
    		
    		$res = $this->model->del($id);
    		if((int)$res > 0){ $this->ajaxReturn(array('code' => 1,'msg' => '删除成功')); }
    		$this->assign(array('code' => 0,'msg' => '删除失败'));
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