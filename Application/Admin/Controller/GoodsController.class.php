<?php

namespace Admin\Controller;

/**
 * 后台药材控制器
 * @author wpf 2016/9/10
 */
class GoodsController extends AdminController { 
        
	/* 显示药材列表 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码		
		$condition = I('get.condition');
		
		$data = D('Goods')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->getCates(false);		
		$this->meta_title = '需求列表';
		$this->display();
	}
	/* 编辑一个药材 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$model = D('Goods');
			$data = $model->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
			if($model->add()) $this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '新增失败'));
		}else{
			$specs = D('Spec')->getList(array(), 500, 1);			
			$this->getCates(false);
			$this->assign('specs', $specs['list']);			
			$this->meta_title = '添加药材';
			$this->display('edit');
		}
	}
	/* 编辑一个药材 */
	public function edit(){
		$model = D('Goods');
		if(IS_AJAX && IS_POST){			
			$data = $model->create();			
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));			
			if($model->save($data)) $this->ajaxReturn(array('code' => 1,'msg' => '编辑成功','url' => U('index')));
			$this->ajaxReturn(array('code' => 0,'msg' => '编辑失败'));
		}else{
			$id = I('get.id', 0, 'intval');			
			$info  = $model->info($id);
			$specs = D('Spec')->getList(array(), 500, 1);	
			$this->getCates(false);
			$this->assign('specs', $specs['list']);			
			$this->assign('info',  $info);
			$this->meta_title = '编辑药材';
			$this->display();
		}
	}
	/* 显示药品趋势图 */
	public function trend($id){
		$model = D('Goods');
		if(IS_AJAX && IS_GET){		
			$lines = $model->getLineChart($id);//获取折线图数据
			$this->ajaxReturn($lines);
		}else{			
			$info = $model->info($id, 'goods_name');
			if($id < 0 || !$info){ $this->error('查询的药品不存在'); }
			$this->assign('info', $info);
			$this->meta_title = $info['goods_name']. '趋势';
			$this->display();
		}		
	}
	/* 异步获取自定义药品--暂时先不去对自定义药品进行分页 */
	public function ajaxCustomGoods(){
		if(IS_AJAX && IS_GET){
			$data = M('custom_goods')->field('id,goods_name')->where(array('status' => 1))->select();
			$this->assign('custom_list', $data);
			$this->display('custom_table');
		}
	}
	/* 根据药品id获取对应的规格 */
	public function ajaxGetSpecs($id){
		if(IS_AJAX && IS_GET){					
			$data = D('Goods')->getSpecs($id);
			$this->ajaxReturn($data);
		}
	}
}
