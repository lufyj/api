<?php
namespace Api\Controller;
use Think\Controller;
class AppMarketController extends BaseController {
    //用户添加药材行情
    public function add_Market(){
        if($this->client_type == 1){
            $this->secureVerify();

            //接收数据
            $data['uid']         = I('post.uid',0,'intval');
            $data['title']       = clearXSS(I('post.title'));
            $data['thumb']       = clearXSS(I('post.thumb'));
            $data['status']      = 1;
            $data['author']      = M('user')->where(['id' => $data['uid']])->getField('realname');
            $data['content']     = clearXSS(I('post.content'));
            $data['description'] = clearXSS(I('post.content'));
            $data['create_time'] = time();

            //校验数据
            $data['uid']         || $this->ajaxDie(30);
            if(!$data['title']   || mb_strlen($data['title'],'utf-8')>30){
                $this->ajaxDie(105);
            }
            if(!$data['content'] || mb_strlen($data['content'],'utf-8')>255 ){
                $this->ajaxDie(106);
            }

            //添加数据
            $result = M('user_goods_market')->add($data);
            if($result){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        } 
    }

    //用户添加药材行情(图片上传)
    public function upload_pic(){
       if($this->client_type == 1){    
            $upload=new \Org\Com\ImageUpload();
            //设置信息
            $upload->maxSize=314158691111;//大小
            $upload->exts=array("jpeg","jpg","png","gif");//类别
            $upload->rootPath="./Uploads/Picture/Umarket/";//路径
            $upload->subName =array('date', 'Ymd');
            $upload->saveExt='jpg';
            //执行上传
            $info=$upload->upload();                                                                             
            if(!$info){
                $this->ajaxDie(41);
            }else{
                foreach($info as $file){
                    //实例化图像处理类
                    $pic=new\Think\Image();
                    //打开文件
                    $pic->open("./Uploads/Picture/Umarket/".$file['savepath'].$file['savename']);
                    //起名
                    $s=$file['savepath']."thumb_200x200_".$file['savename'];
                    $a=$file['savepath'].$file['savename'];
                    //缩放
                    $pic->thumb(200,200)->save("./Uploads/Picture/Umarket/".$s);
                    $path = '/Uploads/Picture/Umarket/'.$s;
                    $shuzu[]=$path;
                   }  
                }
               $this->ajaxDie(1,$shuzu);
               
        }else{
            $this->ajaxDie(43);
        }
    }

    //我的行情文章(列表)
    public function user_market(){
         if($this->client_type == 1){
            $this->secureVerify();

            //接收数据
            $uid      =  I('post.uid',0,'intval');
            $num      =  I('post.num',10,'intval');
            $minId    =  I('post.minId',0,'intval');
            $status   =  I('post.status',0,'intval');
           
            //校验数据
            $uid      || $this->ajaxDie(30);

            //组装条件
            $data['uid'] =   $uid;
            $minId       &&   $data['id'] = array('lt',$minId);
            if($status == '1'){
                $data['status'] = '1';

            }else if($status == '2'){
                $data['status'] = '2';

            }else if($status == '3'){
                $data['status'] = '3';

            }
           
            $list = M('user_goods_market')->field('id,title,description,thumb,author,create_time,status,market_id')->where($data)->order('id desc')->limit($num)->select();

            foreach ($list as $k => $v) {
                if($v['thumb']){
                    $thumb = explode(',',$v['thumb']);
                    $list[$k]['thumb'] = $thumb[0];
                }else{
                    $list[$k]['thumb'] = '';
                }

                //如果状态等于3 则说明该文章通过了审核,则去取该文章阅读量,和回复数
                if($v['status'] == '3'){
                    $list[$k]['view'] = M('goods_market')->where(['belong_id' => $v['id']])->getField('view');
                    
                    //获取通过文章的id
                    $id     = M('goods_market')->where(['belong_id' => $v['id']])->getField('id');
                    $reward = M('user_reward')->where(['getreward_id' => $uid,'market_id' => $id])->getField('money',true);//查询出所有打赏金额
                    $reward = array_sum($reward);//计算打赏金额的和

                    $reward  || $reward = '0';
                    $list[$k]['total_reward']  = $reward;
                    $list[$k]['total_comment'] = M('user_comment')->where(['market_id' => $id])->count();
                }
                $list[$k]['descriptions'] =  $v['description'];
                $list[$k]['create_time']  =  D('user')->timeShow($v['create_time']);
            }

            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        } 
    }
    //新增评论
    public function add_comment(){
        if($this->client_type==1){
            $this->secureVerify();

            //接受数据
            $data['send_id']        =  I('post.uid',0,'intval');//发表评论用户id'
            $data['content']        =  clearXSS(I('post.content'));//评论内容
            $data['content']        =  str_replace("\n","",$data['content']);
            $data['send_name']      =  M('user')->where(['id' => $data['send_id']])->getField('realname');//发表评论用户名称
            $data['market_id']      =  I('post.market_id',0,'intval');//评论所属文章id
            $data['get_send_id']    =  I('post.get_send_id',0,'intval');//被评论用户id
            $data['create_time']    =  time();//评论时间


            $data['send_id']        || $this->ajaxDie(30);
           
            if($data['get_send_id']){

                $data['get_send_name']      = M('user')->where(['id' => $data['get_send_id']])->getField('realname');//被评论用户名称
                $data['comment_type']       = '2';
                $data['comment_id']         =  I('post.comment_id',0,'intval');//被评论的评论id：当评论类型为评论时大于0，否则等于0

                //验证用户评论的的评论是否存在
                $user_comment = M('user_comment')->where(['id' => $data['comment_id']])->find();

                //判断这个评论所属文章与用户传过来评论文章是否一致
                if($data['market_id'] != $user_comment['market_id']){
                    $this->ajaxDie(107);
                }
                //判断并评论的评论id里面的send_id 与用户传过来的get_send_id是否一致
                if($data['get_send_id'] != $user_comment['send_id']){
                    $this->ajaxDie(108);
                }
            }
          

            if(!$data['content'] || mb_strlen($data['content'],'utf-8')>255 ){
                 $this->ajaxDie(109);
            }
          
            $result = M('user_comment')->add($data);
            if($result){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43); 
        }
    }

    //返回指定文章评论详情
    public function market_comment(){
        if($this->client_type==1){
            $this->secureVerify();

            //接受数据
            $uid               = I('post.uid',0,'intval');
            $num               = I('post.num',0,'intval');
            $minId             = I('post.minId',0,'intval');
            $data['market_id'] = I('post.market_id',0,'intval');

            //校验参数
            $uid               || $this->ajaxDie(30);
            $data['market_id'] || $this->ajaxDie(110);
            $minId             && $data['id'] = array('gt',$minId);
            
  
            $list = M('user_comment')->field('id,send_id,send_name,get_send_name,content')->where($data)->order('id asc')->limit($num)->select();

            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43); 
        } 
    }

