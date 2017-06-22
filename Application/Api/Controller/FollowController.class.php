<?php
namespace Api\Controller;
use Think\Controller;
class FollowController extends BaseController {
    //关注接口
    public function follow(){
        //app传过来用户的ID 药品ID 药品名称
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $data['uid']          = I('post.uid',0,'intval');
            $data['goods_id']     = I('post.goods_id',0,'intval');
            $data['goods_name']   = clearXSS(I('post.goods_name'));
            $data['create_time']  = time();

            //校验参数
            $data['uid']          ||  $this->ajaxDie(30);
            $data['goods_id']     ||  $this->ajaxDie(60);
          
            //添加之前 判断一下该用户是否关注过该商品
            if(M('follow')->where(['uid' => $data['uid'],'goods_id' =>$data['goods_id']])->find()){
                $this->ajaxDie(44); //查询到 说明该用户已关注过该药品
            };
           
            if(M('follow')->add($data)){ //否则就添加
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }  
    //取消关注 
    public function cancel_follow(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            
            $goods_id    = I('post.goods_id',0,'intval');
            $data['uid'] = I('post.uid',0,'intval');

            //校验参数
            $data['uid']          ||  $this->ajaxDie(30);
            $data['goods_id']     ||  $this->ajaxDie(60);

            if(M('follow')->where(['uid' => $data['uid'],'goods_id' => $goods_id])->delete()){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
                $this->ajaxDie(43);
        }
    }
    //关注列表
    public function follow_lists(){
        if($this->client_type == 1){

            $this->secureVerify();

            //接收参数
            $data['uid']     = I('post.uid',0,'intval');

            //校验参数
            $data['uid']    ||  $this->ajaxDie(30);
           
            $Model = M('follow');
            $list = $Model->alias('a')
                ->field('a.goods_id,a.goods_name,b.goods_img,c.title')
                ->join('left join ydw_goods b on a.goods_id = b.id')
                ->join('left join ydw_goods_category c  on b.cate_id = c.id')
                ->where(['uid' => $data['uid']])
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

    //给app返回随机8条的搜索度前100的药材 
    public function search_hot(){
        if($this->client_type == 1){
            $num  = I('post.num',8,'intval');
            $list = M('search_hot')->limit(100)->order('goods_hot desc')->Field('goods_name')->select();

            foreach ($list as $k => $v) {
                $goods = M('goods')->where(['goods_name' => preg_replace("/\s+/",'',$v['goods_name'])])->field('id,goods_img')->find();
                $list[$k]['id']        = $goods['id'];
                $list[$k]['goods_img'] = $goods['goods_img'];
            }
            //随机取出8个不重复的数字下标
            $rand_num = get_rand_number(1,99,$num);
            foreach ($rand_num as $k => $v) {
                $result[$k] = $list[$v];
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

    //一键关注接口
    public function onekey_follow(){
        if($this->client_type == 1){

            $this->secureVerify();
            
            //接收参数
            $uid       =  I('post.uid',0,'intval');
            $goods_ids =  clearXSS(I('post.goods_ids'));

            //校验参数
            $uid       || $this->ajaxDie(30);
            $goods_ids || $this->ajaxDie(97);

            $arr = array();
            $ids = explode(',',$goods_ids);
            foreach ($ids as $k => $v) { 
                if(!is_numeric($v)){//安全性判断   如果传过来的id  不是数字  返回98 提示未数据不合法
                    $this->ajaxDie(98);
                } 
                $arr[$k]['uid']         = $uid;
                $arr[$k]['goods_id']    = $v;
                $arr[$k]['goods_name']  = M('goods')->where(['id' => $v])->getField('goods_name');
                $arr[$k]['create_time'] = time();
                //安全性判断   如果传过来的id  查询不到药材  返回98 提示未数据不合法
                if(!$arr[$k]['goods_name']){
                    $this->ajaxDie(98);
                }
            }

            if(M('follow')->addall($arr)){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }  
        }else{
            $this->ajaxDie(43);
        }
    }
}
