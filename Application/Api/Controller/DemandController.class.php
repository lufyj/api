<?php
namespace Api\Controller;
use Think\Controller;
class DemandController extends BaseController {
    //需求接口
    public function demand(){
        //判断请求数据来自pc 还是手机端  pc 是 1  手机端是 0  广告机是2 
        if($this->client_type == 1){
            // $code = I('post.code');
            // $yzcode = session('yzcode');
            // if(!$yzcode){
            //     $this->ajaxDie(36);
            // }else{
            //     $yzcode_create_at = $yzcode['yzcode_create_at'];
                
            //     if($yzcode_create_at + 3600 < time()){
            //         $this->ajaxDie(37);
            //     }
            //     //验证验证码是否与session中保存的一样
            //     if ($code != $yzcode['code']) {
            //         $this->ajaxDie(38);
            //     }
            // }   
            $data['order_type'] = I('post.order_type',0,'intval');
            if(!$data['order_type']){$this->ajaxDie(29);}
            //分类id
            $data['cate_id'] = I('post.cate_id',0,'intval');
            if(!$data['cate_id']){$this->ajaxDie(31);}
            //分类名称
            $data['cate_name'] = clearXSS(I('post.cate_name'));
            if(!$data['cate_name']){$this->ajaxDie(32);}
            //商品id  
            $data['goods_id'] = I('post.goods_id',0,'intval');
            if(!$data['goods_id']){$this->ajaxDie(33);}
            //商品名称
            $data['goods_name'] = clearXSS(I('post.goods_name','',trim));
            if(!$data['goods_name']){$this->ajaxDie(34);}
            //规格id
            $data['goods_attr_id'] = clearXSS(I('post.goods_attr_id','',trim));
            //规格
            $data['goods_attr_name'] = clearXSS(I('post.goods_attr_name','',trim));
            //数量
            $data['num'] = I('post.num',0,'intval');
            if(!$data['num']){$this->ajaxDie(35);}
            //产地类型
            $data['origin_type'] = I('post.origin_type',0,'intval');
            if(!$data['origin_type']){$this->ajaxDie(36);}
            if($data['origin_type'] == 3){
                //产地地址code码
                $data['origin_code'] = clearXSS(I('post.origin_code','',trim));
                if(!$data['origin_code']){$this->ajaxDie(37);}
                //产地地址
                $data['origin_area'] = clearXSS(I('post.origin_area','',trim));
                if(!$data['origin_area']){$this->ajaxDie(38);}
            }
            //联系人
            $data['contacts'] = clearXSS(I('post.contacts','',trim));
            if(!$data['contacts']){$this->ajaxDie(39);}
            //手机号
            $mobile = clearXSS(I('post.mobile','',trim));
            $data['mobile'] = $mobile;
            //验证手机号是否有更改
            // $session_moblie = session('mobile');
            // if ($mobile != $session_moblie['code']) {
            //         $this->ajaxDie(99);
            // }
            //将传过来的手机号与数据库用户表里面的验证 如果手机号不存在则添加 存在则不添加  
            // $list = M('mobile_login')->where(['mobile' => $mobile])->find();
            // if(!$list){
            //     $message['realname'] = $data['contacts'];
            //     $message['create_time'] = time();
            //     $message['update_time'] = time();
            //     $uid = M('user')->add($message);
            //     $token = getToken($uid);
            //     更改用户token
            //     M('user')->where('id='. $uid)->setField(array('token' => $token));
            //     $map['mobile'] = $mobile;
            //     $map['uid'] = $uid;
            //     $uid = M('mobile_login')->add($map);
            //     $data['uid'] = $uid;
            // }else{
                    //$data['uid'] = $list['uid'];
            //}     
            $data['create_time'] = time();
            if(M('demand')->add($data)){
                session('yzcode', null);
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }             
        }else{
            $this->ajaxDie(0);
        }
    	
    }
    //全部需求列表接口
    //当请求时 返回最新的信息
    public function demand_list(){
        if($this->client_type == 1){
            //是否开启识别机器识别码  1是开启  0是关闭识别 
            $api_admachine = C('API_ADMACHINE');
            $maxId = I('post.maxId',0,'intval');
            if($maxId){
                $date['id'] = array('gt',$maxId);
            }
            $num =  I('post.num',0,'intval');
            if($api_admachine === '1'){
                $machine_code = clearXSS(I('post.machine_code'));
                 //如果广告机不传识别码 返回77 //如果广告机不传识别码 返回77 //如果广告机不传识别码 返回77
                if(!$machine_code){
                    $this->ajaxDie(77);
                }
                //获得该识别码所对应的 显示的求购信息
                $adMachine = M('adMachine')->where(['machine_code' => $machine_code])->field('cates,goods')->find();
                if(!$adMachine){
                    $this->ajaxDie(90);
                }
                $cates = $adMachine['cates'];
                $goods = $adMachine['goods'];
                if($cates != ''){
                    $cate_id = explode(',',$cates);
                    $data['cate_id'] = array('in',$cate_id );
                }
                if($goods != ''){
                    $goods_id = explode(',',$goods);
                    $data['goods_id'] = array('in',$goods_id );
                }  
                $data['_logic']  = 'or';
                $map['_complex']  = $data;
                $map['id'] = array('gt',$maxId); 
                $list = M('demand')->where($map)->order('id desc')->limit($num)->select();
            }else{
                $list = M('demand')->where($date)->order('id desc')->limit($num)->select();
            }
            foreach ($list as $key => $value) {
                if($value['price_type'] == 2){
                    $list[$key]['price']= '面议';
                }
                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
                }   
            }
            $this->ajaxDie(1,$list);
        }else{
              $this->ajaxDie(0);
        }
    }
    //搜索指定药材需求列表接口
    public function demand_lists(){
         if($this->client_type == 1){
            $name = clearXSS(I('post.name'));
            $minId = I('post.minId',0,'intval');
            $num =  I('post.num',0,'intval');
            $data['goods_name'] = $name;
            if($minId){
                $data['id'] = array('lt',$minId);
            }
            //返回请求药材的该药材的所有需求信息
            $list = M('demand')->where($data)->limit($num)->select();
            foreach ($list as $key => $value) {
                if($value['price_type'] == 2){
                    $list[$key]['price']= '面议';
                }
                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
                }   
            }
            //如果需求信息不存在 返回状态码 2 
            if($list == ''){
                $this->ajaxDie(2);
            }else{
                $this->ajaxDie(1,$list);
            }    
        }else{
              $this->ajaxDie(0);
        }
    } 
    //验证传过来的id是否是数据库最大ID
    public function demand_identify(){
        if($this->client_type == 1){
            $maxId = I('post.maxId',0,'intval');
            $api_admachine = C('API_ADMACHINE');
            if($maxId){
                $date['id'] = array('gt',$maxId);
            }
            if($api_admachine === '1'){
                $machine_code = clearXSS(I('post.machine_code'));
                if(!$machine_code){
                    $this->ajaxDie(77);
                }
                $adMachine = M('adMachine')->where(['machine_code' => $machine_code])->field('cates,goods')->find();
                $cates = $adMachine['cates'];
                $goods = $adMachine['goods'];
                if($cates != ''){
                    $cate_id = explode(',',$cates);
                    $data['cate_id'] = array('in',$cate_id );
                }
                if($goods != ''){
                    $goods_id = explode(',',$goods);
                    $data['goods_id'] = array('in',$goods_id );
                } 
                $data['_logic']  = 'or';
                $map['_complex']  = $data; 
                $map['id'] = array('gt',$maxId);        
                $list = M('demand')->where($map)->order('id desc')->find();
            }else{
                $list = M('demand')->where($date)->order('id desc')->find();
            }
            if($list['id'] > $maxId){
                $this->ajaxDie(1);
            }else{
                 $this->ajaxDie(2);
            }
        }else{
             $this->ajaxDie(0);
        }
    }
    //求购详情
    public function demand_details(){
         if($this->client_type == 1){
            $id = I('post.id',0,'intval');
            $result = M('demand')->where(['id' => $id])->find();
            if($result['price_type'] == 2){
                $result['price']= '面议';
            }
            if($result['origin_type'] == 1){
                $result['origin_area']= '较广';
            }else if($result['origin_type'] == 2){
                $result['origin_area']= '进口';
            } 
            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
         }
    }

    //广告机投标
     public function tender(){
        if($this->client_type == 1){
            $code = clearXSS(I('post.code'));
            $yzcode = session('yzcode');
            if(!$yzcode){
                $this->ajaxDie(36);
            }else{
                $yzcode_create_at = $yzcode['yzcode_create_at'];
                
                if($yzcode_create_at + 3600 < time()){
                    $this->ajaxDie(37);
                }
                //验证验证码是否与session中保存的一样
                if ($code != $yzcode['code']) {
                    $this->ajaxDie(38);
                }
            }   
            //首先判断价格，联系人，联系电话是否输入正确
            $data = array();
            $data['demand_id'] = I('post.demand_id', 0, 'intval');
            $data['price']    = clearXSS(I('post.price'));
            $mobile  = clearXSS(I('post.mobile'));
            $data['mobile'] = $mobile;
            //将传过来的手机号与数据库用户表里面的验证 如果手机号不存在则添加 存在则不添加  
            $info = M('user')->where(['mobile' => $mobile])->find();
            if($info){
                $data['uid'] = $info['id'];
                $data['contacts'] = $info['realname'];
            }else{
                $message['mobile'] = $mobile;
                $message['create_time'] = time();
                $data['uid'] = M('user')->add($message);
            }    
            if((int)$data['uid'] <= 0) $this->ajaxDie(30);                            
            if(!check_mobile($data['mobile'])) $this->ajaxDie(63);
            $tender = D('Tender');

            $res = $tender->addData($data); 
            if($res['code']>0){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
        }       
    }   
}
