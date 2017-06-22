<?php
namespace Api\Controller;
use Think\Controller;
class AppBannerController extends BaseController {
    public function index(){
        //App端手机展示轮播图 
        if($this->client_type == 1){
            $data['platform'] = '2';
            $list = M('banner')->where($data)->field('id,img_url,title,link_url')->select();
            if($list){
                 $this->ajaxDie(1,$list);
            }else{
                 $this->ajaxDie(0);
            }
        }else{
             $this->ajaxDie(43);
        }
    }

     public function Hall_index(){
        //App端大厅手机展示轮播图
        if($this->client_type == 1){
            $data['platform'] = '3';
            $list = M('banner')->where($data)->field('id,img_url,title,link_url')->select();
            if($list){
                 $this->ajaxDie(1,$list);
            }else{
                 $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    //轮播图优化 1.4版本新家
    public function banner(){
        if($this->client_type == 1){
            $type = I('post.type',1,'intval');//1 首页轮播图 2 大厅轮播图 3 行情页轮播图
            $arr = array(1,2,3);
            if(!in_array($type,$arr)){//参数校验
                $this->ajaxDie(0);
            }
            if($type == 1){
                $data['platform'] = '2';
            }else if($type == 2){
                $data['platform'] = '3';
            }else{
                $data['platform'] = '4';
            }

            $list = M('banner')->where($data)->field('id,img_url,title,link_url')->select();

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
