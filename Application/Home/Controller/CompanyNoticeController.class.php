<?php
/**
 * 前台公司通知控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyNoticeController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize()
    {
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyNotice');
    }

    //保存通知信息
    public function saveInfo($content){

        $info=array();
        $info['content']=$content;
        $info['add_time']=NOW_TIME;
        $info['user_id']=$this->userId;
        unset($data);
        $st=$this->model->saveInfo($info);
        if($st){
           $status=true;
        }

        return $status;

    }

    //获取通知详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0){
                $this->error('获取失败');
            }

            $info=$this->model->getInfo(intval($get['id']));
            if(empty($info)){
                $this->error('获取失败');
            }else{
                $this->assign('info',$info);
                $this->display();
            }
        }else{
            $this->redirect('Company/comIndex');
        }
    }

    //删除通知信息
     public function delInfo(){

         if(IS_AJAX){
            $get=I('get.');
             if(empty($get) || intval($get['id'])<=0){
                 $data['status']=0;
                 $data['msg']='删除失败';
             }else{
                 $st=$this->model->delInfo(intval($get['id']));
                 $data=$st?array('status'=>1,'msg'=>'删除成功'):array('status'=>0,'msg'=>'删除失败');
             }
         }else{
             $data['status']=0;
             $data['msg']='删除失败';
         }
         $this->ajaxReturn($data);
     }

}