<?php
namespace Api\Controller;
use Think\Controller;
class AppHerbalController extends BaseController {
    //药膳列表
    public function herbal(){
        if($this->client_type == 1){
            $num   = I('post.num'  , 10 ,'intval');
            $minId = I('post.minId', 0  ,'intval');
           
            if($minId){
                $data['id'] = array('lt',$minId);
            }

            $list = M('c_ysdq')->field('id,title,description,thumb,create_time')->where($data)->order('id desc')->limit($num)->select();
            foreach ($list as $k => $v) {
                $list[$k]['descriptions'] = $v['description'];
            }
            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        } 
    }
    //药膳详情
    public function herbal_detail(){
        if($this->client_type == 1){
            //药膳ID
            $atticles_id = I('post.atticles_id');

            if(!ctype_digit($atticles_id)){
                $this->ajaxDie(54);
            }

            $info = M('c_ysdq')->where(['id' => $atticles_id])->find();

            $info['content'] = preg_replace('/font\-size\:\w{1,2}.\w{1,5}\;/','',$info['content'] );
            $this->ajaxDie(1,$info);  
        }else{
            $this->ajaxDie(43);
        }
    }

    //行情 列表
    public function market(){
         if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid   =  I('post.uid',0,'intval');
            $gid   =  I('post.gid',0,'intval');
            $num   =  I('post.num',10,'intval');
            $minId =  I('post.minId',0,'intval');

            //校验参数
            $uid   ||  $this->ajaxDie(30);
            $gid   &&  $data['goods_id'] = $gid; 
            $minId &&  $data['id'] = array('lt',$minId);
           
            $list = M('goods_market')->field('id,title,description,thumb,author,belong_id,create_time,view')->where($data)->order('id desc')->limit($num)->select();
            foreach ($list as $k => $v) {
                if($v['thumb']){
                    $thumb = explode(',',$v['thumb']);
                    $list[$k]['thumb'] = $thumb[0];
                }else{
                    $list[$k]['thumb'] = '';
                }

                //如果存在,则说明是用户发布文章
                if($v['belong_id']){
                    $uid_m      =  M('user_goods_market')->where(['id' => $v['belong_id']])->getField('uid');
                    $head_pic   =  M('user')->where(['id' => $uid_m])->getField('head_pic');
                    if($head_pic){
                        $list[$k]['head_pic'] = $head_pic;
                    }else{
                        $list[$k]['head_pic'] = '';
                    }

                    $list[$k]['total_reward']   = M('user_reward') ->where(['market_id' => $v['id']])->count();
                    $list[$k]['total_comment']  = M('user_comment')->where(['market_id' => $v['id']])->count();
                    //判断该用户是否打赏过该文章
                    if(M('user_reward')->where(['reward_id' => $uid,'market_id' => $v['id'] ])->find()){
                        $list[$k]['is_reward'] = '2';
                    }else{
                        $list[$k]['is_reward'] = '1';
                    }

                }else{//不存在则是系统发布文章
                    $list[$k]['head_pic'] = '';
                    $list[$k]['total_reward']  = '';
                    $list[$k]['total_comment'] = M('user_comment')->where(['market_id' => $v['id']])->count();
                    $list[$k]['is_reward'] = '';
                }

                $list[$k]['descriptions'] = $v['description'];
                $list[$k]['create_time']  = D('user')->timeShow($v['create_time']);
            }
            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        } 
    }
    //天天行情(列表)
    public function tthq_list(){
        if($this->client_type == 1){
          
            $type     =  I('post.type',1,'intval');  //行情分类 1 天天行情 2 产地行情  3 药市动态
            $minId    =  I('post.minId',0,'intval');
            $market   =  I('post.market',1,'intval');//天天行情市场 1 亳州 2 安国 3 玉林 4 成都
            $keyword  =  clearXSS(I('post.keyword'));
          

            //获取数据
            $list = D('c_ysdt')->getLists($type, $market,$minId,$keyword);
            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43); 
        }
    }
    //天天行情（详情）app使用
    public function tthq_details(){
        if($this->client_type == 1){
            
            $id       =  I('post.id',0,'intval');
            $type     =  I('post.type',1,'intval');  //行情分类 1 天天行情 2 产地行情  3 药市动态
            $market   =  I('post.market',1,'intval');//天天行情市场 1 亳州 2 安国 3 玉林 4 成都
             //获取数据
            $result = D('c_ysdt')->getdetails($type, $market,$id);
            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43); 
        }
    }
    //指定药材药材行情(列表)
      public function goods_market(){
         if($this->client_type == 1){
           //接收参数
            $num              = I('post.num'     , 10 ,'intval');
            $minId            = I('post.minId'   , 0  ,'intval');
            $data['goods_id'] = I('post.goods_id', 0  ,'intval');

            //组装条件
            $minId   && $data['id'] = array('lt',$minId);

            $list = M('c_tthq')->field('id,title,description,thumb,create_time')->where($data)->order('id desc')->limit($num)->select();

            foreach ($list as $k => $v) {
                $list[$k]['descriptions'] = $v['description'];
            }

            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        } 
    }
    //天天行情(详情)
    public function tthq_detail(){
    	if($this->client_type == 1){
    		//接收参数
    		$tthq_id = I('post.tthq_id');//天天行情ID
    		
    		//校验参数
    		if(!ctype_digit($tthq_id)){
    			$this->ajaxDie(54);
    		}
    		
    		//给天天行情阅读量加1
    		M('c_tthq')->where(['id' => $tthq_id])->setInc('view',1);
    		
    		$result = M('c_tthq')->where(['id' => $tthq_id])->order('id desc')->find();
    		
    		if($result){
    			$this->ajaxDie(1,$result);
    		}else{
    			$this->ajaxDie(0);
    		}
    		
    	}else{
    		$this->ajaxDie(43);
    	}
    }
    //行情(详情)
    public function market_detail(){
        if($this->client_type == 1){

            $uid       = I('post.uid',0,'intval');
            $market_id = I('post.market_id');//行情ID
           
            if(!ctype_digit($market_id)){
                $this->ajaxDie(54);
            }
            //用户点击查看 给该行情阅读量加1
            M('goods_market')->where(['id' => $market_id])->setInc('view',1);
           
            $info = M('goods_market')->field('id,title,description,thumb,author,belong_id,create_time,view,content')->where(['id' => $market_id])->find();
            //过滤html标签
            $info['content'] = strip_tags($info['content']);
            $info['content'] = str_replace(array("\r\n\t", "\r", "\n","\t"), "", $info['content']); 

            if($info['thumb']){
                $imgs = explode(",",$info['thumb']);
                $info['thumb_details'] = $imgs;
            }else{
                 $info['thumb_details'] = array();
            }

            //用户是否打赏过这篇文章
            if(M('user_reward')->where(['reward_id' => $uid,'market_id' => $market_id])->find()){
                $info['is_reward'] = '2';
            }else{
                $info['is_reward'] = '1';
            }
            //判断该文章是否是用户发布
            if($info['belong_id']){
                $user_uid      = M('user_goods_market')->where(['id' => $info['belong_id']])->getField('uid');
                $head_pic      = M('user')->where(['id' => $user_uid])->getField('head_pic');
                $info['mid']   = $user_uid;
               
                if($head_pic){
                     $info['head_pic'] = $head_pic;
                }else{
                     $info['head_pic'] = '';
                }

                $info['total_reward']  = M('user_reward') ->where(['market_id' => $market_id])->count();
                $info['total_comment'] = M('user_comment')->where(['market_id' => $market_id])->count();
                //判断该文章是否是该用户发布
                $reward = M('user_reward')->field('reward_name,money')->where(['market_id' => $market_id])->select();
                foreach ($reward as $k => $v) {
                    if($uid == $user_uid){
                        $reward[$k]['reward_name']  = $v['reward_name'];
                        $reward[$k]['money']        = $v['money'].'元';
                    }else{
                        $reward[$k]['reward_name']  = $v['reward_name'];
                        $reward[$k]['money']        = '';
                    }
                }
            }else{//不存在则是系统发布文章
                $info['head_pic']      = '';
                $info['total_reward']  = '';
                $info['total_comment'] = M('user_comment')->where(['market_id' => $market_id])->count();
            }
            $info['content']     = preg_replace('/font\-size\:\w{1,2}.\w{1,5}\;/','',$info['content'] );
            $info['create_time'] = D('user')->timeShow($info['create_time']);   
            $this->ajaxDie(1,$info,$reward);  
        }else{
            $this->ajaxDie(43);
        }
    }
    //发现列表
     public function discover_list(){
        if($this->client_type == 1){
            //接受数据
            $num    = I('post.num'  , 10 , 'intval');
            $minId  = I('post.minId', 0  , 'intval');
            
            //校验数据
            $minId   &&  $data['id'] = array('lt',$minId);
            
            $list = M('discover')->Field('id,title_type,title_id')->where($data)->order('id desc')->limit($num)->select();
            if($list){
                foreach ($list as $k => $v) {
                    //仓储
                    if($v['title_type'] == '1'){
                       $list[$k]['contents']         = M('company_store')->Field('type,size,height,address,img')->where(['id' => $v['title_id']])->find();
                       $list[$k]['contents']['type'] = $this->getStoreType($list[$k]['contents']['type']);
                       if(!$list[$k]['contents']['img']){
                            $list[$k]['contents']['img'] = '/Public/Home/images/noimg.png';
                       }else{
                            $imgs = explode(',',$list[$k]['contents']['img']);
                            $list[$k]['contents']['img'] = $imgs[0];
                       } 
                    //药膳
                    }else if($v['title_type'] == '5'){
                        $list[$k]['contents'] = M('c_ysdq')->where(['id' => $v['title_id']])->Field('id,title,description,thumb,create_time')->find();
                        if(!$list[$k]['contents']['thumb']){
                            $list[$k]['contents']['thumb'] = '/Public/Home/images/noimg.png';
                        }
                        $list[$k]['contents']['descriptions'] = $list[$k]['contents']['description'];
                    //其他3个都是拿公司的logo
                    //物流
                    }else if($v['title_type'] == '2'){
                        $user_id = M('company_delivery')->where(['id' => $v['title_id']])->getField('user_id');
                        $list[$k]['contents']         = M('company_delivery')->where(['id' => $v['title_id']])->Field('begin,end,type')->find();
                        $list[$k]['contents']['type'] = $this->getDeliveryType($list[$k]['contents']['type']);
                        $list[$k]['contents']['img']  = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                        if(!$list[$k]['contents']['img']){
                            $list[$k]['contents']['img'] = '/Public/Home/images/noimg.png';
                        }
                        //先去掉省市后面追加的00
                        $list[$k]['contents']['begin'] = region($list[$k]['contents']['begin']);
                        $list[$k]['contents']['end'] = region($list[$k]['contents']['end']);
                        if($list[$k]['contents']['begin'] === '0'){
                            $list[$k]['contents']['begin'] = '全国';
                        }else{
                            $list[$k]['contents']['begin'] = M('region')->where(['REGION_CODE' => $list[$k]['contents']['begin']])->getField('REGION_NAME');
                        }
                        if($list[$k]['contents']['end'] ==='0'){
                            $list[$k]['contents']['end'] = '全国';
                        }else{
                            $list[$k]['contents']['end'] = M('region')->where(['REGION_CODE' => $list[$k]['contents']['end']])->getField('REGION_NAME');
                        }
                    //检测
                    }else if($v['title_type'] == '3'){
                        $user_id = M('company_check')->where(['id' => $v['title_id']])->getField('user_id');
                        $list[$k]['contents']= M('company_check')->where(['id' => $v['title_id']])->Field('content,remarks')->find();
                        $list[$k]['contents']['img'] = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                        if(!$list[$k]['contents']['img']){
                            $list[$k]['contents']['img'] = '/Public/Home/images/noimg.png';
                        }
                    //代加工
                    }else if($v['title_type'] == '4'){
                        $user_id = M('company_process')->where(['id' => $v['title_id']])->getField('user_id');
                        $list[$k]['contents']= M('company_process')->where(['id' => $v['title_id']])->Field('content,remarks')->find();
                        $list[$k]['contents']['img'] = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                        if(!$list[$k]['contents']['img']){
                            $list[$k]['contents']['img'] = '/Public/Home/images/noimg.png';
                        }
                    }
                }
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    //发现_主题列表
    public function discover_theme(){
         if($this->client_type == 1){

            $num =  I('post.num');
            $data['theme_status'] = '1';

            $list = M('discover')->Field('id,title_type,title_id')->where($data)->order('id desc')->limit($num)->select();
            if($list){
                foreach ($list as $k => $v) {
                    //仓储
                    if($v['title_type'] == '1'){
                       $content = M('company_store')->where(['id' => $v['title_id']])->Field('address,img')->find();
                       $list[$k]['content'] =  $content['address'];
                       $imgs = explode(',',$content['img']);
                       $list[$k]['img']= $imgs[0]; 
                    //药膳
                    }else if($v['title_type'] == '5'){
                        $content = M('c_ysdq')->where(['id' => $v['title_id']])->Field('title,thumb')->find();
                        $list[$k]['img']     = $content['thumb'];
                        $list[$k]['content'] = $content['title'];
                    //其他3个都是拿公司的logo
                    //物流
                    }else if($v['title_type'] == '2'){
                        $user_id = M('company_delivery')->where(['id' => $v['title_id']])->getField('user_id');
                        $contents = M('company_delivery')->where(['id' => $v['title_id']])->Field('begin')->find();
                        //先去掉省市后面追加的00
                        $contents['begin'] = region($contents['begin']);
                        if($contents['begin'] === '0'){
                            $list[$k]['content'] = '全国';
                        }else{
                            $list[$k]['content'] = M('region')->where(['REGION_CODE' => $contents['begin']])->getField('REGION_NAME');
                        }
                        $list[$k]['img'] = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                    //检测
                    }else if($v['title_type'] == '3'){
                        $user_id = M('company_process')->where(['id' => $v['title_id']])->getField('user_id');
                        $contents= M('company_process')->where(['id' => $v['title_id']])->Field('content')->find();
                        $list[$k]['content'] = $contents['content'];
                        $list[$k]['img'] = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                    //代加工
                    }else if($v['title_type'] == '4'){
                        $user_id = M('company_process')->where(['id' => $v['title_id']])->getField('user_id');
                        $contents= M('company_process')->where(['id' => $v['title_id']])->Field('content')->find();
                        $list[$k]['content'] = $contents['content'];
                        $list[$k]['img'] = M('company_info')->where(['user_id' => $user_id])->getField('logo');
                    }
                }
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    //获取仓库分类名字
    private function getStoreType($type_code){

        switch($type_code){
            case 0:
                $typeName='普通';
                break;
            case 1:
                $typeName='冷藏';
                break;
            case 2:
                $typeName='保温恒温';
                break;
            case 3:
                $typeName='特种';
                break;
            case 4:
                $typeName='气调';
                break;
        }
        return $typeName;    
    }
     //获取物流分类名字
    private function getDeliveryType($type_code){

        switch($type_code){
            case 0:
                $typeName='厢式/板车';
                break;
            case 1:
                $typeName='集装箱';
                break;
            case 2:
                $typeName='冷藏车';
                break;
            case 3:
                $typeName='危险品车辆';
                break;
            case 4:
                $typeName='特种车';
                break;
        }
        return $typeName;    
    }

    /****分割线  1.4新增开始 ****/
    /******行情推荐接口**** 1.4版本  *****/
    public function getMarketRecommend(){
        if($this->client_type == 1){
            $list = D('c_ysdt')->getMarketRecommend();
            //将数据处理成app想要的数据
            $subset  = array_slice($list, 0,2); 
            $subsets = array_slice($list, 2);
            if($list){
                $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $subset,'dataHot' =>$subsets));
            }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => '0','msg' =>'非法请求'));
        }
    }
    /******药市动态 + 品种分析 + 最新资讯 +产地信息列表集合  **** 1.4版本  *****/
    public function getMarketMuster(){
        if($this->client_type == 1){
            $list = D('c_ysdt')->getMarketMuster();
            if($list){
                $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => '0','msg' =>'非法请求'));
        }
    }

    /******药市动态 + 品种分析 + 最新资讯 +产地信息+ 精选(天天行情) 详情集合  **** 1.4版本  *****/
    public function getMarketDetails(){
         if($this->client_type == 1){
            //接收参数
            $id    =  I('post.id' ,0,'intval');
            $type  =  I('post.type',1,'intval');//1 药市动态  2 品种分析  3 最新资讯 4 产地信息 5 天天行情

            //校验参数
            $id    ||  $this->ajaxReturn(array('code' => '0','msg' =>'id不存在'));
            $type  ||  $this->ajaxReturn(array('code' => '0','msg' =>'类型未选择'));

            $list = D('c_ysdt')->getMarketDetails($id,$type);
            if($list){
                $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => '0','msg' =>'非法请求'));
        }

    }

    /******天天行情列表  **** 1.4版本  *****/
    public function getTthq(){
        if($this->client_type == 1){
            $minId       = I('post.minId',0,'intval');
            $market      = I('post.market',1,'intval');//天天行情市场 1 亳州 2 安国 3 玉林 4 成都
            $keyword     = clearXSS(I('post.keyword'));//搜索栏搜索
            $goods_name  = clearXSS(I('post.gn'));//选择药材搜索

            //校验参数
            if(!$market || !in_array($market,array(1,2,3,4))){
                $this->ajaxReturn(array('code' => '0','msg' =>'market参数错误'));
            }

            $list = D('c_ysdt')->getList($market,$minId,$keyword,$goods_name);
            if($list){
                $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => '0','msg' =>'非法请求'));
        }
    }
    /******天天行情详情  **** 1.4版本  *****/
    public function getdetail(){
        if($this->client_type == 1){
            $id          = I('post.id',0,'intval');
            $market      = I('post.market',1,'intval');//天天行情市场 1 亳州 2 安国 3 玉林 4 成都

            //校验参数
            if(!$market || !in_array($market,array(1,2,3,4))){
                $this->ajaxReturn(array('code' => '0','msg' =>'market参数错误'));
            }

            $list = D('c_ysdt')->getdetail($market,$id);
            if($list){
                $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => '0','msg' =>'非法请求'));
        }
    }
   
    /*天天行情 字母搜索对应的字母或药材   *******1.4版本 ******/
    public function getGoodsByLetter() {
        if($this->client_type == 1){
            //’声明变量‘
            $type    = I('post.t',0,'intval');
            $letter  = strtolower(I('post.g'));//将传过来的字母转化为小写
            $letterL = strlen($letter);
            //处理’是不是1,2位的小写字母‘
            if(!ctype_lower($letter) && !in_array($letterL, array(1,2)) && $type != 2) {
                 $this->ajaxDie(0);
            } 
            
            //‘查询条件’
            $condition = array(
                'goods_sx'   => array('like', $letter.'%'),
                'alias_name' => ''
            );
            $list = M('SearchHot')->field('id,goods_name as gn,goods_sx as sx,length(goods_name) as len')
                ->where($condition)
                ->order('len ASC')
                ->select();
            //处理‘是获取2个字母还是药材列表’
            if($letterL == 1 && $type == 1) {
                $data = array();
                foreach ($list as $k => $v) {
                    $data[$k] = substr(strtoupper(preg_replace("/\s+/",'',$v['sx'])), 0, 2);
                }
                array_push($data, strtoupper($letter));
                $list = array_flip(array_flip($data));
                sort($list);
            }else{      
                $cates = $this->getC(1);
                foreach ($list as $k => $v) {
                    $gn = preg_replace("/\s+/",'', $v['gn']);
                    foreach ($cates as $k1 => $v1) {
                        foreach ($v1['_child'] as $k2 => $v2) {
                            if($v2['gn'] == $gn) {
                                $list[$k]['gid']   = $v2['id'];
                                $list[$k]['gn']    = $gn;
                                break 2;
                            }
                        }                   
                    }
                }
            }
            $this->ajaxDie(1,$list);
        }else{
            $this->ajaxDie(43); 
        }      
    }
    /****1.4新增结束 ****/
}
