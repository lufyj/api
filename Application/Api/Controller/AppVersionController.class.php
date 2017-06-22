<?php
namespace Api\Controller;
use Think\Controller;
class AppVersionController extends BaseController {
    //版本更新接口
    public function tests_version(){
        if($this->client_type == 1){
            $list = M('version')->order('id desc')->field('version_num,download_address')->find();
            
            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
        }
        
    }
}
