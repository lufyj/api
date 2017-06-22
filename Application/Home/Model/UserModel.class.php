<?php 
namespace Home\Model;
use Think\Model;

/**
 * 前台用户模型
 * @author wpf
 *
 */
class UserModel extends Model{
	
	public $error_code = 0;
	
	/**
	 * 根据用户id获取用户的相关信息
	 * @param unknown $id
	 */
	public function getOne($id){
		/***获取用户账号信息***/
		$codition = array(			
			'status' => 1,
			'id'	 => session('user_sign.id')
		);
		
		$data = $this->field('email,weixin,qq,address,head_pic,points,memos')
			->where($codition)
			->find();
		
		return $data;
		
		/***获取用户安全账号信息***/
				
	}
	/**
	 * 更新用户信息
	 * @param unknown $data
	 * @return Ambigous <boolean, unknown>
	 */
	public function updateOne($data){		
		$res = $this->save($data);
		if($res !== false){
			//判断是否有上传的图片
			if(!empty($_FILES['file1']['name'])) {
				if($_FILES['file1']['error'] != 0) {
					$this->error = '请选择图片';
					return 0;
				}
				$imgConfig = C('PICTURE_UPLOAD');
				$imgConfig['savePath'] = 'Avatar/';
				$res = uploadImg('file1', $imgConfig);
				if($res['code'] == 1){
					$this->updateField('head_pic', $res['file'][1]);
				}else{
					$this->error = $res['msg'];
					return 0;
				}
			}
			$this->error = '操作成功';
			return 1;
		}
		$this->error = '操作失败';
		return 0;
	}	
	/**
	 * 更新某个字段
	 * @param unknown $data
	 */
	public function updateField($fieldName,$fieldValue){
		$conditon = array('id' => session('user_sign.id'));
		switch ($fieldName){
			case 'head_pic':
				$head_pic = $this->where($conditon)->getField('head_pic');
				if(trim($head_pic)){
					unlink('.'.$head_pic);
				}
				break;
		}		
		$this->where($conditon)->setField($fieldName, $fieldValue);
	}
	/**
	 * 更新密码
	 * @param unknown $fieldName
	 * @param unknown $fieldValue
	 */
	public function updatePwd($uid, $pwd){		
		$this->where(array('id' => $uid))->setField('password', $pwd);
	}
	
	/**
	 * 验证手机是否存在
	 * @param unknown $mobile
	 */
	public function checkExistMobile($mobile){
		$data = $this->where(array('mobile' => $mobile))->find();
		if($data){
			return $data;
		}
		return false;
	}
	
	/**
	 * 保存意见反馈
	 */
	public function saveSuggest($data){
			
		if(!$data['mobile']){
			$data['mobile'] = session('user_sign.mobile');
		}
		$data['uid'] = session('user_sign.id');		
		$data['create_time'] = time();
		$data['update_time'] = time();
		
		return M('feedback')->data($data)->add();		
	}
	
	/**
	 * 根据手机号，密码登录	
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function checkLoginIn($cookie_username = '', $cookie_password = ''){
		
		//第一步，先检验验证码(如果有的话)
		if (session('?login_error_counter') && (session('login_error_counter') >= 4) ) {
			//此时必须有验证码
			$captcha = I('post.code');
			if (!check_verify($captcha)) {
				$this->error = '验证码错误或已过期';
				$this->error_code = 2;
				return false;
			}
		}		
		
		//读取前台是否传入了用户名和密码
		//若用户名密码为空，则判断cookie中的信息
		$username = I('post.mobile');
		$password = I('post.password');
		if (empty($username) && empty($password)) {
			
			//先从cookie中获取，看是否是自动登录
			//$cookie_username = encrypt(cookie(md5(('mobile'^C('key_secret')))),'D',C('val_secret'));
			//$cookie_password = encrypt(cookie(md5(('password'^C('key_secret')))),'D',C('val_secret'));
			
			if (!empty($cookie_username) && !empty($cookie_password)) {
				$username = $cookie_username;
				$password = $cookie_password;
			}
		}
		
		$password = password_md5($password);
		$condition = array(
			'mobile'   => $username,
			'password' => $password,
			'status'   => 1			
		);
		 
		$info = $this->field('id,realname,mobile,company_auth_status as st')->where($condition)->find();
		if($info){
			if(!$info['realname']){
				$info['realname'] = $mobile;
			}
			session('user_auth',$info['st']);//添加用户的认证信息状态 jingwei
			D('CompanyConfirm')->isBusinessAuth($info['id']);
			unset($info['st']);
			session('user_sign',$info);
			//更新用户最后一次登录时间
			$this->where('id='.$info['id'])->setField('last_login_time', time());
			$this->error = '登录成功';
			$this->error_code = 1;
			return true;		
		}else{
			//如果用户名错误，或者不存在记录
			$this->error = '密码错误或用户名不存在';
			$this->error_code = 3;
			return false;
		}		
	}
	/**
	 * 设置下次自动登录
	 */
	public function set_auto_login(){
		$username = I('post.mobile');
		$password = I('post.password');
	
		cookie(md5(('mobile'^C('key_secret'))),encrypt($username, 'E', C('val_secret')), array('expire' => 7*24*60*60, 'prefix' => 'login_'));
		cookie(md5(("password"^C('key_secret'))),encrypt($password, 'E', C('val_secret')), array('expire' => 7*24*60*60, 'prefix' => 'login_'));	
	}
	/**
	 * 验证用户自动登录的是否正确
	 * @return [type] [description]
	 */
	public function check_is_auto_login(){
		$cookie_username = encrypt($_COOKIE['login_'.md5(('mobile'^C('key_secret')))],'D',C('val_secret'));
		$cookie_password = encrypt($_COOKIE['login_'.md5(('password'^C('key_secret')))],'D',C('val_secret'));
		//$cookie_username = encrypt(cookie(md5(('mobile'^C('key_secret')))),'D',C('val_secret'));
		//$cookie_password = encrypt(cookie(md5(('password'^C('key_secret')))),'D',C('val_secret'));
		if (!empty($cookie_username) && !empty($cookie_password)) {
			return $this->checkLoginIn($cookie_username, $cookie_password);
		}
		return false;
	}
}
?>