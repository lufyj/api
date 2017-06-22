<?php
namespace Home\Controller;

/**
 * 前台商品控制器
 * @author wpf
 *
 */
class GoodsController extends HomeController {
	
	private $model;
	
	/* 初始化操作 */
	protected function _initialize(){
		
		parent::_initialize();
		
		$this->model = D('Goods');		
	}
	
	/**
	 * 商品展示--暂时不用
	 */
    public function index(){    	    	
    	$this->display();
    }    
    /* 商品详情 */
    public function detail(){
    	
    	$id = I('get.id',0,'intval');
    	if(!$id || (int)$id <= 0){
    		$this->error('非法操作');
    	}    	
    	//获取当前商品的详细信息
    	$info = $this->model->getOne($id);
    	//查询该用户是否关注过该药品
    	$info['favor'] = 1;    	
    	if(session('user_sign') && (int)session('user_sign.id') > 0){
    		$isexist = D('Follow')->isExist($id);
    		if($isexist){
    			$info['favor'] = 0;
    		}
    	}
        
        $info['goods_img'] = getImgs($info['goods_img'],2,1,array(300,300));
    	//获取5个求购
    	$demands = D('Demand')->getDemandTen(5, $id);
    	//获取5个供货
    	$supplys = D('Supply')->getSupplyTen(5, $id);
    	//获取5个行情
		$markets = $this->model->getMarketTen(5, $id);
		$lines	 = $this->model->getLineChart($id);
		$this->assign('lines', $lines);
    	$this->assign('demands', $demands);
    	$this->assign('supplys', $supplys);
    	$this->assign('markets', $markets);
    	$this->assign('info', $info);    	
    	$this->meta_title = $info['goods_name'].'详情';
    	$this->display();
    }    
    /* 药品行情列表页 */
    public function market(){
    	    	
    	$p = I('get.p', 0, 'intval'); // 当前页码    	
    	$gid = I('get.id', 0, 'intval');
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
    	$condition = array();//查询列表条件
    	$searchParams = array('cure=true'); //搜索条件
    	$model = D('Market');
    	
    	if($gid > 0){
    		$condition = array('a.goods_id' => $gid);
    	}
    	if($q){
    		$condition['b.goods_name'] = array('like', '%'. $q .'%');
    		$searchParams[] = 'q='.$q;
    	}
    	
    	$count = $model->countList($condition);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	 
    	$list = $model->getList($condition, $offset, $limitRecord);
    	if($count > $limitRecord){
    		//生成分页html
    		$pageModel = new \Org\Com\Page;
    		$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false);
    		$this->assign('pageHtml', $pageHtml);
    	}    	
  
    	/*****qiaoer添加的******/
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
    	$this->assign('cates', $cates);
    	$this->assign('q', $q);   
    	/******qiaoer添加的结束*******/
    	
    	$this->assign('list', $list); 
    	$this->assign('act', 'mar');  	
    	$this->meta_title = '天天行情';
    	$this->display();
    }
    /* 药品行情详情页 */
    public function market_h(){    
    	$id = I('get.id', 0 , 'intval');
    	$gmarketModel = D('Market');
    	$info = $gmarketModel->getMarketDetailById($id);
    	if($info){    		
    		$fields = 'id,title';
    		//获取上一篇
    		$nextInfo = $gmarketModel->field($fields)->where(array('id' => array('lt', $id)))->order('id desc')->find();
    		//获取下一篇
    		$lastInfo = $gmarketModel->field($fields)->where(array('id' => array('gt', $id)))->find();
    		$this->assign('lastInfo', $lastInfo);
    		$this->assign('nextInfo', $nextInfo);
    	}   	
    	$this->assign('info', $info);
    	$this->assign('act', 'mar');    	
    	$this->meta_title = $info['title'].'行情';
    	$this->display();
    }
    /**
     * 根据药品id获取对应的规格
     */
    public function ajaxGetSpecs(){
    	if(IS_GET & IS_AJAX){
    		$id = I('get.id',0,'intval');
    		$data = $this->model->getSpecs($id);
    		$this->ajaxReturn($data);
    	}
    }
    /* public function ajaxLines(){
    	$id = I('get.id');
    	//获取折线图数据
    	$lines	 = $this->model->getLineChart($id);
    	$this->ajaxReturn($lines);
    } */
    /*
    *  首页搜索输入提示
    */
    public function ajaxgetgoods(){
        if(IS_GET & IS_AJAX){
            $goods_name= I('get.goods_name',0,'trim');
            if($goods_name){
                $sql ="select goods_name,alias_name from ydw_search_hot where goods_name like '$goods_name%' order by goods_hot desc limit 15";
                $list = M()->query($sql);
                $this->ajaxReturn($list);
            }
           
        }
    }
}