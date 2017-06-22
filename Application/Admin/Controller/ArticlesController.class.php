<?php

namespace Admin\Controller;

/**
 * 后台行情资讯控制器
 * @author wpf
 */
class ArticlesController extends AdminController {
	
	/* 显示行情资讯列表页 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('Articles')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$cates = D('Category')->getTree(0, 'id,title');
		$this->assign('cates', $cates);
		$this->meta_title = '用户列表';
		$this->display();		
	}
	/* 添加行情 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$model = D('Articles');
			$data = $model->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
			if($model->add()) $this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '新增失败'));
		}else{
			$cates = D('Category')->getTree(0, 'id,title');
			$this->assign('cates', $cates);
			$this->meta_title = '添加行情';
			$this->display('edit');
		}
	}
	/* 编辑行情 */
	public function edit($id = 0){
		$model = D('Articles');
		if(IS_AJAX && IS_POST){
			$data = $model->create();
			if(!$data) $this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
			if($model->save($data)) $this->ajaxReturn(array('code' => 1,'msg' => '编辑成功','url' => U('index')));
			$this->ajaxReturn(array('code' => 0,'msg' => '编辑失败'));
		}else{			
			$cates = D('Category')->getTree(0, 'id,title');
			$info = $model->info($id);			
			$this->assign('info', $info);
			$this->assign('cates', $cates);
			$this->meta_title = '编辑行情';
			$this->display('edit');
		}
	}
	/* 新增或编辑文章 */
	public function edit2($id = 0){		
		$model = D('Articles');		
		if(IS_AJAX && IS_POST){
			//有可能添加或者编辑			
			$data = $model->create();
			if($data){
				if($id > 0){
					if($model->save($data) !== false) {						
						$this->ajaxReturn(array('code' => 1,'msg' => '更新成功'));
					} else {					
						$this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
					}
				}else{
					if($model->add()){
						$this->ajaxReturn(array('code' => 1,'msg' => '新增成功'));
					}else{						
						$this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
					}
				}	
			}else{
				$this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));				
			}			
		}else{			
			//获取栏目分类
			$cate_list = D('Category')->getTree(0, 'id,title'); 
			if($id > 0){				
				$info = $model->info($id);
				if(!$id || !$info){
					$this->error('编辑的文章不存在', U('index'));
				}
				$this->assign('info', $info);
				
				$this->meta_title = '编辑文章';
			}else{
				$this->meta_title = '新增文章';
			}
			$this->assign('cate_list', $cate_list);
			$this->display();
		}				
	}	
	/* 删除一篇文章 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			$cate_id = M('Articles')->where(['id' => $id])->getField('cate_id');
			//如果删除的信息cate_id 是属于药膳的话 那就将对应的发现表里的数据也删除
			if($cate_id == '44'){
				M('discover')->where(['title_id' => $id])->delete();
			}
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));			
			$res = M('Articles')->where(array('id' => $id))->delete();
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}	
}