<?php

namespace Admin\Controller;
/**
 * 后台发现管理器
 * @author liangweiwei
 */
class DiscoverController extends AdminController {
	
	public function index(){		
		
		$this->meta_title = '发现管理';
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$data = D('Discover')->getList($show_num, $page_num);
		$list = $data['list'];
		foreach ($list as $k => $v) {
			$list[$k]['title_types'] = $this->getName(intval($v['title_type']));
			//仓储
			if($v['title_type'] == '1'){
				$type = M('company_store')->where(['id' => $v['title_id']])->getField('type');
				$list[$k]['type_title'] = $this->getType($type);
			//物流
			}else if($v['title_type'] == '2'){
				$begin = M('company_delivery')->where(['id' => $v['title_id']])->getField('begin');
				$end = M('company_delivery')->where(['id' => $v['title_id']])->getField('end');
				$v['begin']= region($begin);
				$v['end']= region($end);
				if($v['begin'] === '0'){
					$begins = '全国';
				}else{
					$begins = M('region')->where(['REGION_CODE' => $v['begin']])->getField('REGION_NAME');
				}
				if($v['end'] ==='0'){
					$ends = '全国';
				}else{
					$ends = M('region')->where(['REGION_CODE' => $v['end']])->getField('REGION_NAME');
				}
				$list[$k]['type_title'] = $begins.'-'.$ends;
			//检测
			}else if($v['title_type'] == '3'){
				$list[$k]['type_title'] = M('company_process')->where(['id' => $v['title_id']])->getField('content');
			//代加工
			}else if($v['title_type'] == '4'){
				$list[$k]['type_title'] = M('company_process')->where(['id' => $v['title_id']])->getField('content');
			//药膳
			}else if($v['title_type'] == '5'){
				$list[$k]['type_title'] = M('articles')->where(['id' => $v['title_id']])->getField('title');
			}

		}
		$data['list'] = $list;
		$this->assign('data',$data);
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->display();
	}
	public function add(){		
		$this->meta_title = '发现管理';
		if(IS_POST){
			$data['title_type'] = I('post.title_type','',trim);
			if(!$data['title_type']){$this->error('发现分类不能为空');}
			$data['title_id'] = I('post.title_id','',trim);
			if(!$data['title_id']){$this->error('发现分类不能为空');}
			if(M('discover')->where($data)->find()){
				$this->error('已经添加过了,请选择其他添加');
			}
			if(M('discover')->add($data)){
				$this->success('添加成功',U('index'));
			}else{
				$this->error('添加失败');
			}
		}
		$this->display('');
	}
	/*获取指定分类下的信息*/ 
	public function ajaxGetTypes(){
		$table_code = I('post.table_code');
		if(!$table_code){$this->ajaxReturn(array( 'code' => 0, 'msg' => '未选择分类'));}
		//获取表名
		$tableName=$this->getTableName(intval($table_code));
		//仓储
		if($tableName == 'company_store'){
			$list =M("$tableName")->field('id,type,size')->order('id desc')->select();
			foreach ($list as $k => $v) {
				if($v['type'] == '0'){
					$list[$k]['type'] ='普通';
				}else if($v['type'] == '1'){
					$list[$k]['type'] ='冷藏';
				}else if($v['type'] == '2'){
					$list[$k]['type'] ='保温恒温';
				}else if($v['type'] == '3'){
					$list[$k]['type'] ='特种';
				}else if($v['type'] == '4'){
					$list[$k]['type'] ='气调';
				}
			}
		//物流
		}else if($tableName == 'company_delivery'){
			$list =M("$tableName")->field('id,begin,end')->order('id desc')->select();
			foreach($list as $k => $v){
				$v['begin']= region($v['begin']);
				$v['end']= region($v['end']);
				if($v['begin'] === '0'){
					$list[$k]['begin'] = '全国';
				}else{
					$list[$k]['begin'] = M('region')->where(['REGION_CODE' => $v['begin']])->getField('REGION_NAME');
				}
				if($v['end'] ==='0'){
					$list[$k]['end'] = '全国';
				}else{
					$list[$k]['end'] = M('region')->where(['REGION_CODE' => $v['end']])->getField('REGION_NAME');
				}
			}
		}else if($tableName == 'company_check'){
		//检测
			$list =M('company_process')->where(['type' => '2'])->field('id,content')->order('id desc')->select();
		//代加工
		}else if($tableName == 'company_process'){
			$list =M("$tableName")->where(['type' => '1'])->field('id,content')->order('id desc')->select();
		//药膳
		}else if($tableName == 'articles'){
			$list =M("$tableName")->where(['cate_id' => '44'])->field('id,title')->order('id desc')->select();
		}
		$this->ajaxReturn($list);
	}
	//推荐
	public function theme(){
		$data = I('get.');
		$typeName = $this->getTableName($data['type']);
		if(IS_GET){
			//仓库
			if($data['type'] == '1'){
				if(!M("$typeName")->where(['id' => $data['title_id']])->getField('img')){
					$this->error('没有图片不能推荐');
				}else{
					//走推荐流程
					if($data['status'] == '1'){//取消推荐
						if(M('discover')->where(['id' => $data['id']])->setField('theme_status','0')){
							$this->success('取消推荐成功');
						}
					}else{
						//判断推荐主题是否超过6个
						$num = M('discover')->where(['theme_status' => '1'])->count();
						if($num == '6'){
							$this->error('最多推荐主题为6个');
						}
						if(M('discover')->where(['id' => $data['id']])->setField('theme_status','1')){
							$this->success('推荐成功');
						}
					}
				}
			//药膳
			}else if($data['type'] == '5'){
				$map['id'] = $data['title_id'];
				$map['cate_id'] = '44';
				if(!M("$typeName")->where($map)->getField('thumb')){
					$this->error('没有图片不能推荐');
				}else{
					//走推荐流程
					if($data['status'] == '1'){//取消推荐
						if(M('discover')->where(['id' => $data['id']])->setField('theme_status','0')){
							$this->success('取消推荐成功');
						}
					}else{
						//判断推荐主题是否超过6个
						$num = M('discover')->where(['theme_status' => '1'])->count();
						if($num == '6'){
							$this->error('最多推荐主题为6个');
						}
						if(M('discover')->where(['id' => $data['id']])->setField('theme_status','1')){
							$this->success('推荐成功');
						}
					}
				}
			//除了仓库 药膳 其他3个 都是去 company_info表中 查询logo
			}else{
				if($typeName == 'company_check'){
					$user_id = M("company_process")->where(['id' => $data['title_id']])->getField('user_id');
				}else{
					$user_id = M("$typeName")->where(['id' => $data['title_id']])->getField('user_id');
				}	
				if($user_id){
					if(!M('company_info')->where(['user_id' => $user_id])->getField('logo')){
						$this->error('没有图片不能推荐');
					}else{
						//走推荐流程
						if($data['status'] == '1'){//取消推荐
							if(M('discover')->where(['id' => $data['id']])->setField('theme_status','0')){
								$this->success('取消推荐成功');
							}
						}else{
							//判断推荐主题是否超过6个
							$num = M('discover')->where(['theme_status' => '1'])->count();
							if($num == '6'){
								$this->error('最多推荐主题为6个');
							}
							if(M('discover')->where(['id' => $data['id']])->setField('theme_status','1')){
								$this->success('推荐成功');
							}
						}
					}
				}else{
					$this->error('user_id不存在');
				}
				
			}
		}	
	}
	/* 删除一条发现信息 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));
			$res = M('Discover')->delete($id);
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}
	//获取表名
    private function getTableName($table_code){

        switch($table_code){
            case 1:
                $tableName='company_store';
                break;
            case 2:
                $tableName='company_delivery';
                break;
            case 3:
                $tableName='company_check';
                break;
            case 4:
                $tableName='company_process';
                break;
            case 5:
                $tableName='articles';
                break;
            default:
                $tableName='company_store';
                break;
        }
        return $tableName;    
	}
	//获取分类名字
    private function getName($table_code){

        switch($table_code){
            case 1:
                $tableName='仓储';
                break;
            case 2:
                $tableName='物流';
                break;
            case 3:
                $tableName='检测';
                break;
            case 4:
                $tableName='代加工';
                break;
            case 5:
                $tableName='药膳';
                break;
        }
        return $tableName;    
	}
	//获取仓库类型
    private function getType($type){

        switch($type){
        	case 0:
                $typeName='普通';
                break;
            case 1:
                $typeName='冷藏';
                break;
            case 2:
                $typeName='保温恒温';
                break;
            case 3:
                $typeName='特种';
                break;
            case 4:
                $typeName='气调';
                break;
        }
        return $typeName;    
	}
}
