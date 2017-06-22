<?php

namespace Admin\Controller;

/**
 * 供应列表
 * @author wpf
 *
 */
class SupplyController extends AdminController{

	/**
	 * 显示供应列表
	 */
	public function index(){
		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码

		//当点击编辑页的时候保存住当前页码，此处Cookie关闭浏览器时自动清除
		if (Cookie('__rpage__')) {
			$page_num = Cookie('__rpage__');
			Cookie('__rpage__', null);
		}

		$all_list = D('Supply')->getList(I('get.condition'), $show_num, $page_num);
		$cate_list = D('GoodsCategory')->getList();
		//记录当前页
		Cookie('__page__', $all_list['_page_num']);
		$this->assign('show_num', $show_num);
		$this->assign('page_num', $all_list['_page_num']);
		$this->assign('total_page', $all_list['_total_page']);
		$this->assign('all_count', $all_list['_count']);
		$this->assign('list', $all_list['_list']);
		$this->assign('cate_list', $cate_list);
		$this->meta_title = '供应列表';
		if (IS_AJAX) {
			$this->display('table');
			exit;
		}
		$this->display();
	}
	/* 删除一条供应信息 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));
			$res = M('Supply')->delete($id);
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}
	/* 供应统计 */
	public function analysis(){
		$this->meta_title = '供应统计';
		$this->display();
	}

	//添加供应信息
	public function add()
	{
		$this->_commonData();
		$this->meta_title = '添加供应信息';
		$this->display();
	}

	//编辑供应信息
	public function edit()
	{
		$id=I('get.id',0,'intval');
		$supplyInfo=M('Supply')->where('id='.$id)->find();
		if($supplyInfo){
			$region=A('Region');
			if($supplyInfo['origin_code']){
				$supplyInfo['origin']=$region->addressHtml($supplyInfo['origin_code']);
			}else{
				$supplyInfo['origin']['proInfo']=$region->getProHtmlById(0);
			}

			$supplyInfo['supply']=$region->addressHtml($supplyInfo['supply_code']);
			//获取商品对应的规格信息
			$supplyInfo['specs_list'] = D('Goods')->getSpecs($supplyInfo['goods_id']);
			$this->assign('info',$supplyInfo);
		}else{
			$this->error('编辑失败');
		}

		$this->_commonData();
		$this->meta_title = '编辑供应信息';
		$this->display();
	}


	//保存供应信息
	public function save(){

		if(IS_POST){
			$post = I('post.');
			if(clearXSS($post['contacts']) && check_mobile($post['mobile'])){
				$mobile= trim($post['mobile']);
				$contacts= clearXSS($post['contacts']);
				$supply=D('Supply');
				$info=$supply->operateData(intval($post['id']),$mobile,$contacts);
				if($info){
					$uid=is_array($info)?$info['uid']:$info;
					$isExists=is_array($info)?$info['id']:false;
				}else{
					$this->error('保存失败');
				}

				//过滤提交的供应信息
				$data = array();
				$post['uid']=$uid;
				$data =$supply->filterSupplyInfo($post);
				if(!is_array($data)){
					$this->error($data);
				}
				$status=$supply->saveSupply($isExists,$data);
				if($status){
					$this->success('保存成功',U('Supply/index'));
				}else{
					$this->error('保存失败');
				}
			}else{
				$this->error('数据不完整');
			}
		}else{
			$this->error('请求不正确');
		}
	}

	//精简分类字段名字(仅供处理分类字段信息使用)(jingwei)
	private function _simplefield($cate)
	{
		$newCate = array();
		foreach($cate as $k => $v){
			$tmp = array();
			$tmp['i'] = $v['id'];
			$tmp['t'] = $v['title'];
			if (is_array($v['_child'])) {
				$c_tmp = array();
				foreach ($v['_child'] as $c_k => $c_v) {
					$children = array();
					$children['ci'] = $c_v['id'];
					$children['cg'] = $c_v['goods_name'];
					$c_tmp[] = $children;
				}
				$tmp['c'] = $c_tmp;
			}
			$newCate[] = $tmp;
		}
		return $newCate;
	}

	//上传供应商品图片
	public function upImg()
	{
		$img_config = C('PICTURE_UPLOAD');
		$img_config['savePath'] = 'Supply/';
		$res = uploadImg('supply', $img_config, array(array(200, 200)));
		if ($res['code'] == 1) {
			$data['status'] = 1;
			$data['path'] = $res['file'][1];
			$data['msg'] = '上传成功';
		} else {
			$data['status'] = 0;
			$data['msg'] = $res['msg'];
		}

		$this->ajaxReturn($data);
	}

	/*
	 * 删除已上传图片
	 * @return 1 请求非法  2  参数不正确 3 删除失败  4  删除成功
	 */
	public function delImg()
	{
		if (IS_AJAX) {
			$myPath = explode(",",clearXSS(I('post.path')));
			if (is_array($myPath) && !empty($myPath)) {

				$type = array('jpg', 'png', 'jpeg');
				foreach($myPath as $k=>$v){
					$path='';
					$pathBase='';
					$fileType='';
					$fileTypeBase='';
					$path=$v;
					$pathBase = getImgs($path, 2);
					$path = $_SERVER['DOCUMENT_ROOT'] . $path;
					$pathBase = $_SERVER['DOCUMENT_ROOT'] . $pathBase;
					if(is_file($path) && is_file($pathBase)){
						$fileType = pathinfo($path, PATHINFO_EXTENSION);
						$fileTypeBase = pathinfo($pathBase, PATHINFO_EXTENSION);
						if(in_array($fileType, $type) && in_array($fileTypeBase, $type)){
							unlink($path);
							unlink($pathBase);
						}else{
							$rs['status'] = 3;
							$rs['msg'] = '删除失败';
							$this->ajaxReturn($rs);
						}
					}else{
						$rs['status'] = 2;
						$rs['msg'] = '参数不正确';
						$this->ajaxReturn($rs);
					}
				}

				$rs['status'] = 4;
				$rs['msg'] = '删除成功';
				$this->ajaxReturn($rs);
			} else {
				$rs['status'] = 2;
				$rs['msg'] = '参数不正确';
				$this->ajaxReturn($rs);
			}
		} else {
			$rs['status'] = 1;
			$rs['msg'] = '请求非法';
			$this->ajaxReturn($rs);
		}
	}

	//编辑/添加页面基本数据
	private function _commonData(){
		$cacheData = S('Data_Cache');
		if(!$cacheData){
			//获取所有一级分类以及子分类
			$cates = D('Goods')->getAllCatsForNav();
		}else{
			$cates = $cacheData['cates'];
		}
		$cateJson = json_encode($this->_simplefield($cates));
		$this->assign('cateJson', $cateJson);

		$region = A('Region');
		$regionData = $region->getRegion(1);
		$this->assign('provice', $regionData);
	}
}
