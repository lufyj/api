<?php
namespace Home\Controller;

/**
 * 前台关于我们控制器
 * @author wpf
 */
class AboutController extends HomeController {
	
	/* 关于我们首页 */
    public function index(){
		$this->assign('act','index');
    	$this->meta_title = '关于我们';
    	$this->display();
    }    
    /* 意见反馈 */
    public function suggest(){
    	if(IS_AJAX && IS_POST){
    		$post = I('post.');
			$ret['code'] = 0;
			
    		if(!$post['code']){
    			$ret['msg'] = '请填写验证码';    			
    			$this->ajaxReturn($ret);
    		}
    		if(!$post['question']){
    			$ret['msg'] = '请填写意见';
    			$this->ajaxReturn($ret);    			
    		}
    		
    		$check_verify = check_verify($post['code']);
			if (!$check_verify) {
				$ret['msg'] = '验证码错误或已过期';
    			$this->ajaxReturn($ret); 
			}
    		foreach($post as $k=>$v){
				$post[$k]=clearXSS($v);
			}
			$post['create_time'] = time();
			$post['update_time'] = time();
    		$res = M('feedback')->data($post)->add();
    		if((int)$res > 0){
    			$ret = array( 'code' => 1, 'msg'  => '提交成功，稍后我们会与您取得联系' );    			
    		}else{
    			$ret['msg'] = '网络连接超时，请您稍后重试';
    		}
    		$this->ajaxReturn($ret);    		
    	}else{    		
    		$this->assign('act', 'suggest');
    		$this->meta_title = '意见反馈';
    		$this->display();
    	}    	
    }
    /** 
     * 最新公告-42 
     * 由于此页面没有复杂逻辑，其他地方也不会用到，就扔到控制器里面处理
     * */
    public function platform(){
    	
    	$limitRecord = 15; // 单页最多几条数据
    	$p = I('get.p', 0, 'intval'); // 当前页码
    	
    	//查询条件
    	$condition = array(
    		'cate_id' => 43,
    		'status'  => 1,    			
    	);
    	$count = M('Articles')->where($condition)->count();
    	$totalPage = ceil($count / $limitRecord);
    	if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }    	
    	$offset = ($p-1) * $limitRecord;
    	$limit = " {$offset},{$limitRecord}";
    	
    	$list = M('Articles')->field('id,title,create_time')
    		->where($condition)
    		->order('id desc')
    		->limit($limit)
    		->select();
    	
    	if($count > $limitRecord){
    		//生成分页html
    		$pageModel = new \Org\Com\Page;
    		$pageHtml = $pageModel->show($count, $limitRecord, $p, $_SERVER['path_info'].'?'.implode('&', $searchParams), false, 3);
    		$this->assign('pageHtml', $pageHtml);
    	}
  
    	$this->assign('pager', $pager);
    	$this->assign('list', $list);
    	$this->assign('act','platform');
    	$this->meta_title = '最新公告';
    	$this->display();
    }
    /* 最新公告详情页 */
    public function detail(){
    	$id = I('get.id', 0, 'intval');
    	//查询条件
    	$condition = array(
    		'cate_id' => 43,
    		'status'  => 1,
    	);
    	$articleModel = M('Articles');
    	$info = $articleModel->where($condition)->field('title,author,content,create_time')->find($id);
    	//获取相关文章
    	if($info){
    		$fields = 'id,title';
    		//获取上一篇
    		$nextInfo = $articleModel->field($fields)->where($condition + array('id' => array('lt', $id)))->order('id desc')->find();
    		//获取下一篇
    		$lastInfo = $articleModel->field($fields)->where($condition + array('id' => array('gt', $id)))->find();
    		$this->assign('lastInfo', $lastInfo);
    		$this->assign('nextInfo', $nextInfo);
    	}
    	$this->assign('info', $info);
    	$this->assign('act','platform');
    	$this->meta_title = $info['title'];
    	$this->display();    	
    }    
    /* 关于我们---“企业文化”*/
    public function business_culture(){
		$this->assign('act','business_culture');
    	$this->meta_title = '企业文化';
    	$this->display();
    }
     /* 关于我们---“发展历程”*/
    public function expansion_path(){
		$this->assign('act','expansion_path');
    	$this->meta_title = '发展历程';
    	$this->display();
    }
     /* 关于我们---“团队风采”*/
    public function team_mien(){
		$this->assign('act','team_mien');
    	$this->meta_title = '团队风采';
    	$this->display();
    } 
     /* 关于我们---“媒体报道”*/
    public function media(){
		$this->assign('act','media');
    	$this->meta_title = '媒体报道';
    	$this->display();
    } 
     /* 关于我们---“联系我们”*/
    public function contact(){
		$this->assign('act','contact');
    	$this->meta_title = '联系我们';
    	$this->display();
    } 
     /* 关于我们---“诚聘英才”*/
    public function join_us(){
		$this->assign('act','join_us');
    	$this->meta_title = '诚聘英才';
    	$this->display();
    } 
}