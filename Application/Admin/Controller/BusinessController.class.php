<?php

namespace Admin\Controller;
/**
 * 认证企业业务权限管理器
 * Author jingwei
 * Date 2016-11-25
 *
 */
class BusinessController extends AdminController {
	
	/* 获取已认证用户列表 */
	public function index(){
		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		$data = D('CompanyConfirm')->getConfirmList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->meta_title = '业务管理';
		$this->display('Business/index');
	}

	//编辑管理业务权限
	public function edit(){
		if(IS_AJAX && IS_GET){
			$model = D('CompanyConfirm');
			$code = $model->operateData();
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError(),'url' => U('Business/index')));
		}else{
			$id=I('get.id',0,'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));
			$info = D('CompanyConfirm')->info($id);
			$this->assign('info', $info);
			$this->meta_title = '编辑业务';
			$this->display('Business/edit');
		}
	}

}
