<?php

namespace Api\Model;
use Think\Model;

/**
 * 后台用户模型
 * @author admin
 *
 */
class UserModel extends BaseModel{

	/**	
	 * 自动验证--我只考虑注册，所以以下选项必须被验证
	 * @var unknown
	 */
    protected $_validate = array(
  
    	/* 验证用户名 */    	
    	array('realname','require', 10), //请输入2-16个字符
    	array('realname', '2,10', 10, 0, 'length'), //请输入2-10个字符
    	    	
    	/* 验证密码 */
    	array('password', '6,20', 40, 1, 'length'), //请输入6-20个字符
    	array('repassword','password',41, 1, 'confirm'), //两次密码不一致
    	
    	/* 验证手机号码 */
    	array('mobile', '/^(13[0-9]|15[012356789]|17[3678]|18[0-9]|14[57])[0-9]{8}$/', 20, 1), //请输入正确的手机号    	
    	// array('mobile', '', 21, 1, 'unique'), //手机号被占用
    	
    	array('type','require', 80, 0), //请输入2-16个字符
    );

    protected $_auto = array(
    	array('password', 'password_md5', 1, 'function'),
    	array('register_from', 'get_device_type', 1, 'function'),
    	array('register_ip', 'get_client_ip', 1, 'function'),
        array('create_time', NOW_TIME),
        array('update_time', NOW_TIME),
    );
    
    /**
     * 根据手机号，密码登录
     * @param unknown $mobile
     * @param unknown $password
     * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function checkLoginIn($mobile, $password,$pushId=''){
    	
    	$password = password_md5($password);    	
    	$condition = array(
    		'mobile'   => $mobile,
    		'password' => $password,
    	);
    	$user_info = M('mobileLogin')->field('uid,mobile')->where($condition)->find(); 
    	$info = $this->field('id,realname')->where(['id' => $user_info['uid']])->find();
    	if($user_info){
    		if(!$info['realname']){
    			$info['realname'] = $user_info['mobile'];
    		}
            $info['mobile'] = $user_info['mobile'];
			//session('user_auth',$info['st']);//添加用户的认证信息状态 jingwei
            //------生成token，规则：用户uid+当前时间戳+随机数字------            
    		session('user_sign', $info);
            $token = getToken($info['id']);
            $info['token'] = $token;
    		//更新用户最后一次登录时间
    		$this->where('id='.$info['id'])->setField(array('last_login_time' => time(), 'token' => $token));

			//保存用户登录设备信息以便可以极光推送
			if($pushId){
				$status=D('PushDevice')->saveDevice($pushId,$info['id']);
			}
    		return $info;
    	}else{
    		return false;
    	}
    }

	/**
	 * 退出登录
	 * @param unknown $uid
	 * @param unknown $pushId
	 */
	public function loginOut($uid,$pushId){

		$where['device']=$pushId;
		$where['user_id']=$uid;
		if(M('PushDevice')->where($where)->count()){
			$st=M('PushDevice')->where($where)->delete();
		}
		return 'ok';
	}
    
    /**
     * 验证手机是否存在
     * @param unknown $mobile
     */
    public function checkExistMobile($mobile){
    	$res = M('mobileLogin')->field(1)->where(array('mobile' => $mobile))->find();
    	if($res){
    		return true;
    	}
    	return false;
    }
    /**
     * 时间展现形式处理方法 
     * @param $timeStamp 指定时间戳
     * @return 时间展示形式
     */
    public function timeShow($timeStamp){
        $d1=date('Y-m-d H:i:s',$timeStamp);
        $tmp = substr($d1,0,10);
        $today = date('Y-m-d');
        if($tmp==$today){
            $tmp=date('H:i',$timeStamp);
            return $tmp;
        }else{
            $yesterday=date("Y-m-d",strtotime("-1 day"));
            
            if($tmp==$yesterday){
                $tmp="昨天 ".date('H:i',$timeStamp);
                return $tmp;
            }else{
                $tmp=date('y-m-d H:i',$timeStamp);
                return $tmp;
            }
        }
        
    }
}
