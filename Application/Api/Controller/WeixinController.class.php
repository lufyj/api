<?php
namespace Api\Controller;
use Think\Controller;
class WeixinController extends BaseController {
    //curl方法
    public function curl($url){ 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);


        $res= curl_exec($curl);

        curl_close($curl);
        return $res;
    }
   // 微信登录 获取用户信息
   public  function getUserOpenId(){
        header("Content-Type:text/html;charset=utf-8"); 
        //2 获取到网页授权的access_token
        $appid     = 'wxd0f494d8eb878d31';
        $appsecret = "a9747b92870e66cc22f286885d42485a";
        $code      = $_GET['code'];
        $url       = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        //3 拉取用户的openid
        $res          = $this->curl($url,'get');
        $resData      = json_decode($res);
        $access_token = $resData->access_token;
        $openid       = $resData->openid;
        //拉取用户详细资料
        $url   = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";

        $res   = $this->curl($url,'get');
        $resD  = json_decode($res);
        //判断该openid是否注册过  没有注册过  给他注册一个账户
        $openid = $resD->unionid;
        if(!$openid){
             $this->ajaxReturn(array('code' => 0,'msg' => '信息拉取失败'));
            exit;
        }
        //如果有该用户  那么查询出来他的信息
        $uid = M('weixin_login')->where(['openid' => $openid])->getField('uid');
        if($uid){
            $info   = M('user')->where(['id' => $uid])->Field('id,realname')->find();
            $mobile = M('mobileLogin')->where(['uid' => $info['id']])->getField('mobile');
            $token  = getToken($uid);
            M('user')->where('id='. $uid)->setField(array('token' => $token));
            if($mobile){
                $info['mobile']  = $mobile;
            }else{
                 $info['mobile'] = '';
            }
            $info['token'] = $token;
            //更新用户最后一次登录时间
            M('user')->where('id='.$info['id'])->setField('last_login_time', time());
             $this->ajaxReturn(array('code' => 1,'msg' => $info));
        }else{
            //将微信用户的资料 存起来
            $data['realname']        = $resD->nickname;
            $data['address']         = $resD->city;
            $data['register_from']   = '0';
            $data['register_ip']     = get_client_ip();
            $data['create_time']     = time();
            $data['update_time']     = time();
            $data['last_login_time'] = time();
            $user_id= M('user')->add($data);
            $token = getToken($user_id);
            M('user')->where('id='. $user_id)->setField(array('token' => $token));
            if($user_id){
                $map['uid']      = $user_id;
                $map['openid']   = $openid;
                M('weixin_login')->add($map);
                $info = M('user')->where(['id' => $user_id])->Field('id,realname')->find();
                $mobile = M('mobileLogin')->where(['uid' => $info['id']])->getField('mobile');
                if($mobile){
                    $info['mobile']  = $mobile;
                }else{
                     $info['mobile'] = '';
                }
                $info['token'] = $token;
                $this->ajaxReturn(array('code' => 1,'msg' => $info)); 
            }
        }
    }
}
