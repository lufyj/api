<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
		

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('/');
	}

    protected function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        
        $this->_initialize_config();
    }
    
    /**
     * 初始化一些网站页面的设置
     */
    private function _initialize_config(){
    	
    	//判断是否自动登录
    	if(!session('user_auth') && !session('already_validated')){
    		D('User')->check_is_auto_login();
    		session('already_validated', true);//标示已经验证过，防止重复验证
    	}    	
    	
    	//在这里    	
    	$cacheData = S('Home_Cache');
    	if(!$cacheData){
    		//获取热门的前10条数据
    		$hots = D('Goods')->getHots();
    		
    		S('Home_Cache', array(
    			'hots' => $hots
    		));
    	}else{
    		$hots = $cacheData['hots'];
    	}
    	
    	$this->assign('hots', $hots);
    }
	
    protected $uid;
    
    /**
     * 用户登录检测，记录登录前的页面
     */
    protected function home_login(){
    	$user = session('user_sign');
    	if(!$user || (int)$user['id'] <= 0){    		
    		//为了登录成功后跳转到这个页面
    		//session('jump_url', $_SERVER['PHP_SELF']);
    		//跳转到登录页面    		
    		$url = U('login/index').'?redirectUrl='.urlencode(get_url());
    		redirect($url);    		
    	}
    	$this->uid = $user['id'];
    }
    
    /**
     * 返回终止信息
     * @param string $msg
     * @param number $code
     */
    protected function dieMsg($msg = '', $code = 0){
    	$res = array( 
    		'code' => $code,
    		'msg'  => $msg
    	);    	
    	$this->ajaxReturn($res);die();
    }
    /**
     * 若首页缓存不存在就重新生成
     * @return Ambigous <multitype:unknown , mixed, \Think\mixed, object>
     */
    protected function getIndex_Cache(){
    	$cacheData = S('Index_Cache');
    	if(!$cacheData){
    		//获取所有一级分类以及子分类
    		$cates = D('Goods')->getAllCatsForNav();    		
    		//取出相应的banner信息
    		$banners = D('Banner')->getAllBanners();
    		//获取最新公告
    		$notices = D('Market')->getLatestInfo();
    		
    		$cacheData = array(
    			'cates'   => $cates,
    			'banners' => $banners,
    			'notices' => $notices
    		);
    		S('Index_Cache', $cacheData);
    	}
    	return $cacheData;
    }
}
