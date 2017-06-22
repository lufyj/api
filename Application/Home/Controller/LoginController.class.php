<?php

namespace Home\Controller;

/** 
 * 前台用户登录及注册控制器
 */
class LoginController extends HomeController {	
	
	/* 初始化 */
	protected function _initialize(){
		$user = session('user_sign');
		if($user && (int)$user['id'] > 0){
			$this->redirect('/');
		}			
	}
	/* 用户登录页面 */
	public function index(){
		$this->meta_title = '用户登录';
		$this->display();
	}
	/* 用户注册页面 */
	public function register(){
		
		if(C('USER_ALLOW_REGISTER')){
			$this->error('注册已关闭');
		}
		$this->meta_title = '用户注册';
		$this->display();
		
	}
	/*验证码*/
	function check_verify(){
		$code=I('get.code');
    	$verify = new \Think\Verify();
    	$status=$verify->check($code);
    	
    	if($status){
    		$rs['status']='1';
    		$rs['msg']='验证成功';
    		$this->ajaxReturn($rs);
    	}else{
    		$rs['status']='0';
    		$rs['msg']='验证失败';
    		$this->ajaxReturn($rs);
    	}
	}
	/*******************用户登录操作**********************/
	//用户登录
	public function login(){
		
		if(IS_AJAX && IS_POST){			
			$model = D('User');
			//首先判断手机号和密码是否正确
			$mobile   = clearXSS(I('post.mobile'));
			$password = clearXSS(I('post.password'));
			if(!check_mobile($mobile)){
				$this->dieMsg('请输入正确的手机号');
			}
			if(!$password || strlen($password) <5 || strlen($password) > 20){
				$this->dieMsg('请输入6-20位的密码');
			}
			
			$exist = session('?login_error_counter');
			if (empty($exist)) {
				//统计登录失败错误次数(超过三次就要显示验证码)
				session('login_error_counter', 1);
				session('login_error_time', time());
			}
			if($model->checkLoginIn()){
				session('bf_login_error_counter',null);
				session('bf_login_error_time',null);
				//这里判断下用户是否勾选了下次自动登录
				$auto_login = I('post.auto_login');
				if ($auto_login == 1) {
					$model->set_auto_login();
				}				
			}else{				
	    		//第一次失败后，配置项分钟内就将失败次数++；
    			if ( (time()-session('login_error_time')) < (C('login_error_time_zone')*60) ) {
		    		session('login_error_counter',session('login_error_counter')+1);
    			}else{
    				//大于1个小时后，重新计算counter数，设置是否显示验证码
	    			session('login_error_counter', 1);
    			}
    		}
    		//返回数据
    		$this->ajaxReturn(array('code' => $model->error_code,'msg' => $model->getError()));    		
		}
	}		
	/*******************找回密码操作**********************/	
	
	//找回密码
	public function findpwd(){
		
		if(I('get.i',0,'intval') == 2){
			$findpwd_s = session('findpwd');
			if(!$findpwd_s){
				$this->display();//跳到找回密码首页
				exit;
			}
			$this->assign('mobile', $findpwd_s['mobile']);			
			$this->display("findpwd2");			
		}else if(I('get.i',0,'intval') == 3){
			$this->display("findpwd3");
		}else{
			$this->display();
		}		
	}
	//验证手机号码是否存在
	public function validUsername(){
		
		if(IS_AJAX && IS_POST){
			$mobile = clearXSS(I('post.mobile'));
			$code = clearXSS(I('post.code'));
			
			if(!$mobile){ $this->ajaxReturn(array('code' => 0,'msg' => '请输入您的电话号码')); }			
			if(!$code){ $this->ajaxReturn(array('code' => 0,'msg' => '请输入验证码')); }			
			$check_verify = check_verify($code);
			if (!$check_verify) { $this->ajaxReturn(array('code' => 0,'msg' => '验证码错误或已过期')); }
			
			$res = D('User')->checkExistMobile($mobile);//根据手机号查询系统中是否存在该值
			if($res){
				$yzcode = rand(10000,99999);//在这里发送验证码
				$msg = "尊敬的用户您好，您已申请重置登录密码，本次手机验证码为：".$yzcode." 请及时验证，谢谢！";
				if(send_msg($mobile,$msg)){
					//记录发送的验证码及手机号
					session('findpwd', array(
					'mobile' => $mobile,
					'yzcode' => $yzcode
					));
					$this->ajaxReturn(array('code' => 1,'msg' => '验证通过'));					
				}else{
					$this->ajaxReturn(array('code' => 0,'msg' => '网络连接超时，请您稍后重试'));					
				}
			}
			$this->ajaxReturn(array('code' => 0,'msg' => '您输入的手机号不存在，请核对后重新输入'));			
		}		
	}
	//验证短信验证码及密码
	public function validCode(){
		
		if(IS_AJAX && IS_POST){			
			$code = clearXSS(I('post.code'));//此为手机发送短信验证码
			$pwd  = clearXSS(I('post.pwd'));
			$rpwd = clearXSS(I('post.rpwd'));
			
			//对验证码进行验证
			if(!$code){	$this->ajaxReturn(array('code' => 0,'msg' => '请输入短信验证码')); }
			if($code != session('findpwd.yzcode')){	$this->ajaxReturn(array('code' => 0,'msg' => '验证码错误或已过期，请重新发送验证码')); }
			//对密码进行验证
			if(!$pwd || !$rpwd){ $this->ajaxReturn(array('code' => 0,'msg' => '重置密码不能为空')); }
			if($pwd != $rpwd){ $this->ajaxReturn(array('code' => 0,'msg' => '两次密码输入不一致')); }
			
			$info = D('User')->checkExistMobile(session('findpwd.mobile'));
			$md5_pwd = password_md5($pwd);
			if($md5_pwd == $info['password']){
				$this->ajaxReturn(array('code' => 0,'msg' => '不能和原有密码相同'));				
			}
			$res = D('User')->updatePwd($info['id'], $md5_pwd);
			if($res !== false){
				session('findpwd', null);
				session('[destroy]');
				$this->ajaxReturn(array('code' => 1,'msg' => '验证通过'));				
			}
			$this->ajaxReturn(array('code' => 0,'msg' => '找回密码失败，请联系药都网客服'));			
		}
	}
}
