<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wpf
// +----------------------------------------------------------------------

namespace Admin\Controller;

class IndexController extends AdminController {
	/* 后台首页 */
    public function index(){
    	if(IS_AJAX && IS_POST){
    		$model = D('Member');
    		$data = $model->create($_POST, 2);
    		if($data !== false){    			
    			if($model->save($data) !== false) $this->ajaxReturn(array('code' => 1,'msg' => '更新成功'));
    			$this->ajaxReturn(array('code' => 0,'msg' => '更新失败'));
    		}else{
    			$this->ajaxReturn(array('code' => 0,'msg' => $model->getError()));
    		}	
    	}else{
    		$info = M('Member')->field('uid,nickname,head_pic,mobile,qq,last_login_time')->find(UID);
    		$this->assign('info', $info);
    		$this->meta_title = '后台首页';
    		$this->display();
    	}    	
    }
    
    /**
     * 删除首页面的缓存
     * @return [type] [description]
     */
    public function ajaxclearcache(){
    	if (IS_AJAX) {
    		S('Index_Cache', null);
    		$res = array('status'=>1, 'msg'=>'清除缓存成功，刷新前台首页面看看吧:)');
    		/* $html_cache_path = HTML_PATH;
    		if ($file_exist=is_file($html_cache_path.'index.html')) {
    			$delete = unlink($html_cache_path.'index.html');
    			if ($delete) {
    				$res = array('status'=>1,'msg'=>'清除缓存成功，刷新前台首页面看看吧:)');
    			}else{
    				$res = array('status'=>2,'msg'=>'清除缓存失败！，请重试！:( ');
    			}
    		}else{
    			$res = array('status'=>2,'msg'=>'首页缓存文件已经不存在了。。。');
    		} */
    	}else{
    		$res = array('status' => 0, 'msg'=> '非法请求！！！');
    	}
    	echo json_encode($res);die;
    }

}
