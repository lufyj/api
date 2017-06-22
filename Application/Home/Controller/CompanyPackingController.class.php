<?php
/**
 * 前台公司包装信息控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyPackingController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyPacking');
    }

    //保存包装信息
    public function saveInfo(){

        if(IS_POST){
            $data=I('post.');
            if(empty($data))  $this->_ajax(0,'保存失败');
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

    //获取包装详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0)  $this->_ajax(0,'编辑失败');
            $info=$this->model->getInfo(intval($get['id']));
            if(empty($info)){
                $this->_ajax(0,'编辑失败');
            }else{
                $info['img']=empty($info['img'])?'':explode(",",$info['img']);
                $rs['status']=1;
                $rs['msg']='编辑成功';
                $rs['info']=$info;
                $this->ajaxReturn($rs);
            }
        }else{
            $this->_ajax(0,'编辑失败');
        }
    }

    //删除包装信息
     public function delInfo($id=0){

         if(intval($id)<=0){
             return false;
         }else{
             $st=$this->model->delInfo(intval($id));
             return $st?true:false;
         }
     }

    //上传图片
    public function upImg(){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'Packing/';
        $res = uploadImg('packing', $img_config, array(array(200,200)));
        $data=$res['code']==1?array('status'=>1,'path'=>$res['file'][1],'msg'=>'上传成功'):array('status'=>0,'msg'=>$res['msg']);
        unset($res);
        $this->ajaxReturn($data);
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