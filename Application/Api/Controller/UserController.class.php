<?php
namespace Api\Controller;

class UserController extends BaseController {
	
	/**
	 * 用户登录
	 */
	public function login(){
		if(IS_POST){
			if($this->client_type == 1){
				
			}else{
				//验证验证码，验证过一次就会失效
				$code = clearXSS(I('post.code'));
				if($code){
					$check_verify = check_verify($code);
					if (!$check_verify) {
						$this->ajaxDie(30);
				}	
				}
							
			}
			$mobile = clearXSS(I('post.mobile'));
			$password = clearXSS(I('post.password'));
			$pushId = clearXSS(I('post.push_id'));
			$res = D('User')->checkLoginIn($mobile, $password,$pushId);
			if($res){
				$this->ajaxDie(1, $res);
			}else{
				$this->ajaxDie(-1);
			}			
		}
	}
	
	/*
	 * 微信登录
	 */
	public function weixin_login(){
		if($this->client_type == 1){
			$openid = clearXSS(I('post.openid'));
			$unionid = clearXSS(I('post.unionid'));
			if(!$openid){
				$openid = $unionid;
			}
			if(!$openid){
				$this->ajaxDie(0);
			}
			$realname = clearXSS(I('post.realname'));
			$pushId = clearXSS(I('post.push_id'));
			// 检测openid  是否注册有用户  
			$uid = M('weixin_login')->where(['openid' => $openid])->getField('uid');
	        if($uid){
	        	//拿取用户信息
	            $info = M('user')->where(['id' => $uid])->Field('id,realname')->find();	           
				if($info){					
		            // 如果用户已经关联账号  取出手机号  做为参数返回给客户端
		            $mobile = M('mobileLogin')->where(['uid' => $uid])->getField('mobile');
		            if($mobile){
		            	$info['mobile'] = $mobile;
		            }else{
		            	$info['mobile'] = '';
		            }
		            //保存用户登录设备信息以便可以极光推送
					if($pushId){
						$status=D('PushDevice')->saveDevice($pushId,$info['id']);
					}
					$token = getToken($uid);
					//更改用户最后一次登录时间
		            M('user')->where('id='. $uid)->setField(array('last_login_time' => time(), 'token' => $token));

		            $info['token'] = $token;
					$info['openid'] = $openid;					
					$this->ajaxDie(1,$info);
				}else{
					$this->ajaxDie(0);
				}
	        }else{
	            //将微信用户的资料 存起来
	            $data['realname'] = $realname;
	            $data['create_time'] = time();
	            $data['update_time'] = time();
	            $data['last_login_time'] = time();
	            $uid= M('user')->add($data);
	            if($uid){
	                $map['uid'] = $uid;
	                $map['openid'] = $openid;
	                $weinxin = M('weixin_login')->add($map);
	                $info = array(
	                	'id' => $uid,
	                	'mobile' => '',
	                	'realname' => $realname
	                );	                
	                $token = getToken($uid);
	                //更改用户最后一次登录时间
		            M('user')->where('id='. $uid)->setField(array('last_login_time' => time(), 'token' => $token));
	                //将用户的openid返回给用户
	                $info['openid'] = $openid;
	                $info['token'] = $token;	               
	                $this->ajaxDie(1,$info);
	            }
	        }
	    }else{
	    	$this->ajaxDie(43);
	    }
	}
	/**
	 * 注册用户
	 */
	public function register(){
		if(IS_POST){			
			if($this->client_type == 1){
				
			}else{				
				//验证码检测，如股票通过了在发送短信
				/* $code = I('post.code');
				$check_verify = check_verify($code);
				if (!$check_verify) {
					$this->ajaxDie(-20);
				} */				
			}
			//验证推广码
			$spreading_code = clearXSS(I('post.spreading_code'));
			//如果用户输入了推广码 那校验一下推广码的有效性
			if($spreading_code){
				if(extensionCode($spreading_code)){}else{$this->ajaxDie(105);}
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
			$password = clearXSS(I('post.password'));
			$password_md5 = password_md5($password);
			$mobile = clearXSS(I('post.mobile'));
			$info = M('mobile_login')->where(['mobile' => $mobile])->find();
			$id = $info['id'];
			if(!$data){
				$this->ajaxDie($model->getError());
			}else{
				//判断session中存的手机号与用户传过来的是否一致
				if ($mobile != $session_moblie['code']) {
					$this->ajaxDie(99);
				}
				if($info){
					M('mobile_login')->where(['id' => $id])->save($data);
				}else{
					$res = $model->add();
					$map['mobile'] = $mobile;
					$map['password'] = $password_md5;
					$map['uid'] = $res;
					M('mobile_login')->add($map);
				}
				if($res !== false){
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
					//这里需要体现注册完后是让用户跳到登录页还是跳到首页
					//清除session
					session('yzcode', null);//清除session中短信验证码
					session('mobile', null);//清除session中短信验证码
					$res = D('User')->checkLoginIn($mobile, $password);
					$this->ajaxDie(1);
				}else{
					$this->ajaxDie(-1);
				}
			}
		}
	}
	    
    /**
     * 获取手机短信验证码
     * 先判断接口来自pc还是手机端,1 代表手机，否则为pc
     * 暂时不考虑version版本
     */
    public function mobileCode(){    	
    	    	
    	$mobile = clearXSS(I('post.mobile'));
    	//验证手机是否符合规范
    	if(!check_mobile($mobile)){
    		$this->ajaxDie(20);
    	}    	
    	
    	if($this->client_type == 2){
    	
    		
    	}else{  
    		//验证码检测，如股票通过了在发送短信
    		$code = I('post.code');
			// $check_verify = check_verify($code);
			// if (!$check_verify) {				
			// 	$this->ajaxDie(30);
			// }
			//验证手机是否已被注册过
    		$user_model = D('User');
    		$res = $user_model->checkExistMobile($mobile);
    		//判断用户传过来的手机号是否有密码  没有密码 依旧发送验证码
    		$type = I('post.type',0,'intval');
    		if($type != 1){
    			if($res){
	    			$info = M('mobileLogin')->where(['mobile' => $mobile])->find();
	    			if($info['password']){
	    				 $this->ajaxDie(21);  
	    			}
    			} 	
    		}
    			
    	}
    	//短信验证码生成时间
    	$yzcode_create_at = session('yzcode_create_at');
    	if ($yzcode_create_at && ($yzcode_create_at+120) < time()) { $this->ajaxDie(35); }
    	//生成验证码准备发送
    	$yzcode = rand(10000,99999);    	 
    	//这里的短信内容可以在后台进行配置
    	$code_source  = clearXSS(I('post.code_source'));
    	if($code_source == '1'){
    		$content="感谢您注册药都网，您的验证码是：".$yzcode."，如非您本人操作，可忽略此消息【药都网】";
    	}else if($code_source == '2'){
    		$content="您正在进行找回密码操作，您的验证码是：".$yzcode."，如非您本人操作，可忽略此消息【药都网】";
    	}else if($code_source == '3'){
    		$content="您正在进行绑定手机号操作，您的验证码是：".$yzcode."，如非您本人操作，可忽略此消息【药都网】";
    	} 
    	if(send_msg($mobile, $content)){//这里暂时用$yzcoded代替$content
    		session('yzcode', array(
    			'code' => $yzcode,
    			'yzcode_create_at' => time()
    		));
    		session('mobile',array(
    			'code' => $mobile,
    			'mobile_create_at' => time()
    		));
    		$this->ajaxDie(1);    		
    	}else{
    		$this->ajaxDie(-1);
    	}   	
    } 

    //用户退出登录
	public function loginOut(){
		if(IS_POST){

			$this->secureVerify();

			$this->client_type=I('post.client_type',1,'intval');
			if($this->client_type == 1 || $this->client_type == 2){
				$uid = I('post.uid',0,'intval');
				$pushId = clearXSS(I('post.push_id'));
				if($uid && $pushId){
					$res = D('User')->loginOut($uid,$pushId);
				}
			}
			$this->ajaxDie(1,$res);
		}
	} 
    //找回密码
    public function find_password(){
    	$code = I('post.code',0,'intval');
    	$mobile = I('post.mobile');
    	$password = I('post.password');
    	$confirm_password = I('post.confirm_password');
    	//两次输入密码不一致
    	if($password != $confirm_password){
    		$this->ajaxDie(41);
    	}
    	$strlen = mb_strlen($password);
    	if(6 > $strlen || $strlen > 20){
    		$this->ajaxDie(40);
    	}
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
        //验证手机号是否有更改
        $session_moblie = session('mobile');
        if ($mobile != $session_moblie['code']) {
                $this->ajaxDie(99);
        }   
        $data['password'] = password_md5($password);
        $user_password = M('mobile_login')->where(['mobile' => $mobile])->getfield('password');
        if($data['password'] ==  $user_password){
        	$this->ajaxDie(98);
        }
        if($user_password){
        	if(M('mobile_login')->where(['mobile' => $mobile])->save($data)){
        		session('yzcode', null);
        		$this->ajaxDie(1);
        	}else{
        		$this->ajaxDie(0);
        	}
        }else{
        	 $this->ajaxDie(34);
        }
    }
}