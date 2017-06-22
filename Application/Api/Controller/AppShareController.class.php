<?php
namespace Api\Controller;
use Think\Controller;
class AppShareController extends BaseController {
    //分享
    //供应分享信息
    public function supply_share(){
        //判断请求数据 Ios是 2  Android是 1
        if($this->client_type == 1){
            
            //接收参数            
            $id   =  I('post.id',0,'intval');

            //校验参数
            $id   || $this->ajaxDie(53);

            $result = M('supply')->where(['id' => $id])->find();

            if($result){
                $pic                   = explode(",",$result['pic']);
                $result['pic']         = $pic;
                $result['create_time'] = date('Y-m-d H:i',$result['create_time']);
            }
            
            if($result['origin_type'] == 1){
                $result['origin_area']= '较广';
            }else if($result['origin_type'] == 2){
                $result['origin_area']= '进口';
            }
           
            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
         }else{
            $this->ajaxDie(43);
         }
    } 
    //求购分享信息
    public function  demand_share(){
        if($this->client_type == 1){

            //接收参数            
            $id   =  I('post.id',0,'intval');

            //校验参数
            $id   || $this->ajaxDie(53);

            $result  = M('demand')->where(['id' => $id])->find();
            if($result){
                $result['create_time'] = date('Y-m-d H:i',$result['create_time']);
            }
            if($result['price_type'] == 2){
                $result['price']= '面议';
            }
            if($result['origin_type'] == 1){
                $result['origin_area']= '较广';
            }else if($result['origin_type'] == 2){
                $result['origin_area']= '进口';
            }

            if($result){
                $this->ajaxDie(1,$result);
            }else{
                $this->ajaxDie(0);
            }
         }else{
            $this->ajaxDie(43);
         }     
    }
 
}
