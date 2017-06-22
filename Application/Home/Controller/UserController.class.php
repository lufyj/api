<?php
namespace Home\Controller;

/**
 * 前台用户中心管理器
 * @author wpf
 *
 */
class UserController extends HomeController {

	private $model;
	
	/* 初始化 */
	protected function _initialize(){
		
		parent::_initialize();
		
		$this->home_login();//判断是否已登录
		$this->model = D('User');
				
	}  
	/* 个人中心首页展示 */
    public function index(){    	
    	
    	$this->display('profile');
    }
    /* 个人信息展示 */
    public function profile(){    	
    	if(IS_POST){
    		$post = I('post.');
    		$data = array();
    		$data['qq']      = clearXSS($post['qq']);
    		$data['id']		 = session('user_sign.id');
    		$data['email']   = clearXSS($post['email']);
    		$data['weixin']  = clearXSS($post['weixin']);
    		$data['address'] = clearXSS($post['address']);
    		$data['realname']= clearXSS($post['realname']);
    		if((int)$data['id'] <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '请先登录'));
    		
    		$res = $this->model->updateOne($data);    		
    		$this->ajaxReturn(array('code' => $res,'msg' => $this->model->getError()));    		
    	}else{    		
    		$user_info = $this->model->getOne($this->uid);    		
    		$this->assign('info', $user_info);
    		$this->assign('act', 'profile');
    		$this->meta_title = '个人信息';
    		$this->display();
    	}    	
    }    
    /* 用户退出登录 */
    public function logout(){
    	session(null); // 清空当前的session
    	session('[destroy]');
    	cookie(null,'login_');
    	$this->redirect("/");
    }    
	/* 意见反馈 */
    public function suggest(){
    	if(IS_AJAX && IS_POST){
    		$post = I('post.');
			$ret['code'] = 0;
			
    		if(!$post['code']){
    			$ret['msg'] = '请填写验证码';    			
    			$this->ajaxReturn($ret);
    		}
    		if(!$post['question']){
    			$ret['msg'] = '请填写意见';
    			$this->ajaxReturn($ret);    			
    		}
    		
    		$check_verify = check_verify($post['code']);
			if (!$check_verify) {
				$ret['msg'] = '验证码错误或已过期';
    			$this->ajaxReturn($ret); 
			}
    		foreach($post as $k=>$v){
				$post[$k]=clearXSS($v);
			}
    		$res = $this->model->saveSuggest($post);
    		if((int)$res > 0){
    			$ret = array( 'code' => 1, 'msg'  => '提交成功，稍后我们会与您取得联系' );    			
    		}else{
    			$ret['msg'] = '网络连接超时，请您稍后重试';
    		}
    		$this->ajaxReturn($ret);    		
    	}else{    		
    		$this->assign('act', 'suggest');
    		$this->meta_title = '意见反馈';
    		$this->display();
    	}    	
    }    
    /**
     *****************************开始关注操作**************************** 
     */
    /* 关注列表 */
    public function follow(){    	
    	$limitRecord = 18; // 单页最多几条数据
    	$p = I('get.p', 0, 'intval'); // 当前页码
    	$model = D('Follow');
    	$whereSql = '';//查询条件
    	$searchParams = array('cure=true'); //搜索条件
    	
    	$count = $model->countList($whereSql);
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
    	$offset = ($p-1) * $limitRecord;    	
    	
    	$list = $model->getList($whereSql, $offset, $limitRecord);  
    	if($count > $limitRecord){
    		//生成分页html
    		$pageModel = new \Org\Com\Page;
    		$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false, 3);
    		$this->assign('pageHtml', $pageHtml);    		
    	}    	
    	
    	$this->assign('list', $list);    	
    	$this->assign('act', 'follow');
    	$this->meta_title = '我的关注';
    	$this->display();    	
    }
    /* 设置关注 */
    public function ajaxSetFollow(){    	
    	if(IS_AJAX && IS_GET){
    		$gid   = I('get.gid', 0, 'intval');    		
    		if(!$gid || $gid < 0){
    			$this->ajaxReturn(array( 'code' => 0, 'msg'  => '非法操作' ));
    		}
    		$this->ajaxReturn(D('Follow')->setFollow($gid));
    	}    	
    }    
    /* 取消关注 */
    public function ajaxCancelFollow(){    	
    	if(IS_AJAX){
    		$gid = I('get.gid', 0, 'intval');
    		$res = D('Follow')->cacelFollow($gid);
    		if($res > 0){
    			$this->ajaxReturn(array( 'code' => 1, 'msg'  => '取消关注成功' ));
    		}else{
    			$this->ajaxReturn(array( 'code' => 0, 'msg'  => '取消关注失败' ));
    		}
    	}    	
    }
    /****************************结束关注操作******************************/    
    
    /**
     * 异步上传头像
     */
    public function ajaxUploadImg(){
    	if(IS_AJAX){
    		$img_config = C('PICTURE_UPLOAD');
    		$img_config['savePath'] = 'Avatar/';
    		
    		$res = uploadImg('file1', $img_config);
    		if($res['ok']){
    			//更新头像
    			$this->model->updateField('head_pic',$res['file'][1]);
    		}
    		$this->ajaxReturn($res);
    	}		
    }   
    
}