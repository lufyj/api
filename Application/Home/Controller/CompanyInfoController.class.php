<?php
/**
 * 前台公司基本信息控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyInfoController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyInfo');
    }

    //保存信息
    public function saveInfo(){
        if(IS_POST){
            $data=I('post.');
            if(empty($data))  $this->_ajax(0,'保存失败');

            if(isset($data['content'])){
                if(empty($data['content'])){
                    $content='';
                }else{
                    $content=clearXSS($data['content']);
                    $content=new_html_special_chars(safe_replace($content));
                }
                $comNotice = A('CompanyNotice');
                $st=$comNotice->saveInfo($content);
                if(!$st)  $this->_ajax(0,'保存失败');
            }

            $data['desc']=preg_replace(array("/ /","/[\r\n]+/","/  /"),array("","<br/>",""),$data['desc']);
            unset($data['content']);
            $info=array();
            $fields=array('name','style','desc','background','logo');
            foreach($data as $k=>$v){
                if(in_array($k,$fields)){
                    if(!empty($v)){
                        $v=safe_replace(clearXSS($v));
                        $info[$k]=$v;
                    }
                }
            }

            if(strlen($info['name'])>100)  $this->_ajax(0,'保存失败');

            //$info['desc']=preg_replace(array("/ /","/[\r\n]+/","/  /"),array("","<br/>",""),$info['desc']);
            $info['user_id']=$this->userId;
            unset($data);
            $st=$this->model->saveInfo($info);
            $rs=$st?array('status'=>1,'msg'=>'保存成功'):array('status'=>0,'msg'=>'保存失败');
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'保存失败');
        }
    }

    //上传/编辑logo
    public function uplogo(){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'Logo/';
        $res = uploadImg('logo', $img_config, array(array(80,160)));
        if($res['code']==1){
            $data['status']=1;
            $data['path']=$res['file'][1];
            $data['msg']='上传成功';
        }else{
            $data['status']=0;
            $data['msg']=$res['msg'];
        }

        unset($res);
        $this->ajaxReturn($data);
    }

    //上传/编辑公司背景图片
    public function upBackground(){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'Background/';
        $res = uploadImg('bg', $img_config, array(array(320,200)));
        if($res['code']==1){
            $data['status']=1;
            $data['path']=$res['file'][1];
            $data['msg']='上传成功';
        }else{
            $data['status']=0;
            $data['msg']=$res['msg'];
        }

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