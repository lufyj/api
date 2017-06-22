<?php
namespace Api\Controller;
use Think\Controller;
class AppHotController extends BaseController {
    //热门推荐
    public function goods_hot(){
        if($this->client_type == 1){
            $data['is_recom'] = '1';
            $list = M('goods')->field('id,goods_name,goods_img')->where($data)->select();
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
