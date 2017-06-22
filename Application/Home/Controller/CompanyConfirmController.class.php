<?php
/**
 * 前台公司认证信息控制器
 * Author: jingwei
 * Date: 2016/10/20
 */
namespace Home\Controller;


class CompanyConfirmController extends HomeController{

    private $userId=0;
    private $model;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=intval($user['id']);
        $this->model=D('CompanyConfirm');
    }
    
    //获取公司提交的认证信息(点击认证管理时使用)
    public function confirmInfo(){

        $get=I('get.');
        $info=$this->model->where('user_id='.$this->userId)->find();
        $this->assign('act','comConfirm');
        $this->meta_title = '认证管理';
        if($info){
            if($info['license_continue']!=9999){
                $t=explode(',',$info['license_continue']);
                $info['begin']=date('Y-m-d',$t[0]);
                $info['end']=date('Y-m-d',$t[1]);
                unset($t);
            }
            //$get['go'] 代表点击上一步
            //$get['status'] 代表点击审核未通过后进入编辑信息页面
            $this->assign('info',$info);
            if($info['confirm_status']==1){
               if($info['step']!=0){
                   if(intval($get['go'])==1){
                       $this->display('CompanyConfirm/confirmCompany');
                   }else{
                       $this->display('CompanyConfirm/confirmCount');
                   }
               }else{
                   $this->display('CompanyConfirm/confirmCompany');
               }
            }else{
                if(intval($get['status'])==4 || intval($get['go'])==1){
                    $this->display('CompanyConfirm/confirmCompany');
                }else{
                    $this->display('CompanyConfirm/confirmView');
                }
            }
        }else{
            $this->display('CompanyConfirm/confirmCompany');
        }
    }

    //保存每个步骤提交的信息
    //第二个步骤保存信息暂时不使用
    public function saveStep(){
        if(IS_POST){
            $post=I('post.');
            if($post && in_array(intval($post['step']),array(1,3))){
                $status=false;
                switch(intval($post['step'])){
                    case 1:
                        $status=$this->saveFirst($post);
                        $step=1;
                        break;
                    /*case 2:
                        $status=$this->saveSecond($post);
                        break;*/
                    case 3:
                        $status=$this->saveThird($post);
                        $step=3;
                        break;
                }

                if($status){
                    $rs['status']=1;
                    $rs['msg']='保存成功';
                    if($step==1){
                        $rs['path']=U('CompanyConfirm/confirmInfo');
                    }elseif($step==3){
                        $rs['path']=U('CompanyConfirm/confirmSub');
                    }
                   echo json_encode($rs);
                }else{
                    $rs['status']=0;
                    $rs['msg']='保存失败';
                    $this->ajaxReturn($rs);
                }

            }else{
                $rs['status']=0;
                $rs['msg']='保存失败';
                $this->ajaxReturn($rs);
            }
        }else{
            $rs['status']=0;
            $rs['msg']='保存失败';
            $this->ajaxReturn($rs);
        }
    }

    //保存步骤1提交的信息
    private function saveFirst($data){
        $confirmInfo=array();
        if($data['name']){
            $confirmInfo['name']=clearXSS($data['name']);
        }else{
            return false;
        }

        if(intval($data['cerType'])==1){
            if(strlen($data['licNumber'])==15 && ctype_digit($data['licNumber']) && strlen($data['orgCode'])==10 && preg_match('/[0-9A-Z]{8}-[0-9A-Z]/',$data['orgCode'])){
                $confirmInfo['certificate_type']=intval($data['cerType']);
                $confirmInfo['license_number']=trim($data['licNumber']);
                $confirmInfo['organization_code']=clearXSS($data['orgCode']);

                $fields=array('id','user_id','license_img','organization_code_certificate','comprehensive_license');
                $info=$this->getConfirm($fields);
                if($info){

                    if($_FILES['lic'] && $_FILES['lic']['size']>0){
                        $confirmInfo['license_img']=$this->upImg('License','lic');
                        $this->delImg($info['license_img']);
                        $this->delImg($info['comprehensive_license']);
                    }

                    if($_FILES['org'] && $_FILES['org']['size']>0){
                        $confirmInfo['organization_code_certificate']=$this->upImg('License','org');
                        $this->delImg($info['organization_code_certificate']);
                        $this->delImg($info['comprehensive_license']);
                    }
                }else{
                    if($_FILES['lic'] && $_FILES['org']){
                        $confirmInfo['license_img']=$this->upImg('License','lic');
                        $confirmInfo['organization_code_certificate']=$this->upImg('License','org');
                    }else{
                        return false;
                    }
                }
                $confirmInfo['comprehensive_license']='';
                $confirmInfo['social_credit_code']='';
            }else{
                return false;
            }
        }elseif(intval($data['cerType'])==2){
            if(strlen($data['socialCreditCode'])==18 && preg_match('/[0-9A-Z]{18}/',$data['socialCreditCode'])){
                $confirmInfo['certificate_type']=intval($data['cerType']);

                $fields=array('id','user_id','comprehensive_license','license_img','organization_code_certificate');
                $info=$this->getConfirm($fields);
                if($info){
                    if($_FILES['comLic'] && $_FILES['comLic']['size']>0) {
                        $confirmInfo['comprehensive_license']=$this->upImg('License','comLic');
                        $this->delImg($info['comprehensive_license']);
                        $this->delImg($info['license_img']);
                        $this->delImg($info['organization_code_certificate']);
                    }
                }else{
                    if($_FILES['comLic']){
                        $confirmInfo['comprehensive_license']=$this->upImg('License','comLic');
                    }else{
                        return false;
                    }
                }
                $confirmInfo['social_credit_code']=clearXSS($data['socialCreditCode']);
                $confirmInfo['license_number']='';
                $confirmInfo['organization_code']='';
                $confirmInfo['license_img']='';
                $confirmInfo['organization_code_certificate']='';
            }else{
                return false;
            }
        }else{
            return false;
        }

        if($data['licAddress']){
            $confirmInfo['license_address']=clearXSS($data['licAddress']);
        }else{
            return false;
        }

        if($data['licContinue']==9999){
            $confirmInfo['license_continue']=9999;
        }elseif($data['licContinue']==1 && $data['licContinue_begin'] && $data['licContinue_end']){
            $begin=strtotime($data['licContinue_begin']);
            $end=strtotime($data['licContinue_end']);
            if($begin && $end){
                $confirmInfo['license_continue']=$begin.','.$end;
            }else{
                return false;
            }
        }

        if($data['address']){
            $confirmInfo['address']=clearXSS($data['address']);
        }else{
            return false;
        }
        
        if(preg_match('/^((0\d{2,3}[-_]\d{7,8})|1[34578]\d{9})$/',$data['mobile'])){
            $confirmInfo['mobile']=trim($data['mobile']);
        }else{
            return false;
        }
       
        $confirmInfo['business_scope']=empty($data['busScope'])?'':clearXSS($data['busScope']);
        $confirmInfo['registered_capital']=empty($data['regCapital'])?'':intval($data['regCapital']);
        $confirmInfo['step']=1;
        $confirmInfo['user_id']=$this->userId;
        $confirmInfo['confirm_status']=1;
        $status=$this->model->saveInfo($confirmInfo);

        return $status;
    }

    //保存步骤2提交的信息
    private function saveSecond($data){
        $confirmInfo=array();
        if($data['legAddress']){
            $confirmInfo['legal_address']=clearXSS($data['legAddress']);
        }else{
            return false;
        }

        if($data['legName'] && strlen($data['legName'])<15){
            $confirmInfo['legal_name']=clearXSS($data['legName']);
        }else{
            return false;
        }

        if(preg_match('/\d{15}|\d{17}[0-9Xx]/',$data['cardNum'])){
            $confirmInfo['idcard']=$data['cardNum'];
        }else{
            return false;
        }

        if(in_array($data['cardType'],array(1,2))){
            $confirmInfo['idcard_type']=intval($data['cardType']);
        }else{
            return false;
        }

        $fields=array('id','user_id','idcard_face','idcard_back');
        $info=$this->getConfirm($fields);
        if($info){
            if($_FILES['face']){
                $confirmInfo['idcard_face']=$this->upImg('IdCard','face');
                $this->delImg($info['idcard_face']);
            }

            if($_FILES['back']){
                $confirmInfo['idcard_back']=$this->upImg('IdCard','back');
                $this->delImg($info['idcard_back']);
            }
        }else{
            if($_FILES['face'] && $_FILES['back']){
                $confirmInfo['idcard_face']=$this->upImg('IdCard','face');
                $confirmInfo['idcard_back']=$this->upImg('IdCard','back');
            }else{
                return false;
            }
        }
        $confirmInfo['user_id']=$this->userId();
        $confirmInfo['step']=2;
        $status=$this->model->saveInfo($confirmInfo);

        return $status;
    }

    //保存步骤3提交的信息
    private function saveThird($data){
        $confirmInfo=array();
        if(ctype_digit($data['countNum'])){
            if($data['countName'] && $data['bankName'] && $data['bankCity'] && $data['bankBranch']){
                $confirmInfo['count_number']=$data['countNum'];
                $confirmInfo['count_name']=clearXSS($data['countName']);
                $confirmInfo['bank_name']=clearXSS($data['bankName']);
                $confirmInfo['bank_city']=clearXSS($data['bankCity']);
                $confirmInfo['bank_branch_name']=clearXSS($data['bankBranch']);
            }else{
                return false;
            }
        }else{
            $confirmInfo['count_name']=empty($data['countName'])?'':clearXSS($data['countName']);
            $confirmInfo['bank_name']=empty($data['countName'])?'':clearXSS($data['bankName']);
            $confirmInfo['bank_city']=empty($data['countName'])?'':clearXSS($data['bankCity']);
            $confirmInfo['bank_branch_name']=empty($data['countName'])?'':clearXSS($data['bankBranch']);
            $confirmInfo['count_number']='';
        }

        $confirmInfo['user_id']=$this->userId;
        $confirmInfo['step']=3;
        $confirmInfo['confirm_status']=2;
        $status=$this->model->saveInfo($confirmInfo);

        $stdata['company_auth_status']=1;
        $st=D('User')->where('id='.$this->userId)->save($stdata);
        return $status;
    }

    //跳转提交申请成功页面
    public function confirmSub(){
        $this->assign('act','comConfirm');
        $this->meta_title = '认证管理';
        $this->display('CompanyConfirm/confirmSub');
    }

    //上传图片
    private function upImg($dir,$fileName){

        $img_config = C('PICTURE_UPLOAD');
        $img_config['savePath'] = $dir.'/';
        $res = uploadImg($fileName, $img_config, array(array(480,400)));
        $path=($res['code']==1)?$res['file'][1]:'';
        unset($res);
        return $path;
    }

    //删除旧图片
    public function delImg($path=''){
        if($path){
            $pathBase=getImgs($path,2);
            $path=$_SERVER['DOCUMENT_ROOT'].$path;
            $pathBase=$_SERVER['DOCUMENT_ROOT'].$pathBase;
            if(is_file($path) && is_file($pathBase)){
                $fileType=pathinfo($path,PATHINFO_EXTENSION);
                $fileTypeBase=pathinfo($pathBase,PATHINFO_EXTENSION);
                $type=array('jpg','png','jpeg');
                if(in_array($fileType,$type) && in_array($fileTypeBase,$type)){
                    unlink($path);
                    unlink($pathBase);
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //获取指定的认证信息
    private function getConfirm($fields)
    {
        $data=$this->model->where('user_id='.$this->userId)->field($fields)->find();
        return $data?$data:array();
    }

    //保存认证企业二级域名
    // ajax返回状态码：1 用户未认证  2 审核中 3  域名不合法 4 提交成功 5 提交失败 6 域名已被注册
    public function subDomain(){
        $domain=I('get.d');
        $info=$this->model->where('user_id='.$this->userId.' and confirm_status=3')->find();
        if($info){
            //验证当前用户二级域名的审核状态
            $status=$this->model->isPass($info);
            if($status){
                //处理提交的域名
                $domain=$this->model->checkDomain($domain,$this->userId);
                if($domain=='exists'){
                    $this->ajaxInfo(6,'域名已被注册');
                }elseif($domain=='error'){
                    $this->ajaxInfo(3,'域名不合法');
                }elseif($domain){
                    $st=$this->model->where('user_id='.$this->userId)->save(array('domain'=>$domain));
                    $st?$this->ajaxInfo(4,'提交成功'):$this->ajaxInfo(5,'提交失败');
                }else{
                    $this->ajaxInfo(5,'提交失败');
                }
            }else{
                $this->ajaxInfo(2,'审核中');
            }
        }else{
            $this->ajaxInfo(1,'用户未认证');
        }
    }

    public function ajaxInfo($code,$msg,$data=''){
        $rs=['code'=>$code,'msg'=>$msg,'data'=>$data];
        $this->ajaxReturn($rs);
    }
}