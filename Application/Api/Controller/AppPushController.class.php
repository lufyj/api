<?php
/**
 * App端推送消息接口
 * Author: jingwei
 * Date: 2017/01/06
 */

namespace Api\Controller;
use Think\Controller;
class AppPushController extends BaseController {

    //推送消息列表
    public function pushList(){

        if($this->client_type==1 || $this->client_type==2){

            $this->secureVerify();

            $uid=I('post.uid',0,'intval');
            if($uid<=0){
                $this->ajaxDie(0);
            }
            $info=D('Push')->pushList($uid);
            if($info){
                $this->ajaxDie(1,$info);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }

    //更新消息阅读状态
    public function pushStatus(){
        if($this->client_type==1 || $this->client_type==2){

            $this->secureVerify();

            $uid=I('post.uid',0,'intval');
            $pushId=I('post.p',0,'intval');
            $status=I('post.s',0,'intval');
            $infoType=I('post.i');
            if($uid<=0 || $pushId<=0 || !in_array($infoType,['s','d'])){
                $this->ajaxDie(0);
            }
            if($infoType=='s'){
                $info=D('Push')->pushStatus($uid,$pushId,$status);
            }else{
                $info=D('PushDemandinfo')->pushStatus($uid,$pushId,$status);
            }

            if($info){
                $this->ajaxDie(1,$info);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
}
