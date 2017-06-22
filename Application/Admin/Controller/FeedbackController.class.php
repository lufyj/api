<?php
namespace Admin\Controller;

/**
 * 意见反馈控制器
 * @author wpf
 */
class FeedbackController extends AdminController {   
	
    /* 显示意见反馈 */    
    public function index(){
    	
    	$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
    	$page_num = I('get.page_num', 1, 'intval');//页码
    	$condition = I('get.condition');
    	
    	$data = D('Feedback')->getList($condition, $show_num, $page_num);    	
    	$this->assign('data', $data);
    	
    	if(IS_AJAX && IS_GET){
    		$this->display('table');
    		exit;
    	}
    	$this->meta_title = '用户列表';
    	$this->display();    	
    }
    /* 查看某个反馈 */   
    public function look(){
    	$id = I('id', 0, 'intval');
    	$model = D('Feedback');
    	
    	if(IS_AJAX && IS_POST){
    		$reply = I('post.reply');
    		if(!$reply){ $this->ajaxReturn(array('code' => 0,'msg' => '回复内容不能为空')); }
 			$data = array('id' => $id,'reply' => $reply);
 			
    		$code = $model->reply($data);    		
    		$this->ajaxReturn(array('code' => $code,'msg' => $model->getError(),'url' => U('index')));
    	}else{
    		$info = $model->info($id);
    		$this->assign('info',$info);
    		$this->meta_title = '反馈详情';
    		$this->display();
    	}        	
    }
    /* 删除当前反馈回复信息 */
    public function del(){
    	if(IS_AJAX && IS_GET){
    		$id = I('get.id', 0, 'intval');
    		if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '无效操作'));
    		$res = M('Feedback')->delete($id);
    		if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
    		$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
    	}
    }
}
