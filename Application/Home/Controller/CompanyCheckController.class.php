<?php
/**
 * 前台公司代检测信息控制器
 * Author: jingwei
 * Date: 2016/10/10
 */
namespace Home\Controller;

class CompanyCheckController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyCheck');
    }

    //保存代检测信息
    public function saveInfo(){

        if(IS_POST){
            $data=I('post.');
            if(empty($data))  $this->_ajax(0,'保存失败');
            $data['uid']=$this->userId;
            $info=$this->model->filterData($data);
            if($info==''){
                $this->_ajax(0,'保存失败');
            }
            $st=$this->model->saveInfo($info);
            $rs=$st?array('status'=>1,'msg'=>'保存成功'):array('status'=>0,'msg'=>'保存失败');
            unset($data,$info);
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'保存失败');
        }
    }

    //获取代检测详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0)  $this->_ajax(0,'编辑失败');
            $info=$this->model->getInfo(intval($get['id']));
            $method=C('checkMethod');
            if(in_array($info['content'],$method)){
                foreach($method as $k=>$v){
                    if($info['content']==$v){
                        $info['content']=$k;
                    }
                }
                $info['other']='';
            }else{
                $info['other']=$info['content'];
                $info['content']='';
            }
            $rs=empty($info)?array('status'=>0,'msg'=>'编辑失败'):array('status'=>1,'msg'=>'编辑成功','info'=>$info);
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'编辑失败');
        }
    }

    //删除检测信息
     public function delInfo($id=0){

         if(intval($id)<=0){
             return false;
         }else{
             $st=$this->model->delInfo($id);
             return $st?true:false;
         }
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