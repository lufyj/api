<?php
/**
 * 前台公司物流信息控制器
 * Author: jingwei
 * Date: 2016/9/29  
 */
namespace Home\Controller;


class CompanyDeliveryController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyDelivery');
    }

    //保存物流信息
    public function saveInfo(){

        if(IS_POST){
            $data=I('post.');
            if(empty($data)) $this->ajaxReturn(0,'保存失败');
            $data['uid']=$this->userId;
            $info=$this->model->filterData($data);
            if($info=='')  $this->_ajax(0,'保存失败');
            $st=$this->model->saveInfo($info);
            $rs=$st?array('status'=>1,'msg'=>'保存成功'):array('status'=>0,'msg'=>'保存失败');
            unset($data,$info);
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'保存失败');
        }
    }

    //获取物流详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0)  $this->_ajax(0,'编辑失败');
            $info=$this->model->getInfo(intval($get['id']));
            $info['begin']=$this->getZoneInfo($info['begin']);
            $info['end']=$this->getZoneInfo($info['end']);
            $rs=empty($info)?array('status'=>0,'msg'=>'编辑失败'):array('status'=>1,'msg'=>'编辑成功','info'=>$info);
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'编辑失败');
        }
    }

    //删除物流信息
     public function delInfo($id=0){

         if(intval($id)<=0){
             return false;
         }else{
             $st=$this->model->delInfo(intval($id));
             return $st?true:false;
         }
     }

    //处理地址信息
    private function getZoneInfo($code){

        $company=A('Company');
        $info=array();
        if($code!=0){
            $code=sliceCode($code);
            if(!empty($code[0])){
                $info['provice']=$company->getProHtml($code[0]);
                if(!empty($code[1])){
                    $info['city']=$company->getCityHtml($code[0],$code[1]);
                    if(!empty($code[2])){
                        $info['dist']=$company->getAreaHtml($code[1],$code[2]);
                    }else{
                        $info['dist']='';
                    }
                }else{
                    $info['city']='';
                    $info['dist']='';
                }
            }else{
                $info['provice']=$company->getProHtml(0);
                $info['city']='';
                $info['dist']='';
            }
        }else{
            $info['provice']=$company->getProHtml(0);
            $info['city']='';
            $info['dist']='';
        }

        return $info;
    }

    /*
      * 公共的ajax返回操作
      * @param $code 状态码 $msg 返回信息 $info 返回数据 $key 返回数据键值
      */
    private function _ajax($code,$msg,$info='',$key=''){
        $rs=['status'=>$code,'msg'=>$msg];
        if($key && $info){
            $rs[$key]=$info;
        }
        $this->ajaxReturn($rs);
    }
}