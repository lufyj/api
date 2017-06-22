<?php

namespace Admin\Controller;

/**
 * 后台药品规格控制器
 * @author wpf 2016/9/10
 */
class SpecController extends AdminController { 
        
	/* 显示规格列表 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('Spec')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}		
		$this->meta_title = '规格列表';
		$this->display();		
	}
	/* 添加规格 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$model = D('Spec');
			$data = $model->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
			if($model->add()) $this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '新增失败'));
		}else{
			$this->meta_title = '添加规格';
			$this->display('edit');
		}		
	}
	/* 编辑规格 */
	public function edit($id = 0){		
		$prop = D('Spec');		
		if(IS_AJAX && IS_POST){
			$data = $prop->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError));
			if($prop->save($data)) $this->ajaxReturn(array('code' => 1,'msg' => '编辑成功','url' => U('index')));
			$this->ajaxReturn(array('code' => 0,'msg' => '编辑失败'));
		}else{			
			$info = $prop->info($id);				
			$this->assign('info', $info);
			$this->meta_title = '编辑规格';			
			$this->display();
		}		
	}	
}
