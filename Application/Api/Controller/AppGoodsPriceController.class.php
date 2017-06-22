<?php
namespace Api\Controller;
use Think\Controller;
class AppGoodsPriceController extends BaseController {
    //今日价格
    public function goods_price(){
        if($this->client_type == 1){

         	//接收参数
         	$num       =  10; 
         	$cid       =  I('post.cid', 0,'intval');//分类id
         	$type      =  I('post.type'  , 0,'intval');//1是全部今日价格 2是今日变动
         	$minId     =  I('post.minId' , 0,'intval');
         	$market    =  I('post.market', 0,'intval');//1亳州  2 安国 3 玉林 4 成都
         	$keyword   =  clearXSS(I('post.keyword'));//药材关键字

         	//组装条件
         	$cid     && $data['cate_id']    = $cid;
         	$minId   && $data['id']         = array('lt',$minId);
         	$market  && $data['market']     = $market;
         	$keyword && $data['goods_name'] = $keyword;

         	$field = 'id,cate_id,goods_name,goods_attr_name,origin,price,trend,market';

         	if($type == 1){
         		$table = 'goods_price2';	
         	}else if($type == 2){
         		$table = 'goods_price_today';
         	}

            $list = M($table)
            	->where($data)
                ->field($field)
                ->order('id desc')
                ->limit($num)
                ->select();

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