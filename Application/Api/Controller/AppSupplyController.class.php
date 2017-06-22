<?php
namespace Api\Controller;
use Think\Controller;
class AppSupplyController extends BaseController {
    //供应接口
    public function supply(){
        //判断请求数据来自pc 还是手机端  pc 是 1  手机端是 0
        if($this->client_type == 1){

            $this->secureVerify();

            //接收数据
            $id                       = I('post.id',0,'intval'); //供应id
            $num                      = clearXSS(I('post.num','',trim));//求购数量
            $data['pic']              = clearXSS(I('post.pic','',trim));//图片 
            $data['uid']              = I('post.uid',0,'intval');// 用户id
            $data['mobile']           = clearXSS(I('post.mobile','',trim));//手机号
            $data['details']          = clearXSS(I('post.details','',trim));//详情
            $data['cate_id']          = I('post.cate_id',0,'intval'); //分类id
            $data['contacts']         = clearXSS(I('post.contacts','',trim)); //联系人
            $data['goods_id']         = I('post.goods_id',0,'intval');//商品id
            $data['cate_name']        = clearXSS(I('post.cate_name'));//分类名称
            $data['price_type']       = clearXSS(I('post.price_type','',trim));//价格类型
            $data['goods_name']       = clearXSS(I('post.goods_name','',trim)); //商品名称
            $data['origin_type']      = I('post.origin_type',0,'intval'); //产地类型
            $data['create_time']      = time();
            $data['supply_area']      = clearXSS(I('post.supply_area','',trim));
            $data['supply_code']      = clearXSS(I('post.supply_code','',trim));//货源地code码
            $data['goods_attr_id']    = clearXSS(I('post.goods_attr_id','',trim)); //规格id
            $data['supply_details']   = clearXSS(I('post.supply_details','',trim)); //货源地详细地址
            $data['goods_attr_name']  = clearXSS(I('post.goods_attr_name','',trim));//规格
            
            //校验参数
            $data['uid']                  || $this->ajaxDie(30);
            $data['cate_id']              || $this->ajaxDie(31);
            $data['cate_name']            || $this->ajaxDie(32);
            $data['goods_id']             || $this->ajaxDie(33);
            $data['goods_name']           || $this->ajaxDie(34);
            $data['origin_type']          || $this->ajaxDie(36);
            $data['contacts']             || $this->ajaxDie(39);
            $data['details']              || $this->ajaxDie(50);
            $data['mobile']               || $this->ajaxDie(51);
            check_mobile($data['mobile']) || $this->ajaxDie(51); 
            $data['supply_code']          || $this->ajaxDie(59);
            $data['goods_attr_id']        || $this->ajaxDie(127);
            $data['goods_attr_name']      || $this->ajaxDie(127);
          
             
            // //判断用户是否是企业用户,如果不是企业用户不允许他发布毒麻类药材
            // $status = M('company_confirm')->where(['user_id' =>  $data['uid']])->getfield('confirm_status');
            // if($status != 3 && $data['cate_id'] == '18'){
            //     $this->ajaxDie(126);
            // }
            //判断数量
            if($num == '大货'){
                $data['num'] = '-1';
            }else{
                $data['num']                  =  I('post.num',0,'intval');
                $data['num']                  || $this->ajaxDie(35);
                if($data['num'] > 10000000 ) {
                    $this->ajaxDie(91);
                }
            }
            //判断价格
            if($data['price_type'] == 1){
                $data['price']     =   clearXSS(I('post.price','',trim));
                $data['price']     ||  $this->ajaxDie(58);
            }

            //判断价格和图片的权重
            $data['weight'] = $this->_setweight($data['price'],$data['pic'],$data['uid']);
            
           //判断产地
            if($data['origin_type'] == 3){
                $data['origin_code']   =  clearXSS(I('post.origin_code','',trim));
                $data['origin_code']   || $this->ajaxDie(37);
                //产地地址
                $data['origin_area']   =  clearXSS(I('post.origin_area','',trim));
                $data['origin_area']   || $this->ajaxDie(38);
            }
           
           if($id){//id存在则代表是修改
                //修改时 判断图片有没有修改
                $pic = M('supply')->where(['id' => $id])->getField('pic');
                if($pic &&  $pic != $data['pic']){
                   $this->diffPic($pic,$data['pic']);
                }

                if(M('supply')->where(['id' => $id])->save($data)){
                    $this->ajaxDie(1);
                }else{
                    $this->ajaxDie(0);  
                }
           }else{ //添加数据
                if(M('supply')->add($data)){
                    $this->ajaxDie(1);
                }else{
                    $this->ajaxDie(0);
                }
           }  
        }else{
                $this->ajaxDie(43);
        }
    }
    //搜索指定药材供应接口
    public function supply_lists(){
        //返回请求药材的该药材的所有供应信息
        if($this->client_type == 1){

            //接收参数
            $num                =  I('post.num'  ,10,'intval');
            $page               =  I('post.page',0,'intval');
            $minId              =  I('post.minId',0 ,'intval');
            $goods_name         =  clearXSS(I('post.goods_name'));
            $data['status']     =  array('not in','-1');
            $data['goods_name'] =  $goods_name;

            //校验参数
            $goods_name  || $this->ajaxDie(52);

            //支持两种分页方式(老版本使用的保留,新版本使用limit分页(老版本存在bug))
            if($page){
                //计算条数
                $total   =  ($page-1) * $num;
                $list    =  M('supply')->where($data)->order('weight desc,id desc')->limit($total,$num)->select();
            }else{
                $minId   && $data['id'] = array('lt',$minId);
                $list    =  M('supply')->where($data)->order('weight desc,id desc')->limit($num)->select();
            }
            
            foreach ($list as $key => $value) {
                //判断供应信息是否是企业用户
                $company_auth_status     = M('user')->where(['id' => $value['uid']])->getfield('company_auth_status');
                $confirm_status          = M('company_confirm')->where(['user_id' => $value['uid']])->getfield('confirm_status');
                if($company_auth_status  == '2' && $confirm_status == '3'){
                    $list[$key]['auth']  = '1';
                }else{
                    $list[$key]['auth']  = '0';
                }
                if($value['price_type']  == 2){
                    $list[$key]['price'] = '面议';
                }
                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
                }   
            }
            if(!$list){
                $this->ajaxDie(0);
             }else{
                $this->ajaxDie(1,$list);
             }
           
        }else{
              $this->ajaxDie(43);
        }
    }
    //供应详情
    public function supply_details(){
         if($this->client_type == 1){

            //接收参数
            $id  =   I('post.id',0,'intval');

            //校验参数
            $id  ||  $this->ajaxDie(53);

            $result = M('supply')->where(['id' => $id])->find();

            //取出原图
            $pic = explode(",",$result['pic']);
            $result['pic'] = $pic;
            if($result['price_type'] == 2){
                $result['price']= '面议';
            }
            if($result['origin_type'] == 1){
                $result['origin_area']= '较广';
            }else if($result['origin_type'] == 2){
                $result['origin_area']= '进口';
            }
            if($result['num'] == '-1'){
                $result['num'] ='大货';
            }
            //判断供应信息是否是企业用户
            $company_auth_status  = M('user')->where(['id' => $result['uid']])->getfield('company_auth_status');
            $confirm_status       = M('company_confirm')->where(['user_id' => $result['uid']])->getfield('confirm_status');
            if($company_auth_status == '2' && $confirm_status == '3'){
                $result['auth'] = '1';
            }else{
                $result['auth'] = '0';
            } 

			$result['details'] || $result['details'] = '';
            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
         }else{
            $this->ajaxDie(43);
         }
    } 
    //供应图片上传
    public function upload_pic(){
        if($this->client_type == 1){    
            $upload= new \Org\Com\ImageUpload();
            //设置信息
            $upload->maxSize=314158691111;//大小
            $upload->exts=array("jpeg","jpg","png","gif");//类别
            $upload->rootPath="./Uploads/Picture/Supply/";//路径
            $upload->subName =array('date', 'Ymd');
            $upload->saveExt='jpg';
            //执行上传
            $info=$upload->upload();                                                                             
            if(!$info){
                $this->ajaxDie(41);
            }else{
                foreach($info as $file){
                    //实例化图像处理类
                    $pic=new\Think\Image();
                    //打开文件
                    $pic->open("./Uploads/Picture/Supply/".$file['savepath'].$file['savename']);
                    //起名
                    $s=$file['savepath']."thumb_200x200_".$file['savename'];
                    $a=$file['savepath'].$file['savename'];
                    //缩放
                    $pic->thumb(200,200)->save("./Uploads/Picture/Supply/".$s);
                    $path = '/Uploads/Picture/Supply/'.$s;
                    $shuzu[]=$path;
                   }
                    
                }
               $this->ajaxDie(1,$shuzu);
               
        }else{
            $this->ajaxDie(43);
        }
    }
    //指定药材的属性 图片 是否关注的状态
    public function goods_attributes(){
    	if($this->client_type == 1){   

            $this->secureVerify();

            //接收参数
	    	$uid      = I('post.uid',0,'intval');
	    	$goods_id = I('post.goods_id',0,'intval');

            //校验参数
            $goods_id || $this->ajaxDie(60);

            $info = M('goods')->where(['id' => $goods_id])->field('goods_img,description')->find();
            $info['description'] = html_entity_decode($info['description']);
            $info['description'] = preg_replace("/<([\/a-zA-Z]+)[^>]*>/","",$info['description']);
            $info['description'] = preg_replace(array("/&nbsp;/","/\t/","/(\r\n)+|\n+/"), array("","","\n",),$info['description']);
            // $info['description'] = preg_replace("/\n{2,}/", "\n", $info['description']);//将多个!替换成1个@

            //判断该用户是否存在
            if($uid){
                $info['follow_state'] = '0' ;
                if(M('user')->where(['id' => $uid])->find()){}else{$this->ajaxDie(45,$info);};
            }
            if($info){
                $result = M('follow')->where(['goods_id' =>$goods_id,'uid' => $uid])->find();
                if($result){
                    $info['follow_state'] = '1' ;
                }else{
                    $info['follow_state'] = '0' ;
                }
                $this->ajaxDie(1,$info);
            }else{
                $this->ajaxDie(0);
            }
    	 	
    	 }else{
    	 		$this->ajaxDie(43);
    	}
    }

    //给手机站返回地址信息(云鹏用)
    public function address(){
        if($this->client_type == 1){
            //接收参数
            $code = I('post.code',0,'intval');

            if(!$code){
                $list = M('region')->find('REGION_NAME,REGION_CODE')->where(['PARENT_ID' => '1'])->select();
            }else{
                $list = M('region')->find('REGION_NAME,REGION_CODE')->where(['PARENT_ID' => $code])->select();
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
    /*设置权重值 规则： 价格 50，图片 30 企业 18*/
    private function _setweight($price=0,$pic='',$uid=0){
    	$weight=0;
    	if($price>0){
    		$weight=$weight+50;
    	}
    
    	if($pic){
    		$weight=$weight+30;
    	}
    
    	if($uid>0){
    		$where=[
    		'uid'=>$uid,
    		'status'=>3
    		];
    		$count=M('companyAuthen')->where($where)->count();
    		if($count){
    			$weight=$weight+18;
    		}
    	}
    
    	return $weight;
    }
    //供应编辑时,将用户删除的图片从服务器中删除
    private function diffPic($o_pic,$n_pic){//删除成功与否,不影响用户提交
        //将两个字符串转化成数组
        $n_pic = explode(',',$n_pic);
        $o_pic = explode(',',$o_pic);

        $diff = array_diff($o_pic, $p_pic);
        if($diff){
             foreach ($diff as $k => $v) {
                $v   = substr($v,1);//删除图片路径中的/
                //删除原图 thumb_200x200_ 是固定的    如果想截取的话 图片路径长度固定  可以直接    substr($v,32,14) 截取
                $big = str_replace('thumb_200x200_','',$v);
                unlink($v);//删除缩略图
                unlink($big);//删除原图
            }
        }
        return ture; 
    }

    /*分割线  1.4版本新增*/
    /*
     *获取供应+直销推荐数据   1.4版本开始
     */
    public function getSupplyData(){
        if($this->client_type == 1){
            $list = D('supply')->getSupplyData();
            if($list !== false){
              $this->ajaxDie(1,$list);  
          }else{
            $this->ajaxDie(0);
          }
        }else{
           $this->ajaxDie(43); 
        }
    }
    //获取发布供应时的配置  1.4版本
    public function getSupplyConf(){
        if($this->client_type == 1){
            $type = I('post.t',0,'intval');
            $data = D('supply')->getSupConf($type);
            if($data !== false){
               $this->ajaxReturn(array('code' => '1','msg' =>'获取成功','data' => $data));  
          }else{
                $this->ajaxReturn(array('code' => '0','msg' =>'获取失败'));
          }
        }else{
           $this->ajaxReturn(array('code' => '0','msg' =>'参数无效')); 
        }
    }

    /* 发布供应  1.4版本*/ 
    public function publish() {
        if($this->client_type == 1){
            $this->secureVerify();
            $post = I('post.');
            $post = array_map('clearXSS',$post);

            //参数校验
            if(!intval($post['cid']) || !$post['cn']){//分类id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品分类'));
            }

            if(!intval($post['gid']) || !$post['gn']){//药材id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品'));
            }

            if(!intval($post['aid']) || !$post['an']){//规格id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品规格'));
            }

            if(intval($post['num']) > 0){//供应数量 数量小于等于0时为面议
                if (intval($post['num']) < 0 || intval($post['num']) >= 10000000) {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请填写正确的供应数量'));
                }
            }else{
                $post['num'] = '-1';
            }

            if ((float)$post['price'] < 0 || (float)$post['price'] > 1000000 || (float)$post['mprice'] < 0 || (float)$post['mprice'] > 1000000) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的价格'));
            }elseif((float)$post['price'] == 0 && (float)$post['mprice'] == 0){//价格和最大价格都为0时 保存价格为面议
                $post['price']      = 0;
                $post['mprice']     = 0;
                $post['price_type'] = 2;
            }elseif((float)$post['price'] == 0 && (float)$post['mprice'] > 0){//价格和最大价格 只有一个有值时 将值保存进price中
                $post['price']      = (float)$post['mprice'];
                $post['mprice']     = 0;
                $post['price_type'] = 1;
            }elseif((float)$post['price'] > 0 && (float)$post['mprice'] == 0){//价格和最大价格 只有一个有值时 将值保存进price中
                $post['price']      = (float)$post['price'];
                $post['mprice']     = 0;
                $post['price_type'] = 1;
            }elseif((float)$post['price'] > 0 && (float)$post['mprice'] > 0){//价格和最大价格都有值时,进入下面判断
                if((float)$post['price'] < (float)$post['mprice']){//价格小于最大价格时,正常保存数据
                    $post['price']      = (float)$post['price'];
                    $post['mprice']     = (float)$post['mprice'];
                    $post['price_type'] = 1;
                }elseif((float)$post['price'] > (float)$post['mprice']){//价格大于最大价格时,当错误数据处理,保存为面议
                    $post['price']      = 0;
                    $post['mprice']     = 0;
                    $post['price_type'] = 2;
                }elseif((float)$post['price'] == (float)$post['mprice']){//价格等于最大价格时,只保存一个值进price
                    $post['price']      = (float)$post['price'];
                    $post['mprice']     = 0;
                    $post['price_type'] = 1;
                }
            }else{
                $post['price']      = 0;
                $post['mprice']     = 0;
                $post['price_type'] = 2;
            }

            if(!$post['o_type']){//产地类型
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择产地类型'));
            }else{
                if(intval($post['origin_type']) == 3){//产地类型为3时 代表选择了省市县 则必须有产地code值
                    if(!$post['o_code'] || !$post['o_area']){
                        $this->ajaxReturn(array('code' => 0, 'msg' => '请选择产地'));
                    }
                }
            }

            $Days = C('VALID_DAYS2');//供应有效期
            if(!$Days[(int)$post['days']]){
                $this->ajaxReturn(array('code' => 0, 'msg' => '选择正确的有效期限'));
            }
            $post['valid_days']  = isset($Days[(int)$post['days']]) ? $Days[(int)$post['days']] : 0;
            $post['remain_days'] = $post['valid_days'] > 0 ? $post['valid_days'] : 0;

            if(!$post['s_code'] || !$post['s_area']){//库存地
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择库存地'));
            }

            if(!in_array((int)$post['pjid'], [0, 1, 2, 3])) {//药材票据
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择票据'));
            }else{
                $tickets = [
                    '0' => '不提供票据',
                    '1' => '发票',
                    '2' => '收购手续',
                    '3' => '发票或收购手续'
                ];
                $post['ticket'] = $tickets[(int)$post['pjid']];
            }

            if(!in_array((int)$post['zlid'], [0, 1, 2, 3, 4, 5])){//药材质量
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药材标准'));
            }else{
                $standards = [
                    '0' => '待确定',
                    '1' => '达到出口标准和药典标准',
                    '2' => '达到出口标准',
                    '3' => '达到省标',
                    '4' => '达到2010版药典标准',
                    '5' => '达到2015版药典标准'
                ];
                $post['standard'] = $standards[(int)$post['zlid']];
            }

            if(!$post['contacts']){//联系人
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入联系人'));
            }

            if(!check_mobile($post['mobile'])){//电话
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的手机号'));
            }
            if(mb_strlen($post['contacts'], 'utf-8') > 20){//联系人
                $this->ajaxReturn(array('code' => 0, 'msg' => '联系人字数不能超过20个'));
            }

            //qq号不用必填
            if (!ctype_digit($post['qq']) || mb_strlen($post['qq'],'utf-8') > 15) {
                $post['qq'] = '';
            }

            $res = D('Supply')->savePublish($post);
            if ((int)$res > 0) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '发布成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '发布失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数无效'));
        }
    }
     //供应列表接口
    public function Supply_list(){
        //返回请求药材的该药材的所有供应信息
        if($this->client_type == 1){
            //接收参数
            $num            =  10;
            $page           =  I('post.page',1,'intval');
            $key            =  clearXSS(I('post.key'));//1 代表筛选条件 2搜索框搜索 
            $goods_name     =  clearXSS(I('post.gn','','trim'));//品种
            $origin_code    =  clearXSS(I('post.o_code','','trim'));//产地code码
            $supply_code     =  clearXSS(I('post.s_code','','trim'));//库存地code码
            //1待确定 2 达到出口标准和药典标准 3达到出口标准 4达到省标 5达到2010版药典标准 6达到2015版药典标准
            $standard_id    =  I('post.sid',0,'intval');//质量 
            //1：不提供票据，主要用户查询，2：发票，3：收购手续，4：发票或收购手续
            $ticket_id      =  I('post.tid',0,'intval');//票据
            $price_type     =  clearXSS(I('post.p_type',0,'intval'));//价格  1 代表有价格 2 代表面议
            
            //组装条件
            //where条件
            $data['status']      =  1;

            if($key){
                $data['goods_name']  = array('like',$key.'%');
            }
            if($goods_name){
                $data['goods_name']  = $goods_name;
            }

            $origin_code    && $data['origin_code'] = array('like',$origin_code.'%');
            $supply_code    && $data['supply_code']  = array('like',$supply_code.'%');
            if($standard_id){
                if(!in_array((int)$standard_id, [ 1, 2, 3, 4, 5,6])){//药材质量
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药材标准'));
                }
                $data['standard_id'] = $standard_id -1; 
            }
            if($ticket_id){
                if(!in_array((int)$ticket_id, [1, 2, 3,4])) {//药材票据
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请选择票据'));
                }
                $data['ticket_id']   = $ticket_id-1;
            } 
            if($price_type == '1'){
                $data['price_type'] = '1';
            }else if($price_type =='2'){
                $data['price_type'] = '2';
            }
            //将过期时间过滤掉
            $data['_string'] = '!(remain_days=0 && valid_days!=0)';

            //计算条数
            $total =($page-1) * $num;
            $list  = M('supply')->field('id,uid,goods_name as gn,goods_attr_name as an,origin_type as o_type,price_type,origin_area as o_area,supply_area as s_area,price,maxprice,pic as img,standard')
                    ->where($data)
                    ->order('weight desc,id desc')
                    ->limit($total,$num)
                    ->select();

            foreach ($list as $k => $v) {

                if($v['o_type'] == 1){
                    $list[$k]['o_area'] ='较广';
                }else if($v['o_type'] == 2){
                    $list[$k]['o_area'] ='进口';
                }

                if($v['price'] != '0' && $v['maxprice'] != '0'){
                    $list[$k]['price']  = $v['price'].'~'.$v['maxprice'].'元/公斤';
                }else{
                    $list[$k]['price']  = $v['price'].'元/公斤';
                }

                if($v['price_type']  == 2){
                    $list[$k]['price'] = '面议';
                }

                

                if($v['img']){
                    $img  = explode(',',$v['img']);
                    $list[$k]['img'] = $img[0];
                }else{
                    $list[$k]['img']  = '/Uploads/Picture/Avatar/20170622/594b7dee39413.jpg';
                }
                
                //是否是企业认证
                $where['uid']    = $v['uid'];
                $where['status'] = '1';
                $companyAuthen = M('companyAuthen')->where($where)->find();
                if($companyAuthen){
                    $list[$k]['company_status'] = '1';
                }else{
                    $list[$k]['company_status'] = '0';
                }
            }
            if($list){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $list));
             }else{
                if(intval($page) > 1){
                    $this->ajaxReturn(array('code' => 0, 'msg' => '数据加载完毕'));
                }else{
                    $this->ajaxReturn(array('code' => 0, 'msg' => '暂无数据'));
                }
          
             }
           
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
    /************1.4版本开始结束********/
}
