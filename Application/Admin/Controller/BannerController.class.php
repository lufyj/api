<?php

namespace Admin\Controller;

/**
 * Banner控制器
 * @author wpf
 */
class BannerController extends AdminController {
	
	/* 显示Banner列表 */
	public function index(){		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('Banner')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}		
		$this->meta_title = '需求列表';
		$this->display();
    }
    /* 添加banner */
    public function add(){
    	if(IS_AJAX && IS_POST){
    		$model = D('Banner');
    		$data = $model->create();
    		if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
    		$res = $model->add();
    		if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
    		else{
    			$error = $model->getError();
    			if(strpos($error, '图片') === false) $error = '新增失败';
    			$this->ajaxReturn(array('code' => 0,'msg' => $error));
    		}    		
    	}else{
    		$this->meta_title = '新增Banner';
    		$this->display('edit');
    	}
    }
    /* 编辑banner */
    public function edit(){    	
    	if(IS_AJAX && IS_POST){
    		$model = D('Banner');
    		$data = $model->create();
    		if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
    		$res = $model->save();
    		if($res !== false) $this->ajaxReturn(array('code' => 1,'msg' => '编辑成功','url' => U('index')));
    		$this->ajaxReturn(array('code' => 0,'msg' => '编辑失败'));
    	}else{
    		$id = I('get.id', 0, 'intval');    		
    		$info = M('Banner')->find($id);
    		$this->assign('info', $info);
    		$this->meta_title = '编辑Banner';
    		$this->display();
    	} 	
    }
    /* 删除一条供应信息 */
    public function del(){
    	if(IS_AJAX && IS_GET){
    		$id = I('get.id', 0, 'intval');
    		if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));
    		$res = M('Banner')->delete($id);
    		if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
    		$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
    	}
    }
}
