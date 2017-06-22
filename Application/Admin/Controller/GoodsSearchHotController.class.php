<?php

namespace Admin\Controller;

/**
 * 后台药材控制器
 * @author wpf 2016/9/10
 */
class GoodsSearchHotController extends AdminController { 
        
	/* 显示热门搜索列表 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码		
		$condition = I('get.condition');
		$data = D('SearchHot')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		$this->assign('act','index');
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->getCates(false);		
		$this->meta_title = '热门药材列表';
		$this->display();
	}
	/* 显示自定义热门搜索列表 */
	public function custom_index(){
		$show_num = I('get.show_num', 30);//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码		
		$condition = I('get.condition');
		$data = D('SearchCustom')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		$this->assign('act','custom_index');
		if(IS_AJAX && IS_GET){
			$this->display('custom_table');
			exit;
		}
		$this->getCates(false);		
		$this->meta_title = '自定义热门药材列表';
		$this->display('');
	}
	/* 删除一个药材 */
	public function del(){
		$goods_name= I('get.goods_name','',trim);
		$alias_name= I('get.alias_name','',trim);
		$data['goods_name'] = $goods_name;
		if($alias_name){
			$data['alias_name'] = $alias_name;
		}else{
			$data['alias_name'] = array('eq','');
		}
		if(M('SearchHot')->where($data)->delete()){
			$this->success('删除成功');
		}else{
			$this->success('删除失败');
		}
	}
	//修改权重
	public function weight(){
		$goods_name = I('post.goods_name','',trim);
		$alias_name= I('get.alias_name','',trim);
		if($alias_name){
			$data['alias_name'] = $alias_name;
		}
		if($goods_name){
			$data['goods_name'] = $goods_name;
			$date['goods_hot'] = I('post.weight');
			if(!$date['goods_hot']){$this->error('权重只能为数字');}
			if(M('SearchHot')->where($data)->save($date)){
				$this->success('修改成功',U('index'));
			}else{
				$this->success('修改失败',U('index'));
			}
		}else{
			$goods_name= I('get.goods_name','',trim);
			$data['goods_name'] = $goods_name;
			$data = M('SearchHot')->where($data)->find();
			$this->assign('data', $data);
			$this->display('edit');
		}
	}
	/*
	*	删除用户自定义输入关键词
	*/
	public function custom_del(){
		$id = I('get.id');
		$ids  = explode(',',$id);
		$data['id'] = array('in',$ids);
		if(M('SearchCustom')->where($data)->delete()){
			$this->success('删除成功');
		}else{
			$this->success('删除失败');
		}
	}
}
