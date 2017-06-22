<?php

namespace Admin\Controller;
/**
 * 前台注册用户管理器
 * @author wpf
 *
 */
class UserController extends AdminController {
	
	/* 获取用户列表 */	
	public function index(){
		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('User')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}		
		$this->meta_title = '用户列表';
		$this->display();
	}
	/* 编辑用户 */
	public function edit(){
		if(IS_AJAX && IS_POST){
			$data  = array();
			$error = '';
			$model = D('User');
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError(),'url' => U('index')));
		}else{
			$id = I('get.id', 0, 'intval');
			if((int)$id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));
			$info = D('User')->info($id);
			$this->assign('info', $info);
			$this->meta_title = '编辑用户';
			$this->display();
		}		
	}
	/* 添加用户 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$data  = array();
			$error = '';
			$model = D('User'); 
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
		}else{
			$this->meta_title = '添加用户';
			$this->display('edit');
		}		
	}
	/* 处理提交的数据--暂时不考虑过滤 */
	private function _operPostData(&$data, &$error){
		$post = I('post.');
		$data['id']		  = (int)$post['id'];
		$data['qq']		  = trim($post['qq']);
		$data['email']	  = trim($post['email']);
		$data['status']   = (int)$post['status'];
		$data['weixin']   = trim($post['weixin']);
		$data['mobile']   = trim($post['mobile']);
		$data['address']  = trim($post['address']);
		$data['realname'] = trim($post['realname']);
		
		if(!check_mobile($data['mobile'])) $error = '请输入正确的手机号';
		elseif(!$data['realname']) $error = '请输入用户姓名';
		elseif($data['status'] != 0 && $data['status'] != 1) $error = '无效操作'; 
	}
}
