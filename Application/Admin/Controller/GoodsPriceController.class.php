<?php
namespace Admin\Controller;
/**
 * 后台价格药材控制器
 * @author wpf
 * @date 2016-11-29下午5:03:48
 */
class GoodsPriceController extends AdminController { 
        
	/* 显示价格药材列表 */
	public function index(){		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		
		$data = D('GoodsPrice')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->getCates(false);
		$this->meta_title = '药材列表';
		$this->display();
	}
	/* 添加一个价格药材 */
	public function add(){
		if(IS_AJAX && IS_POST){
			$params = I('post.');
			//在这里判断传入的数据是否正确，（暂时不判断--后台）
			$model = D('GoodsPrice');
			$res = $model->operateData($params);
			$this->ajaxReturn(array('code' => $res, 'msg' => $model->getError()));
		}else{
			$this->getCates(false);			
			$this->meta_title = '添加药材价格';
			$this->display('edit');
		}
	}
	/* 编辑一个价格药材 */
	public function edit(){		
		$id = I('get.id', 0, 'intval');
		$info = M('GoodsPrice')->find($id);		
		$this->getCates(false);			
		$this->assign('info', $info);
		$this->meta_title = '编辑药材价格';
		$this->display();		
	}
	/* 根据价格药材id获取所有价格 */
	public function ajaxAllGoodsPriceH(){
		if(IS_AJAX && IS_GET){
			$goods_price_id = I('get.id', 0, 'intval');
			if($goods_price_id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '请选择药材'));
			$goodsHlist = M('GoodsPriceHistory')->where(array('goods_price_id' => $goods_price_id))->order('create_time desc')->select();	
			foreach ($goodsHlist as $k => $v){
				$goodsHlist[$k]['create_time'] = date('Y-m-d', strtotime($v['create_time']));
			}		
			$this->ajaxReturn(array('code' => 1,'data' => $goodsHlist));
		}
	}
	/* 查看趋势 */
	public function trend(){
		if(IS_AJAX && IS_GET){
			$goods_price_id = I('get.id', 0, 'intval');
			if($goods_price_id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '请选择药材'));			
			$data = M('GoodsPriceHistory')->field('price,create_time')->where(array('goods_price_id' => $goods_price_id))->order('create_time')->select();
			$retData = array();
			foreach ($data as $k => $v){
				$retData[$k][] = strtotime($v['create_time']).'000'+0;
				$retData[$k][] = (float)$v['price'];				
			}
			$this->ajaxReturn($retData);
		}else{
			$info = M('GoodsPrice')->find($id);	
			$this->meta_title = $info['goods_name'].'价格曲线图';
			$this->display();
		}
	}
	/* 根据分类id获取药材 */
	public function ajaxGetGoods(){
		if(IS_AJAX && IS_GET){
			$cate_id = I('get.cate_id', 0, 'intval');
			if($cate_id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '请选择分类'));
			$condition = array(array('name' => 'cate_id','value' => $cate_id));
			$goods = D('Goods')->getList($condition);
			$this->ajaxReturn(array('code' => 1,'data' => $goods['list']));
		}		
	}
}