    //我的评论(列表)接口
    public function user_comment(){
        if($this->client_type==1){
            $this->secureVerify();

            //接收参数
            $uid     = I('post.uid',0,'intval');
            $num     = I('post.num',10,'intval');
            $pic     = M('user')->where(['id' => $uid])->getField('head_pic');
            $minId   = I('post.minId',0,'intval');
           
            //校验参数
            $uid     || $this->ajaxDie(30);
            $minId   && $data['id'] = array('lt',$minId);
            
            //组装参数
            $data['send_id']  =  $uid;

            $list = M('user_comment')->field('id,send_id,send_name,create_time,content,market_id')->where($data)->order('id desc')->limit($num)->select();

            foreach ($list as $k => $v) {
                $market                    = M('goods_market')->field('title,author')->where(['id' => $v['market_id']])->find();
                $list[$k]['title']         = $market['title'];
                $list[$k]['author']        = $market['author'];
                $list[$k]['head_pic']      = $pic;
                $list[$k]['create_time']   = D('user')->timeShow($v['create_time']);
                $list[$k]['total_comment'] = M('user_comment')->where(['market_id' => $v['market_id']])->count();
            }

            if($list){
                $this->ajaxDie(1,$list);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43); 
        } 
    }

    //行情删除接口
    public function del_market(){
        if($this->client_type==1){
            $this->secureVerify();

            //接收参数
            $uid        =  I('post.uid',0,'intval');
            $market_id  =  I('post.id',0,'intval');
            $market     =  M('user_goods_market')->field('uid,status')->where(['id' => $market_id])->find();
            
            //校验参数
            $uid        || $this->ajaxDie(30);
            if($market['uid'] != $uid){
                $this->ajaxDie(111);
            }
            if($market['status'] != '2'){
                $this->ajaxDie(112);
            }

            if(M('user_goods_market')->where(['id' => $market_id])->delete()){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
           $this->ajaxDie(43); 
        } 
    }
    //打赏详情列表接口
    public function reward_list(){
        if($this->client_type==1){
            $this->secureVerify();

            //接收参数
            $data = array();
            $data['uid']     = I('post.uid',0,'intval');//用户id
            $market_id       = I('post.market_id',0,'intval');

            //校验参数
            $market_id      || $this->ajaxDie(110); 

            //先判断查看这篇文章是否是打赏详情的用户自己看
            $belong_id = M('goods_market')       -> where(['id' => $market_id]) -> getField('belong_id');
            $uid       = M('user_goods_market')  -> where(['id' => $belong_id]) -> getField('uid');
            
            $list = M('user_reward')->Field('reward_id,reward_name,money')->where(['market_id' => $market_id])->select();
            if($data['uid'] == $uid){//是用户本人查看   
                foreach ($list as $k => $v) {
                    $list[$k]['head_pic'] = M('user')->where(['id' => $v['reward_id']])->getField('head_pic');
                }
            }else{//不是用户本人查看
                foreach ($list as $k => $v) {
                    $list[$k]['head_pic'] = M('user')->where(['id' => $v['reward_id']])->getField('head_pic');
                    $list[$k]['money']    = '';
                }
            }

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
