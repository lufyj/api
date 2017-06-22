<?php
namespace Api\Controller;
use Think\Controller;
class AppDemandController extends BaseController {
    //求购接口
    public function demand(){
        //判断请求数据来自pc 还是手机端  pc 是 1  手机端是 0  广告机是2 
        if($this->client_type == 1){

            $this->secureVerify();
            $pay_type = I('post.pay_type',0,'intval'); //先判断是否缴纳保证金
            if($pay_type == '2'){//$pay_type = 2的时候 代表支付宝支付  暂时不写

            }else{
                $data['order_number'] = get_order_sn();
                //$pay_type = 1的时候 代表转账支付
                if($pay_type == '1'){
                    $data['deposit'] = -1;
                }
            }

            //接收数据
            $mobile                      =   clearXSS(I('post.mobile','','trim'));//手机号
            $data['uid']                 =   I('post.uid',0,'intval');//用户id
            $data['num']                 =   I('post.num',0,'intval');//数量
            $data['mobile']              =   $mobile;
            $data['cate_id']             =   I('post.cate_id',0,'intval');//分类id
            $data['details']             =   clearXSS(I('post.details','','trim'));//求购详情
            $data['goods_id']            =   I('post.goods_id',0,'intval');//商品id
            $data['contacts']            =   clearXSS(I('post.contacts','','trim'));//联系人
            $data['cate_name']           =   clearXSS(I('post.cate_name'));//分类名称
            $data['goods_name']          =   clearXSS(I('post.goods_name','','trim'));//商品名称
            $data['order_type']          =   I('post.order_type',0,'intval');//订单来源
            $data['create_time']         =   time();//时间戳
            $data['mobile_show']         =   I('post.mobile_show',0,'intval');//手机号是否显示
            $data['origin_type']         =   I('post.origin_type',0,'intval');//产地类型
            $data['goods_attr_id']       =   clearXSS(I('post.goods_attr_id','','trim'));//规格id
            $data['goods_attr_name']     =   clearXSS(I('post.goods_attr_name','','trim'));//规格 

            
            //数据校验
            $data['uid']                  ||   $this->ajaxDie(30);
            $data['num']                  ||   $this->ajaxDie(35);
            $data['mobile']               ||   $this->ajaxDie(51);
            $data['cate_id']              ||   $this->ajaxDie(31);
            $data['details']              ||   $this->ajaxDie(50);
            $data['goods_id']             ||   $this->ajaxDie(33);
            $data['contacts']             ||   $this->ajaxDie(39);
            $data['cate_name']            ||   $this->ajaxDie(32);
            $data['goods_name']           ||   $this->ajaxDie(34);
            $data['order_type']           ||   $this->ajaxDie(29);
            $data['origin_type']          ||   $this->ajaxDie(36);
            if($data['num'] > 10000000 )       $this->ajaxDie(35);
            check_mobile($data['mobile']) ||   $this->ajaxDie(51);
                 
            // //判断用户是否是企业用户,如果不是企业用户不允许他发布毒麻类药材
            // $status = M('company_confirm')->where(['user_id' =>  $data['uid']])->getfield('confirm_status');
            // if($status != '3' && $data['cate_id'] == '18'){
            //     $this->ajaxDie(126);
            // }
            //判断价格和图片的权重
            $data['weight'] = $this->_dsetweight($data['uid']);
            
            if($data['origin_type'] == '3'){
                //产地地址code码
                $data['origin_code'] = clearXSS(I('post.origin_code','0','trim'));
                $data['origin_code'] || $this->ajaxDie(37);
                //产地地址
                $data['origin_area'] = clearXSS(I('post.origin_area','','trim'));
                $data['origin_area'] || $this->ajaxDie(38);
            } 

            if(M('demand')->add($data)){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }             
        }else{
            $this->ajaxDie(43);
        }
    	
    }
    //搜索指定药材求购列表接口
    public function demand_lists(){
         if($this->client_type == 1){
            //接收数据
            $num                =   I('post.num'  , 10 ,'intval');
            $page               =   I('post.page' , 0 ,'intval');
            $minId              =   I('post.minId', 0  ,'intval');
            $goods_name         =   clearXSS(I('post.goods_name'));
            $data['status']     =   array('not in','-1');
            $data['goods_name'] =   $goods_name;
          

            //数据校验
            $minId              &&  $data['id'] = array('lt',$minId);
            $goods_name         ||  $this->ajaxDie(52);
            
            //支持两种分页方式(老版本使用的保留,新版本使用limit分页(老版本存在bug))
            if($page){
            	//计算条数
            	$total   =  ($page-1) * $num;
            	$list    =  M('demand')->where($data)->order('weight desc,id desc')->limit($total,$num)->select();
            }else{
            	$minId   && $data['id'] = array('lt',$minId);
            	$list  = M('demand')->where($data)->order('id desc')->limit($num)->select();
            }

            foreach ($list as $key => $value) {
                //判断求购信息是否是企业用户
                $company_auth_status  = M('user')->where(['id' => $value['uid']])->getfield('company_auth_status');
                $confirm_status       = M('company_confirm')->where(['user_id' => $value['uid']])->getfield('confirm_status');
                if($company_auth_status == '2' && $confirm_status == '3'){
                    $list[$key]['auth'] = '1';
                }else{
                    $list[$key]['auth'] = '0';
                }

              
                $list[$key]['mobile'] = maskPhone($value['mobile']);
                

                //返回处理后的数据
                $result['price_type']   ==  2 &&  $result['price']= '面议';

                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
                }   
            }
            //如果需求信息不存在 返回状态码 2 
            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }    
        }else{
              $this->ajaxDie(43);
        }
    } 
    //求购详情
    public function demand_details(){
         if($this->client_type == 1){

            $this->secureVerify();

            //接收数据
            $id   =   I('post.id' , 0 ,'intval');
            $uid  =   I('post.uid', 0 ,'intval');

            //校验数据
            $id   ||  $this->ajaxDie(53);
            $uid  ||  $this->ajaxDie(30);
        
            $result = M('demand')->where(['id' => $id])->find();
            //判断供应信息是否是企业用户
            $company_auth_status  =  M('user')            -> where(['id'      => $result['uid']])->getfield('company_auth_status');
            $confirm_status       =  M('company_confirm') -> where(['user_id' => $result['uid']])->getfield('confirm_status');
           
           
            if($company_auth_status == '2' && $confirm_status == '3'){
                $result['auth'] = '1';//1代表是企业用户
            }else{
                $result['auth'] = '0';//0 代表不是企业用户
            } 

            $result['price_type']   ==  2 &&  $result['price']= '面议';

            if($result['mobile_show'] == '0'){
                $result['mobile'] = maskPhone($result['mobile']);
            }
            if($result['origin_type'] == 1){
                $result['origin_area']= '较广';
            }else if($result['origin_type'] == 2){
                $result['origin_area']= '进口';
            } 

			$info = M('tender')->where(['uid' => $uid,'demand_id' => $id])->find();
			$result['details'] || $result['details'] = '';
            if($result){
                if($info){
                    $result['tender_status'] = '1';
                    if($result['mobile_show'] == '0'){
                        $result['mobile'] = maskPhone($result['mobile']);
                    }
                }else{
                    $result['tender_status'] = '2';
                    if($uid == $result['uid']){
                        $result['tender_status'] = '0';
                    }
                    $result['mobile'] = maskPhone($result['mobile']);
                }
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
         }else{
            $this->ajaxDie(43);
         }
    }
    /* 投标 */
    public function tender(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收数据
            $data = array();
            $data['uid']        =   I('post.uid',0, 'intval');
            $data['imgs']       =   clearXSS(I('post.imgs'));
            $data['price']      =   clearXSS(I('post.price'));
            $data['mobile']     =   clearXSS(I('post.mobile'));
            $data['remarks']    =   clearXSS(I('post.remarks'));
            $data['contacts']   =   clearXSS(I('post.contacts'));
            $data['demand_id']  =   I('post.demand_id', 0, 'intval');

            //数据校验
            $data['uid']                    ||  $this->ajaxDie(30);       
            $data['price']                  ||  $this->ajaxDie(111); 
            $data['contacts']               ||  $this->ajaxDie(62); 
            check_mobile($data['mobile'])   ||  $this->ajaxDie(63);               

            $tender = D('tender');
            $res    = $tender->addData($data); 
            if($res['code']>0){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
        }       
    }   
    /* 处理流程状态信息 */
    public function doDemand(){
        if($this->client_type == 1){

            $this->secureVerify();
            
            $id    = I('post.id' ,  0,  'intval');        
            $inx   = I('post.inx',  0,  'intval');
            $uid   = I('post.uid',  0,  'intval');

            $data  =   array( 'id' => $id, 'uid' => $uid );
            $id    ||  $this->ajaxDie(0);
            $uid   ||  $this->ajaxDie(30);
            
            switch ($inx){
                case 1://处理支付托管资金
                    $status = M('demand')->where(['id' => $id])->getField('trading_type');
                    if($status != '0'){
                        $this->ajaxDie(91);
                    }
                    
                    $type = I('post.type');
                    if(!in_array($type, [0,1])){
                        $this->ajaxDie(0);
                    }
                    $data['type'] = $type;
                    break;
                case 2://处理发货
                    $tender_id = I('post.tid', 0, 'intval');
                    if($tender_id <= 0) $this->ajaxDie(0);
                    $data['tender_id'] = $tender_id;            
                    break;
                case 3://处理签收
                    
                    break;
            }
            $model = D('Demand');
            $code  = $model->updataField($data, $inx);
            if($code == '1'){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
            }   
    }
    //投标图片上传
    public function upload_pic(){
        if($this->client_type == 1){    
            $upload=new \Org\Com\ImageUpload();
            //设置信息
            $upload->maxSize=314158691111;//大小
            $upload->exts=array("jpeg","jpg","png","gif");//类别
            $upload->rootPath="./Uploads/Picture/Tender/";//路径
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
                    $pic->open("./Uploads/Picture/Tender/".$file['savepath'].$file['savename']);
                    //起名
                    $s=$file['savepath']."thumb_200x200_".$file['savename'];
                    $a=$file['savepath'].$file['savename'];
                    //缩放
                    $pic->thumb(200,200)->save("./Uploads/Picture/Tender/".$s);
                    $path = '/Uploads/Picture/Tender/'.$s;
                    $shuzu[]=$path;
                   }
                    
                }
               $this->ajaxDie(1,$shuzu);
               
        }else{
            $this->ajaxDie(43);
        }
    }
    /*设置权重值 规则：企业 18*/
    private function _dsetweight($uid=0){
    	$weight=0;
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
}
