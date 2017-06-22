<?php

namespace Admin\Controller;

/**
 * 后台广告管理控制器
 * @author wpf
 */
class AdController extends AdminController {
	
	function index(){
		$this->meta_title = '广告管理';
		$this->display();
	}
}
