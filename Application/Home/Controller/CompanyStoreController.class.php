<?php
/**
 * 前台公司仓库信息控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyStoreController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyStore');
    }

    //保存仓库信息
    public function saveInfo(){

        if(IS_POST){
            $data=I('post.');
            if(empty($data))  $this->_ajax(0,'保存失败');
            $data['uid']=$this->userId;
            $info=$this->model->filterData($data);
            if(!is_array($info))  $this->_ajax(0,$info);
            $st=$this->model->saveInfo($info);
            $rs=$st?array('status'=>1,'msg'=>'保存成功'):array('status'=>0,'msg'=>'保存失败');
            unset($data,$info);
            $this->ajaxReturn($rs);
        }else{
            $this->_ajax(0,'保存失败');
        }
    }

    //获取仓库详细信息
    public function editInfo(){

        if(IS_GET){
            $get=I('get.');
            if(empty($get) || intval($get['id'])<=0)  $this->_ajax(0,'编辑失败');
            $info=$this->model->getInfo(intval($get['id']));
            $info['zone']=sliceCode($info['zone']);
            $company=A('Company');
            if(!empty($info['zone'][0])){
                $info['zone']['provice']=$company->getProHtml($info['zone'][0]);
                if(!empty($info['zone'][1])){
                    $info['zone']['city']=$company->getCityHtml($info['zone'][0],$info['zone'][1]);
                    if(!empty($info['zone'][2])){
                        $info['zone']['dist']=$company->getAreaHtml($info['zone'][1],$info['zone'][2]);
                    }else{
                        $info['zone']['dist']='';
                    }
                }else{
                    $info['zone']['city']='';
                    $info['zone']['dist']='';
                }
            }else{
                $info['zone']['provice']=$company->getProHtml(0);
                $info['zone']['city']='';
                $info['zone']['dist']='';
            }

            if(empty($info)){
                $this->_ajax(0,'编辑失败');
            }else{
                $info['img']=empty($info['img'])?'':explode(",",$info['img']);
                $this->_ajax(1,'编辑成功',$info,'info');
            }
        }else{
            $this->_ajax(0,'编辑失败');
        }
    }

    //删除仓库信息
     public function delInfo($id=0){

         if(intval($id)<=0){
             return false;
         }else{
             $st=$this->model->delInfo($id);
             return $st?true:false;
         }
     }

    //上传图片
    public function upImg(){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = 'Store/';
        $res = uploadImg('store', $img_config, array(array(200,200)));
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