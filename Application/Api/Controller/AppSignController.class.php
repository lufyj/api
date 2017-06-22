<?php
namespace Api\Controller;
use Think\Controller;
class AppSignController extends BaseController {
    //签到接口
    public function sign(){
        //判断请求数据来自pc 还是手机端  pc 是 1  手机端是 0
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $da['uid']    = I('post.uid',0,'intval');
            $da['time']   = date('ymd');

            $das['month'] = date('Ym'); 
            $das['uid']   = I('post.uid',0,'intval');

            //校验参数
            $da['uid']   || $this->ajaxDie(30);
            
            $result = M('sign')->where($da)->find();
            $res    = M('sign')->where($das)->order('id desc')->find();
            $num    = $res['num'];
            //已签到
            if($result){
                $this->ajaxDie(79);
            }
            $data['uid']   = I('post.uid',0,'intval');
            $data['month'] = date('Ym');

            if($num){
                $data['num'] = $num+1;
            }else{
                $data['num'] = 1;
            }
           
            $data['time'] = date('ymd');

            if(M('sign')->add($data)){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            } 
        }else{
        	   $this->ajaxDie(43);
        }
    } 


    //签到详情
    public function sign_details(){
        if($this->client_type == 1){

            $this->secureVerify();
            
            //接收参数
            $data['uid']   = I('post.uid',0,'intval');
            $data['month'] = Date('Ym');
            
            //校验参数
            $data['uid']  || $this->ajaxDie(30);
           
            $list = M('sign')->where($data)->order('time asc')->getField('time',true);
            $arr = array();
            foreach ($list as $k => $v) {
                $arr[] = substr($v, -2);
            }
            $date = Date('d');

            if(in_array($date, $arr)){//判断当天有没有签到
                $sign= '1';
            }else{
                $sign= '-1';
            };

            if($arr){
                //IOS 要求放平级
                $this->ajaxDie(1,$arr,'',$sign);
            }else{
                //用户本月没有签到的情况
                $this->ajaxDie(1,$arr,'',$sign);
            }
        }else{
            $this->ajaxDie(43);
        }
    }

 
}
