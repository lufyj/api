<?php

namespace Admin\Controller;
/**
 * 广告机管理器
 * @author wpf
 */
class GoodsHotController extends AdminController {

	/* 首页展示 */
	public function index(){
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
        $data = D('GoodsHot')->getLists($show_num, $page_num);
		$this->assign('data',$data);
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->meta_title = '分类热门药材';
		$this->display();
	}
	/* 新增一个分类热门药品 */
	public function add(){
		if(IS_AJAX && IS_POST){			
			$data  = array();
			$error = '';
			$model = D('GoodsHot');
			$this->_operPostData($data, $error);
			if($error) $this->error("$error");
			$code = $model->operateData($data, $error);
			if($code == '1'){
				$this->success('新增成功',U('index'));
			}else if($code == '2'){
				$this->error('该分类已经添加过了');
			}else{
				$this->error('新增失败');
			}
		}else{
			$cate = M('goods_category')->where(['status' => 1 ])->Field('id,title')->select();
			$num = C('GOODS_MAX_NUM');
			$this->meta_title = '添加热门药材';
			$this->assign('cate',$cate);
			$this->assign('max_num',$num);
			$this->display('edit');
		}
	}
	/* 编辑一个分类热门药品 */
	public function edit(){
		if(IS_AJAX && IS_POST){
			$data  = array();
			$error = '';
			$model = D('GoodsHot');
			$this->_operPostData($data, $error);
			if($error) $this->error("$error");
			$code = $model->operateData($data, $error);
			if($code == '1'){
				$this->success('修改成功',U('index'));
			}else{
				$this->error('修改失败');
			}
		}else{
			$cate = M('goods_category')->where(['status' => 1 ])->Field('id,title')->select();
			$num = C('GOODS_MAX_NUM');
			$id = I('get.id', 0, 'intval');
			if((int)$id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效参数'));	
			$info = M('GoodsHot')->where(['id' => $id])->find();	
			$goods = D('GoodsHot')->getGoods($id);				
			$this->assign('goods',$goods);
			$this->assign('max_num',$num);
			$this->assign('info',$info);
			$this->assign('cate',$cate);
			$this->meta_title = '编辑热门药材';
			$this->display();
		}
	}
	//处理提交数据 -- 暂时不做过滤
	private function _operPostData(&$data, &$error){
		$post = I('post.');
		$data['id']		  = (int)$post['id'];
		$data['cate_id'] = $post['cate_id'];
		$data['goods_id'] = $post['goods_id'];
		if(!$data['cate_id'] && !$data['goods_id'])$error = '请选择热门药材';
	}
	/* 删除一条热门分类药材信息 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));
			$res = M('GoodsHot')->delete($id);
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}
	/*获取指定分类下的全部药品*/ 
	public function ajaxGetGoods(){
		if(IS_AJAX && IS_GET){
			$cate_id = I('get.cate_id');
			if(!$cate_id){$this->ajaxReturn(array( 'code' => 0, 'msg' => '未选择分类'));}
			$goods['cate'] =M('goods_category')->where(['id' => $cate_id ])->Field('id,title')->find();
			$goods['goods'] = M('goods')->where(['cate_id' => $cate_id])->Field('id,goods_name')->order('length(goods_name) asc')->select(); 
			$this->ajaxReturn($goods);
		}
	}
}
