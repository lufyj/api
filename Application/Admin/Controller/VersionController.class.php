<?php

namespace Admin\Controller;

class VersionController extends AdminController {	
	
	/* 显示版本列表 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
        $data = D('Version')->getList($show_num, $page_num);
		$this->assign('data',$data);
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->meta_title = '版本列表';
		$this->display();
    }
    /* 新增版本信息 */
    public function add(){
    	if(IS_AJAX && IS_POST){
    		$data  = array();
			$error = '';
			$model = D('version'); 
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError()));
    	}else{
    		$this->meta_title = '添加用户';
			$this->display();
    	}
    }
	/* 编辑版本信息 */
    public function edit(){
    	if(IS_AJAX && IS_POST){
    		$data  = array();
			$error = '';
			$model = D('version'); 
			$this->_operPostData($data, $error);
			if($error) $this->ajaxReturn(array('code' => 0,'msg' => $error));
			$code = $model->operateData($data, $error);
			$this->ajaxReturn(array('code' => $code,'msg' => $model->getError(),'url' => U('index')));
    	}else{
    		$id = I('get.id', 0, 'intval');
			if((int)$id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));
			$info = M('version')->where(['id' => $id])->find();
			$this->assign('info', $info);
			$this->meta_title = '编辑用户';
			$this->display('add');
    	}
    }
    /* 删除一条版本信息 */
    public function del(){
    	if(IS_AJAX && IS_GET){
    		$id = I('get.id', 0, 'intval');
    		if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));
    		$res = M('Version')->delete($id);
    		if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
    		$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
    	}
    }
    private function _operPostData(&$data, &$error){
    	$post = I('post.');
    	$data['id'] = (int)$post['id'];
    	$data['version_num'] = trim($post['version_num']);
    	$data['download_address'] = trim($post['download_address']);
    	if(!$data['version_num']) $error = '版本号未输入';
		elseif(!$data['download_address']) $error = '下载地址未输入';
		elseif(!preg_match("/^\d+(\.\d+){1,2}+$/",$data['version_num'])) $error = '版本号不合法'; 
		elseif(!preg_match("/^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+/",$data['download_address'])) $error = '下载地址不合法'; 
    }
}