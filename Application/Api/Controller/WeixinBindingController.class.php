<?php
namespace Api\Controller;
use Think\Controller;
class WeixinBindingController extends BaseController {
    /*
     * 用户微信绑定(已有账号)
     */
    public function weixinBinding(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $uid            = I('post.uid',0,'intval');
            $password       = clearXSS(I('post.password'));
            $data['mobile'] = clearXSS(I('post.mobile'));

            //用户已有账号  则需要将微信登录所产生的user表中的信息删除  
            $user        = M('user');
            $weixinLogin = M('weixinLogin');
            //开启事务 
            $user       ->startTrans();
            $weixinLogin->startTrans();
          
            //判断是否是微信登录 与 是否绑定过手机
            $openid = M('weixinLogin')->where(['uid' => $uid])->getField('openid');
            $mobile = M('mobileLogin')->where(['uid' => $uid])->getField('mobile');
            if(!$openid && $mobile){
                $this->ajaxDie(2);
            }
           
            //将用户传过来的密码进行加密
            $data['password'] = password_md5($password);
            //查询成功 说明账户正确  否则代表账户错误
            $res = M('mobileLogin')->where($data)->find();
            //查看该用户时是否绑定过
            $weixin_status = $user->where(['id' => $res['uid']])->getField('weixin_status');
            if($weixin_status == '1'){
                $this->ajaxDie(4);
            }

            if($res['uid']){    
                $data['uid'] = $res['uid'];
                //更改微信表中对应的uid
                $wx       = $weixinLogin->where(['uid' => $uid])->save($data);
                //先删除user表中 weixin注册产生的user信息
                $user_del = $user->where(['id' => $uid])->delete();
                if($wx && $user_del){
                    //将俩个表的事物提交
                    $user       ->commit();
                    $weixinLogin->commit();
                    //成功将信息存入session 并 更新user表中微信绑定的状态
                    $info           =  M('user')->where(['id' =>$res['uid']])->Field('id,realname,token')->find();
                    M('user')->where(['id' =>$res['uid']])->save(array('weixin_status' => 1));
                    $info['mobile'] =  M('mobileLogin')->where(['uid' => $info['id']])->getField('mobile');
                    $info['mobile'] || $info['mobile'] = ''; 
                   
                    //注册成功 判断用户手机号 是否被推广人员录入
                    $customer = M('extension_customer')->where(['mobile' => $mobile])->find();
                    //如果该手机号被推广人员录入 则改变推广表中的登录状态
                    if($customer){
                        M('extension_customer')->where(['id' => $customer['id']])->save(array('is_login' => '1'));
                    }
                    $this->ajaxDie(1,$info);
                }else{
                    //将俩个表的事物回滚
                    $user       ->rollback();
                    $weixinLogin->rollback();
                    $this->ajaxDie(0);
                }
            }else{
                $this->ajaxDie(3);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
   

  /*
     * 用户微信注册绑定(没有账号) --相当于走一遍注册流程--
     */
    public function weixinRegisterBinding(){
        if($this->client_type == 1){ 

            $this->secureVerify();
            //验证推广码
            $spreading_code = clearXSS(I('post.spreading_code'));
            //如果用户输入了推广码 那校验一下推广码的有效性
            if($spreading_code){
                if(extensionCode($spreading_code)){}else{$this->ajaxDie(105);}
            }

            // 微信登录session保存的id   用做关联 
            $uid       = I('post.uid',0,'intval');
            $password  = clearXSS(I('post.password'));
           
            $uid      ||  $this->ajaxDie(30);
            //判断是否是微信登录 与 是否绑定过手机
            $openid = M('weixinLogin')->where(['uid' => $uid])->getField('openid');
            $mobile = M('mobileLogin')->where(['uid' => $uid])->getField('mobile');
            if(!$openid && $mobile){
                $this->ajaxDie(2);
            }
            //判断短信验证码是否存在
            $code = clearXSS(I('post.captcha'));
            $yzcode = session('yzcode');
            $session_moblie = session('mobile');
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
            //收集数据          
            $model = D('User');
            $data = $model->create();
            $mobile = clearXSS(I('post.mobile'));
            $realname['realname'] = $data['realname'];
            $password_md5 = password_md5($password);
            if(!$data){
                $this->ajaxDie($model->getError());
            }else{
                //判断session中存的手机号与用户传过来的是否一致
                if ($mobile != $session_moblie['code']) {
                    $this->ajaxDie(99);
                }
                //如果查询到数据 只能说明一种情况 就是该手机号被广告机注册了(特点 有mobile 没密码)
                $Ad_mobile = M('mobile_login')->where(['mobile' => $mobile])->find();
                //如果被广告机注册过了 就先处理广告机这种情况
                if($Ad_mobile){
                    //先删除微信注册产生的user表信息
                    $user_del = M('user')->where(['id' => $uid])->delete();
                    //将user表中的状态 昵称 修改
                    M('user')->where(['id' =>$Ad_mobile['uid']])->save(array('weixin_status' => 1,'realname' => $data['realname']));
                    //将密码更新到mobileLogin表中
                    M('mobileLogin')->where(['id' => $Ad_mobile['id']])->save(array('password' => $password_md5));
                    // 将微信登录表对应的uid 修改
                    $map['uid'] = $Ad_mobile['uid'];
                    $res = M('weixinLogin')->where(['uid' => $uid])->save($map);
                    $uid = $Ad_mobile['uid'];
                }else{
                    //将用户传过来的名称更改到user表
                    M('user')->where(['id' => $uid])->save($realname);
                    $map['mobile'] = $mobile;
                    $map['password'] = $password_md5;
                    $map['uid'] = $uid;
                    $res = M('mobile_login')->add($map);
                }
           }
            if($res !== false){
                //清除session
                session('yzcode', null);//清除session中短信验证码
                session('mobile', null);//清除session中短信验证码
                $info = M('user')->where(['id' =>$uid])->Field('id,realname,token')->find();
                M('user')->where(['id' =>$uid])->save(array('weixin_status' => 1));
                $info['mobile'] = $mobile;
                //注册成功 判断用户手机号 是否被推广人员录入
                $customer = M('extension_customer')->where(['mobile' => $mobile])->find();
                //如果该手机号被推广人员录入 则改变推广表中的登录状态
                if($customer){
                    M('extension_customer')->where(['id' => $customer['id']])->save(array('is_login' => '1'));
                }
                //如果用户输入了推广码  那么去推广人员表中去找对应的推广员
                if($spreading_code){
                    $eid = M('extension')->where(['spreading_code' => $spreading_code])->getField('id');
                    if($eid){
                        $massage['eid']    = $eid;
                        $massage['mobile'] = $mobile;
                        $massage['create_time'] = time();
                        $massage['is_login'] = '1';
                        $massage['tel_attributions'] = D('tel_attributions')->getTel_attr($mobile);
                        $massage['month'] = date('Ym');
                        M('extension_customer')->add($massage);
                    }
                }
                $this->ajaxDie(1,$info);
            }else{
                $this->ajaxDie(0);
            }
        }else{
             $this->ajaxDie(43);
        }
    }
}
