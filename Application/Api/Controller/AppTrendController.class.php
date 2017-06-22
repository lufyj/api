<?php
namespace Api\Controller;
use Think\Controller;
class AppTrendController extends BaseController {
    //趋势接口
    public function trend(){
        if($this->client_type == 1){

            //接收参数
            $goods_id = I('post.goods_id',0,'intval');

            //校验参数
            $goods_id || $this->ajaxDie(60);

            $data = M('goodsPriceHistory')->field('create_time,price')->where(['goods_price_id' => $goods_id])->order('create_time desc')->select();
            foreach ($data as $k => $v){
            	$data[$k]['goods_avg']    =  $v['price'];
            	$data[$k]['create_time']  =  strtotime($v['create_time']);
            }
            asort($data);
            $data = array_values($data);
            if($data){
                $this->ajaxDie(1,$data);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }
}