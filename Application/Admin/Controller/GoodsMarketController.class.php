<?php

namespace Admin\Controller;

/**
 * 后台药品行情控制器
 * @author wpf
 */
class GoodsMarketController extends AdminController {

	/* 药品行情列表页 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('GoodsMarket')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}		
		$this->meta_title = '需求列表';
		$this->display();
	}
	/* 添加一个行情 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$model = D('GoodsMarket');
			$data = $model->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
			if($model->add()) $this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '新增失败'));
		}else{
			$this->getCates(false);			
			$this->meta_title = '添加行情';
			$this->display('edit');
		}
	}
	/* 编辑规格 */
	public function edit($id = 0){
		$prop = D('GoodsMarket');
		if(IS_AJAX && IS_POST){
			$data = $prop->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError));
			if($prop->save($data)) $this->ajaxReturn(array('code' => 1,'msg' => '编辑成功','url' => U('index')));
			$this->ajaxReturn(array('code' => 0,'msg' => '编辑失败'));
		}else{
			$info = $prop->info($id);
			$this->getCates(false);
			//根据所属分类id获取对应的药品列表
			$glist = D('Goods')->getListByCateId($info['cate_id']);
			$this->assign('glist', $glist);			
			$this->assign('info', $info);
			$this->meta_title = '编辑规格';
			$this->display();
		}
	}
	/* 删除一条求购信息 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));
			$res = M('GoodsMarket')->delete($id);
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}	
	/**
	 * 根据药品分类id获取药品
	 */
	public function ajaxGetGoodsByCateId(){
		if(IS_AJAX){
			//判断分类id是否存在
			$id = I('get.id');
			if(!$id || (int)$id <= 0){
				$this->error('该药品不存在');				
			}
			//根据分类id获取药品列表
			$glist = D('Goods')->getListByCateId($id);
			$this->ajaxReturn(array(
				'status' => 1,
				'data'	 => $glist 				
			));
		}		
	}
}
