<?php
namespace Api\Controller;
use Think\Controller;
class AppUserController extends BaseController {

    //个人中心修改接口
    //用户信息展示
    public function user_details(){

        //判断请求数据 Ios是 2  Android是 1
        if($this->client_type==1 || $this->client_type==2){

            $this->secureVerify();

            //接收参数
            $uid     =  I('post.uid',0,'intval');//用户id
            $time    =  date('ymd');
            $version =  I('post.version_num',0,'intval');//给安卓返回版本号 (安卓会传过来一个1的标识)
            
            //校验参数
            $uid    ||  $this->ajaxDie(30);

            $result = M('user')->field('id,realname,token,address,head_pic,points,register_from,status,company_auth_status,weixin_status')->where(['id' => $uid,'status' => 1 ])->find();

            //给安卓返回版本号
            if($version == '1'){
                $result['version_num'] = M('version')->order('id desc')->getField('version_num');
            }

            //手机号
            $result['mobile']  =  M('mobile_login')->where(['uid' => $uid])->getField('mobile');
            $result['mobile'] ||  $result['mobile'] = '';

            //关注数量
            $result['follow_num'] =   M('follow')->where(['uid' => $uid])->count();
            $result['follow_num'] ||  $result['follow_num'] = '0';
         
            //账户余额
            $result['balance']    =   M('user_balance')->where(['uid' => $uid])->getField('balance');
            $result['balance']    ||  $result['balance'] = '0.00';
           
            //获取未读推送消息数量
            $pushNum=D('Push')->pushInfoCount($result['id']);
            $pushDemandInfoNum=D('PushDemandinfo')->pushNoReadCount($result['id']);
            $notRead_num = $pushNum+$pushDemandInfoNum;
            $result['notRead_num'] =  strval($notRead_num);

            if(M('sign')->where(['uid' => $uid,'time' =>$time])->find()){
                $result['sign_status'] = '1';
            }else{
                $result['sign_status'] = '0';
            }
            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }  
        }else{
        		$this->ajaxDie(43);
        }
    } 
    //修改头像
    public function  user_herd(){
        if($this->client_type == 1){

            $this->secureVerify();

            //配置参数
            $img_config             = C('PICTURE_UPLOAD');
            $img_config['only']     = array(200,200);
            $img_config['savePath'] = 'Avatar/';
            
            //图片上传
            $res = uploadImage('head_pic', $img_config, NULL);

            if($res['code'] != 1){
                $this->ajaxDie(41);
            }
            $uid = I('post.uid',0,'intval');
            //用户id存在的话 就执行修改
            $data['head_pic'] = $res['images'][0];
            if($uid && $data['head_pic']){
                //执行修改操作 成功 返回1 失败返回0
                if(M('user')->where(['id' => $uid])->save($data)){
                    $this->ajaxDie(1,$data['head_pic']);
                }else{
                    $this->ajaxDie(0);
                }
            }else{
                $this->ajaxDie(30);
            }
        }else{
            $this->ajaxDie(43);
        }     
    }
    //修改用户昵称
    public function user_nickname(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid              = I('post.uid',0,'intval');
            $data['realname'] = clearXSS(I('post.realname','',trim));

            //校验参数
            $uid   || $this->ajaxDie(30);
            // 计算传过来的用户名长度
            $len = mb_strlen($data['realname'],'utf8');
            if($len < 2 || $len >10){
                $this->ajaxDie(10);
            }

            //修改昵称
            if( M('user')->where(['id' => $uid])->save($data)){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
         }else{
            $this->ajaxDie(43);
         }
    }
    //修改用户密码
    public function user_password(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid          = I('post.uid',0,'intval');
            $old_password = clearXSS(I('post.old_password','',trim)); //旧密码
            $new_password = clearXSS(I('post.new_password','',trim)); //新密码

            //校验参数
            $uid          ||  $this->ajaxDie(30);
            $old_password ||  $this->ajaxDie(56);
            $new_password ||  $this->ajaxDie(57);
            //判断旧密码与新密码是否一致  一致返回4
            if($old_password == $new_password){$this->ajaxDie(4);}
            $data['password'] = password_md5($new_password);
            //验证该用户传过来的密码 与数据库密码是否匹配
            $info = M('mobileLogin')->where(['uid' => $uid])->find();
            $md5_pwd = password_md5($old_password);

            //验证通过 修改密码
            if($md5_pwd == $info['password']){
                if(M('mobileLogin')->where(['uid' => $uid])->save($data)){
                    $this->ajaxDie(1);
                }else{
                    $this->ajaxDie(0);
                }
            }else{
                //密码错误 返回42
                $this->ajaxDie(42);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    //我的求购列表
    public function user_demand(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid     =  I('post.uid',  0 ,'intval');
            $num     =  I('post.num',  10, 'intval');
            $minId   =  I('post.minId',0 , 'intval');
            $status  =  I('post.status',0, 'intval');

            //校验参数
            $uid     || $this->ajaxDie(30);
            $minId   && $data['id'] = array('lt',$minId);
           
            //组装条件
            $data['uid']               = $uid;
            if($status == '1'){
                 $data['status']       = array('in','0,1,2,3');
                 $data['trading_type'] = array('not in','2');
            }else if($status == '2'){
                $where['status']       = '4';
                $where['trading_type'] = '2';
                $where['_logic']       = 'or';
                $data['_complex']      = $where;                                                               
            }else{
                $data['status'] = array('neq',-1);
            }

            $list = M('demand')->where($data)->order('id desc')->limit($num)->select();
            foreach($list as $k => $v){
                $list[$k]['price'] = M('tender')->where(['demand_id' => $v['id'],'status' => '1'])->getField('price');
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
    //指定我的求购详细信息
    public function demand_details(){
        if($this->client_type == 1){

            //接收参数
            $demand_id = I('post.demand_id',0,'intval');//求购的id 

            //校验参数
            $demand_id || $this->ajaxDie(66);

            $info = M('demand')->where(['id' =>$demand_id])->find();

            if($info['origin_type'] == 1){
                $info['origin_area']  = '较广';
            }else if($info['origin_type'] == 2){
                $info['origin_area']  = '进口';
            } 

            $info['tender'] = M('tender')->where(['demand_id' => $demand_id])->select();
            //遍历将 投标人的头像取到放在info 中返回  并将imgs由字符串变为数组
            foreach ($info['tender'] as $k => $v) {   
                $info['tender'][$k]['head_pic']  =  M('user')->where(['id' => $v['uid']])->getField('head_pic');          
                $imgs = explode(",",$v['imgs']);
                $info['tender'][$k]['imgs'] = $imgs;
            }
            foreach ($info as $k => $v) {
                if($v['price_type'] == 2){
                    $list[$k]['price'] = '面议';
                }
            }

            if($info){
                $this->ajaxDie(1,$info);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    } 
    //我的供应信息
    public function user_supply(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $num           =  I('post.num'  ,10, 'intval');
            $minId         =  I('post.minId',0 , 'intval');
            $data['uid']   =  I('post.uid'  ,0 , 'intval');
            
            //校验参数
            $data['uid']  ||  $this->ajaxDie(30);
            $minId        &&  $data['id'] = array('lt',$minId);

            $list = M('supply')->where($data)->order('id desc')->limit($num)->select();
            foreach ($list as $k => $v) {
                $list[$k]['pic'] = explode(",",$v['pic']);
                if($v['price_type'] == '2'){
                    $list[$k]['price'] = '面议';
                }
                if($v['num'] == '-1'){
                    $list[$k]['num'] = '大货';
                }
                if($list[$k]['origin_type'] == 1){
                    $list[$k]['origin_area']= '较广';
                }else if($list[$k]['origin_type'] == 2){
                    $list[$k]['origin_area']= '进口';
                }
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
    //我的竞标
    public function user_tender(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $num           =  I('post.num'   ,10,'intval');
            $minId         =  I('post.minId' ,0 ,'intval');
            $status        =  I('post.status',0 ,'intval');
            $data['uid']   =  I('post.uid'   ,0 ,'intval');
            
            //校验参数
            $minId         && $data['id'] = array('lt',$minId);
            $data['uid']   || $this->ajaxDie(30);
          
            //组装条件
            if($status == '1'){//进行中
                $data['status'] = 0;
            }else if($status == '2'){ //已中标
                $data['status'] = 1;                                                              
            }else if($status == '3'){//未中标
                $data['status'] = 2;  
            }

            $list = M('tender')->where($data)->order('id desc')->limit($num)->select();
            foreach ($list as $k => $v) {
                $list[$k]['imgs']            = explode(",",$v['imgs']);
                $info = M('demand')->field('goods_name,goods_attr_name,num,status,trading_type,mobile_show')->where(['id' => $v['demand_id']])->find();
                $list[$k]['num']             = $info['num'];
                $list[$k]['goods_name']      = $info['goods_name'];
                $list[$k]['mobile_show']     = $info['mobile_show'];
                $list[$k]['trading_type']    = $info['trading_type'];
                $list[$k]['demand_status']   = $info['status'];
                $list[$k]['goods_attr_name'] = $info['goods_attr_name'];
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
    /* 我的 竞标详细*/
    public function tender_details(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid         =  I('post.uid',0,'intval');
            $demand_id   =  I('post.demand_id',0,'intval');

            //校验参数
            $uid         || $this->ajaxDie(30);

            $list = M('tender')->where(['uid' => $uid,'demand_id' => $demand_id])->find();
            if($list){
                $list['demand'] = M('demand')->field('goods_name,goods_attr_name,num,contacts,mobile,origin_type,origin_area,details,mobile_show')->where(['id' => $demand_id])->find();
                if($list['demand']['mobile_show'] == '0'){
                      $list['demand']['mobile'] = maskPhone($list['demand']['mobile']);
                }
                if($list['demand']['origin_type'] == 1){
                    $list['demand']['origin_area']  = '较广';
                }else if($list['demand']['origin_type'] == 2){
                    $list['demand']['origin_area']  = '进口';
                }

                $list['imgs'] = explode(",",$list['imgs']);
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    /* 中标 */
    public function win(){
        if($this->client_type == 1){

            $this->secureVerify();

            $data = array();
            $data['uid']       = I('post.uid', 0, 'intval');
            $data['demand_id'] = I('post.demand_id', 0, 'intval');
            $data['tender_id'] = I('post.tender_id', 0, 'intval');
            
            if((int)$data['uid'] <= 0) $this->ajaxDie(30);
            
            $tender = D('Tender');
            $res = $tender->updateStatus($data);
            if($res['code'] == 1){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }  
    //意见反馈
    public function suggest(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $data['qq']          =  clearXSS(I('post.qq'));
            $data['uid']         =  I('post.uid',0,'intval');
            $data['mobile']      =  clearXSS(I('post.mobile'));
            $data['question']    =  clearXSS(I('post.question'));
            $data['create_time'] =  time();

            //校验参数
            $data['uid']                  || $this->ajaxDie(30);
            $data['mobile']               || $this->ajaxDie(63);
            $data['question']             || $this->ajaxDie(62);
            check_mobile($data['mobile']) || $this->ajaxDie(63);

            if(M('feedback')->add($data)){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
        }
    }

    // 保存用户的地理位置 经纬度
    public function user_address(){
        if($this->client_type == 1){
            //接收参数
            $uid                     =  I('post.uid',0,'intval');
            $data['lat']             =  I('post.lat');
            $data['long']            =  I('post.long');
            $data['address']         =  I('post.address');
            $data['register_from']   =  I('post.register_from',0,'intval');
           
            if(M('user')->where(['id' => $uid])->save($data)){
                 $this->ajaxDie(1);
             }else{
                 $this->ajaxDie(0);
             }
        }else{
            $this->ajaxDie(43);
        }
    }
    /********1.4版本新增***********/
    /*
    *我的普通求购列表  1.4版本
    **/
    public function uesr_General_list(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid     =  I('post.uid',  0 ,'intval');
            $num     =  10;
            $minId   =  I('post.minId',0 , 'intval');
           

            $minId   && $data['id'] = array('lt',$minId);

            $list = M('GeneralDemand')->where($data)->order('id desc')->limit($num)->select();
           
            if($list){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => 0, 'msg' => '获取内容失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
    //我的普通求购详情  1.4版本
    public function user_General_detalis(){
        if($this->client_type == 1){

            //接收参数
            $did = I('post.did',0,'intval');//求购的id 

            //校验参数
            $did || $this->ajaxReturn(array('code' => 0, 'msg' => '参数未传'));

            $info = M('GeneralDemand')->field('id,uid,goods_name as gn,goods_attr_name as an,create_time as time,remain_days as days,price,num,unit,contacts,qq,standard,mobile,ticket,stock_area as s_area,origin_area as o_area,origin_type as o_type,details')
                    ->where(['id' =>$did])
                    ->find();
            //将产地code转化一下
            if($info['o_type'] == 1){
                $info['o_area']  = '较广';
            }else if($info['o_type'] == 2){
                $info['o_area']  = '进口';
            } 

            //将qq转化一下
            $info['qq']  || $info['qq'] = '无';
            
            //将价格转化一下
            if($info['price'] == '0'){
                $info['price'] = '面议';
            }else{
                $info['price'] = $info['price'].'元/公斤';
            }

            //将时间转化一下
            $info['time']  = date('Y-m-d',$info['time']);

            //是否是企业认证
            $where['uid']    = $info['uid'];
            $where['status'] = '1';
            $companyAuthen = M('companyAuthen')->where($where)->find();
            if($companyAuthen){
                $info['company_status'] = '1';
            }else{
                $info['company_status'] = '0';
            }
            //将剩余天数转化一下
            if($info['days'] == '0'){
                $info['days'] = '常年';
            }else{
                $info['days'] = $info['days'].'天';
            } 

            if($info){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $info));
            }else{
                $this->ajaxReturn(array('code' => 0, 'msg' => '获取内容失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    } 
    //我的供应列表信息
    public function user_supply_list(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $num           =  10;
            $minId         =  I('post.minId',0 , 'intval');
            $data['uid']   =  I('post.uid'  ,0 , 'intval');
            
            $minId        &&  $data['id'] = array('lt',$minId);

            $list = M('supply')->where($data)->order('id desc')->limit($num)->select();
            foreach ($list as $k => $v) {
                $list[$k]['pic'] = explode(",",$v['pic']);
                if($v['price_type'] == '2'){
                    $list[$k]['price'] = '面议';
                }
                if($v['num'] == '-1'){
                    $list[$k]['num'] = '大货';
                }
                if($list[$k]['origin_type'] == 1){
                    $list[$k]['origin_area']= '较广';
                }else if($list[$k]['origin_type'] == 2){
                    $list[$k]['origin_area']= '进口';
                }
            }
            if($list){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $list));
            }else{
                $this->ajaxReturn(array('code' => 0, 'msg' => '获取内容失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
    //我的供应详情  1.4版本
    public function user_supply_detalis(){
        if($this->client_type == 1){

            //接收参数
            $sid = I('post.sid',0,'intval');//供应的id 

            //校验参数
            $sid || $this->ajaxReturn(array('code' => 0, 'msg' => '参数未传'));

            $info = M('supply')->field('id,uid,goods_name as gn,goods_attr_name as an,pic as img,create_time as time,remain_days as days,price_type,price,maxprice,num,unit,contacts,qq,standard,mobile,ticket,supply_area as s_area,origin_area as o_area,origin_type as o_type,catalogue,details')
                    ->where(['id' =>$sid])
                    ->find();
            //图片
            if($info['img']){
                $img = explode(",",$info['img']);
                $info['img'] = $img; 
            }else{
                $info['img'] = array(); 
            }
            //详情转字符串
            $info['details'] ||  $info['details'] = ''; 
            
            //是否提供样品
            if($info['catalogue'] == '1'){
                $info['catalogue'] = '提供样品';
            }else{
                $info['catalogue'] = '无';
            }
            //将产地code转化一下
            if($info['o_type'] == 1){
                $info['o_area']  = '较广';
            }else if($info['o_type'] == 2){
                $info['o_area']  = '进口';
            } 
            //将数量转化一下
            if($info['num'] == '-1'){
                $info['num'] = '大货';
            }
            //将qq转化一下
            $info['qq']  || $info['qq'] = '无';
            
            //将价格转化一下
            if($info['price'] != '0' && $info['maxprice'] != '0'){
                $info['price']  = $info['price'].'~'.$info['maxprice'].'元/公斤';
            }else{
                $info['price']  = $info['price'].'元/公斤';
            }

            if($info['price_type'] == '2'){
                $info['price'] = '面议';
            }

            
            //将时间转化一下
            $info['time']  = date('Y-m-d',$info['time']);

            //是否是企业认证
            $where['uid']    = $info['uid'];
            $where['status'] = '1';
            $companyAuthen = M('companyAuthen')->where($where)->find();
            if($companyAuthen){
                $info['company_status'] = '1';
            }else{
                $info['company_status'] = '0';
            }
            //将剩余天数转化一下
            if($info['days'] == '0'){
                $info['days'] = '常年';
            }else{
                $info['days'] = $info['days'].'天';
            }

            if($info){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $info));
            }else{
                $this->ajaxReturn(array('code' => 0, 'msg' => '获取内容失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
}
