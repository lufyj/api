<?php

namespace Admin\Model;
use Think\Model;

/**
 * 用户模型
 * @author wpf
 */

class MemberModel extends Model {
	
	/* 自动验证 */
    protected $_validate = array(
        array('nickname', '1,16', '昵称长度为1-16个字符', 0, 'length'),
        /* array('nickname', '', '昵称被占用', 0, 'unique'), //用户名被占用 */
    	array('mobile', 'check_mobile', '请输入正确的手机号' , 1, 'function', 2)
    );

    public function lists($status = 1, $order = 'uid DESC', $field = true){
        $map = array('status' => $status);
        return $this->field($field)->where($map)->order($order)->select();
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->find($uid);
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        //记录行为
        action_log('user_login', 'member', $uid, $uid);

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'uid'             => $user['uid'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => I('post.username'),
        	'nickname'        => $user['nickname'],
        	'head_pic'        => $user['head_pic'],
            'last_login_time' => $user['last_login_time'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

    }

    public function getNickName($uid){
        return $this->where(array('uid'=>(int)$uid))->getField('nickname');
    }    
    /**
     * 更新数据之前判断是否包含图片
     *
     * @see \Think\Model::_before_update()
     */
	protected function _before_update(&$data, $option)	{		
    	//处理上传图片
    	if(!empty($_FILES['img']['name'])) {    		
    		if($_FILES['img']['error'] != 0) {
    			$this->error = '请选择图片';
    			return  false;
    		}
    		$imgConfig = C('PICTURE_UPLOAD');
    		$imgConfig['savePath'] = 'Avatar/';
    		$res = uploadImg('img', $imgConfig);
    		if($res['code'] == 1){
    			$data['head_pic'] = $res['file'][1];
    			//重新给session中的图片赋值
    			session('user_auth.head_pic', $data['head_pic']);
    			session('user_auth_sign', data_auth_sign(session('user_auth')));
    			//处理原有图片    			
    			$old_img = '.'.$this->where(array('uid' => UID))->getField('head_pic');
    			if(is_file($old_img)){
    				//删除图片文件
    				$delImgs = getImgs($old_img, -1);
    				foreach ($delImgs as $v){
    					unlink($v);
    				}    				
    			}   			
    		}else{
    			$this->error = $res['msg'];
    			return false;
    		}
    	}
    }
}
