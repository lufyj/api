<?php

namespace Home\Controller;

/**
 * 前台帮助中心控制器 
 */
class HelpController extends HomeController {
   /**默认帮助中心位置共计16个*/	
	public function index(){
		$this->meta_title = '帮助中心';
		$this->display();
	}
	public function allHelp(){
		$this->assign('act', '1');
	}
	/* 
	 * 帮助中心--新手入门--1求购指南1
	 */
	
	public function demandHelp(){
		$this->assign('act', 'demandHelp');
		$this->assign('code', '1');
		$this->meta_title = '求购指南';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1供货指南2
	 */
	public function supplyHelp(){
		$this->assign('act', 'supplyHelp');
		$this->assign('code', '1');
		$this->meta_title = '供货指南';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1会员注册3
	 */
	public function register(){
		$this->assign('act', 'register');
		$this->assign('code', '1');
		$this->meta_title = '会员注册';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1登录4
	 */
	public function login(){
		$this->assign('act', 'login');
		$this->assign('code', '1');
		$this->meta_title = '如何登录';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1找回密码5
	 */
	public function findpwd(){
		$this->assign('act', 'findpwd');
		$this->assign('code', '1');
		$this->meta_title = '找回密码';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1我的关注6
	 */
	public function attention(){
		$this->assign('act', 'attention');
		$this->assign('code', '1');
		$this->meta_title = '我的关注';
		$this->display();
	}
	/*
	 * 帮助中心--新手入门--1个人资料7
	 */
	public function personalData(){
		$this->assign('act', 'personalData');
		$this->assign('code', '1');
		$this->meta_title = '个人资料';
		$this->display();
	}
	/*
	 * 帮助中心--支付方式--2企业认证1
	 */
	public function approve(){
		$this->assign('act', 'approve');
		$this->assign('code', '2');
		$this->meta_title = '企业认证';
		$this->display();
	}
	/*
	 * 帮助中心--支付方式--2企业服务2
	 */
	public function service(){
		$this->assign('act', 'service');
		$this->assign('code', '2');
		$this->meta_title = '企业服务';
		$this->display();
	}
	/*
	 * 帮助中心--支付方式--2企业名片3
	 */
	public function card(){
		$this->assign('act', 'card');
		$this->assign('code', '2');
		$this->meta_title = '企业名片';
		$this->display();
	}
	/*
	 * 帮助中心--支付方式--3在线支付1
	 */
	public function payment1(){
		$this->assign('act', 'payment1');
		$this->meta_title = '在线支付';
		$this->assign('code', '3');
		$this->display();
	}
	/*
	 * 帮助中心--支付方式--3线下支付2
	 */
	public function payment2(){
		$this->assign('act', 'payment2');
		$this->meta_title = '线下支付';
		$this->assign('code', '3');
		$this->display();
	}
	/*
	 * 帮助中心--关于我们--4公司简介1
	 */
	public function brief(){
		$this->assign('act', 'brief');
		$this->meta_title = '公司简介';
		$this->assign('code', '4');
		$this->display();
	}
	/*
	 * 帮助中心--关于我们--4联系客服2
	 */
	public function contact(){
		$this->assign('act', 'contact');
		$this->meta_title = '联系客服';
		$this->assign('code', '4');
		$this->display();
	}
	/*
	 * 帮助中心--关于我们--4意见反馈3
	 */
	public function feedback(){
		$this->assign('act', 'feedback');
		$this->meta_title = '意见反馈';
		$this->assign('code', '4');
		$this->display();
	}
	
}
