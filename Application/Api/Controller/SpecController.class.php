<?php
namespace Api\Controller;
use Think\Controller;
class SpecController extends BaseController {
   public function spec(){
      if($this->client_type == 1  || $this->client_type == 13){
            $type = $this->client_type;
            //因IOS 已上架  所以 暂时分割  等IOS重新上架时  取消这个
            if($type == '13'){
                //接收参数
                $gid = I('post.id',0,'intval');

                $result = M('goods')->where(['id' => $gid])->find();

                //校验参数
                $gid                      || $this->ajaxDie(60);
                $result['goods_attr_ids'] || $this->ajaxDie(89);
                
                $spec = explode(',', $result['goods_attr_ids']);
                foreach($spec as $k => $v){
                    $attr = M('spec')->where(['id' => $v])->field('attr_name,id')->find();
                    $list[$k]['id']        = $attr['id'];
                    $list[$k]['attr_name'] = $attr['attr_name'];  
                }

                if($list){
                    $this->ajaxDie(1,$list);
                }else{
                    $this->ajaxDie(0);
                }
            }else if($type == '1'){
                  //接收参数
                $gid = I('post.id',0,'intval');

                $result = M('goods')->where(['id' => $gid])->find();

                //校验参数
                $gid                      || $this->ajaxDie(60);
                $result['goods_attr_ids'] || $this->ajaxDie(89);

                $spec = explode(',', $result['goods_attr_ids']);
                foreach($spec as $k => $v){
                    $attr = M('spec')->where(['id' => $v])->field('attr_name,id')->find();
                    $list[$k]  = $attr['attr_name'];  
                }

                if($list){
                    $this->ajaxDie(1,$list);
                }else{
                    $this->ajaxDie(0);
                }
            }
        }else{
            $this->ajaxDie(43);
        }
    }
}
