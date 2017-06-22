<?php

namespace Admin\Controller;
/**
 * 前台用户认证信息管理器
 * Author jingwei
 * Date 2016-10-20
 *
 */
class CompanyConfirmController extends AdminController {
	
	/* 获取用户认证申请列表 */
	public function index(){
		
		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码
		$condition = I('get.condition');
		 
		$data = D('CompanyConfirm')->getList($condition, $show_num, $page_num);
		$this->assign('data', $data);
		 
		if(IS_AJAX && IS_GET){
			$this->display('table');
			exit;
		}
		$this->meta_title = '认证管理';
		$this->display();
	}
	
	public function review(){

		$get=I('get.');
		if(intval($get['id'])>0){
			$info=D('CompanyConfirm')->where('id='.intval($get['id']))->find();
			if($info){
				$this->meta_title = '认证管理';
				if($info['license_continue']!=9999){
					$t=explode(',',$info['license_continue']);
					$info['begin']=date('Y年m月d日',$t[0]);
					$info['end']=date('Y年m月d日',$t[1]);
					unset($t);
				}

				$info['license_img_base']=empty($info['license_img'])?'':getImgs($info['license_img'],2);
				$info['organization_code_certificate_base']=empty($info['organization_code_certificate'])?'':getImgs($info['organization_code_certificate'],2);
				$info['comprehensive_license_base']=empty($info['comprehensive_license'])?'':getImgs($info['comprehensive_license'],2);
				//处理二级域名信息
				$domain=D('CompanyConfirm')->domainInfo($info['domain']);
				$this->assign('domain',$domain);
				$this->assign('info',$info);
				$this->display('CompanyConfirm/edit');
			}else{
				$this->meta_title = '认证管理';
				$this->redirect('CompanyConfirm/index');
			}
		}else{
			$this->meta_title = '认证管理';
			$this->redirect('CompanyConfirm/index');
		}
	}

	//保存审核结果
	public function subCheck(){

		if(IS_AJAX){
			$post=I('post.');
			if(intval($post['id'])>0 && in_array($post['confirmStatus'],array(3,4))){
				$info=D('CompanyConfirm')->where('id='.intval($post['id']))->find();
				if($info && $info['step']==3 && ($info['confirm_status']!=1 || $info['confirm_status']!=3)){
					$data['confirm_status']=intval($post['confirmStatus']);
					$data['remark']=empty($post['remark'])?'':clearXSS($post['remark']);
					$data['confirm_time']=time();
					$data['operator_id']=$_SESSION['ydw_admin']['user_auth']['uid'];
					$status=D('CompanyConfirm')->where('id='.$info['id'])->save($data);
					if($status){
						if($data['confirm_status']==3){
							$dataSt['company_auth_status']=2;
						}elseif($data['confirm_status']==4){
							$dataSt['company_auth_status']=1;
						}else{
							$dataSt['company_auth_status']=0;
						}

						$st=D('User')->where('id='.$info['user_id'])->save($dataSt);
						$rs['status']=1;
						$rs['msg']='审核成功';
						$rs['path']=U('CompanyConfirm/index');
						$this->ajaxReturn($rs);
					}else{
						$this->ajaxReturn(array('status' => 0,'msg' => '审核失败'));
					}
				}else{
					$this->ajaxReturn(array('status' => 0,'msg' => '审核失败'));
				}
			}else{
				$this->ajaxReturn(array('status' => 0,'msg' => '审核失败'));
			}
		}else{
			$this->ajaxReturn(array('status' => 0,'msg' => '审核失败'));
		}
	}

	/**
	 * 提交二级域名审核结果
	 */
	public function ajaxGetDomainStatus(){
		$rs=['status'=>0,'msg'=>'审核失败'];
		if(IS_AJAX){
			$post=I('post.');
			$ds=intval($post['domainStatus']);
			$remark=clearXSS($post['domainremark']);
			if(intval($post['id'])>0 && in_array($ds,array(2,3))){
				$info=D('CompanyConfirm')->where('id='.intval($post['id']))->find();
				if($info && ($info['step']==3) && ($info['confirm_status']==3) && $info['domain']){
					$data['domain']=$ds.substr($info['domain'],1);
					$data['domain_remark']=($ds==3)?$remark:'';
					$status=D('CompanyConfirm')->where('id='.$info['id'])->save($data);
					if($status){
						$rs=['status' => 1,'msg' => '审核成功','path'=>U('CompanyConfirm/index')];
					}
				}
			}
		}
		$this->ajaxReturn($rs);
	}
}
