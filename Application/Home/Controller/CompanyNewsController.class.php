<?php
/**
 * 前台公司动态控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyNewsController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyNews');
    }

    //保存公司动态信息
    public function saveInfo(){

        if(IS_POST){
            $data=I('post.');
            if(empty($data)) $this->_ajax(0,'保存失败');

            $data['uid']=$this->userId;
            $info=$this->model->filterData($data);
            if(empty($info)) $this->_ajax(0,'保存失败');

            $st=$this->model->saveInfo($info);
            $rs=$st?array('status'=>1,'msg'=>'保存成功'):array('status'=>2,'msg'=>'保存失败');
            unset($data);
        }else{
            $rs['status']=0;
            $rs['msg']='保存失败';
        }
        $this->ajaxReturn($rs);
    }

    //获取公司动态详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0) $this->_ajax(0,'获取失败');
            $info=$this->model->getInfo(intval($get['id']));
            if(empty($info)){
                $this->_ajax(0,'获取失败');
            }else{
                $this->_ajax(1,'获取成功',$info,'info');
            }
        }else{
            $this->_ajax(0,'获取失败');
        }
    }

    //删除公司动态信息
     public function delInfo(){

         if(IS_AJAX){
            $post=I('post.');
             if(empty($post) || intval($post['id'])<=0){
                 $this->_ajax(0,'删除失败');
             }else{
                 $st=$this->model->delInfo(intval($post['id']));
                 $data=$st?array('status'=>1,'msg'=>'删除成功'):array('status'=>0,'msg'=>'删除失败');
                 $this->ajaxReturn($data);
             }
         }else{
             $this->_ajax(0,'删除失败');
         }
     }

    //上传图片
    public function upImg(){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'News/';
        $res = uploadImg('news',$img_config,array(array(200,200)));
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