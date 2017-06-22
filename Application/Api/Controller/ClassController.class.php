<?php
namespace Api\Controller;
use Think\Controller;
class ClassController extends BaseController {
  //广告机获取分类列表接口
  public function Chinese_class(){
      //获取全部分类,并获得分类下所有药材,分类药材 按名称字数从小到大排序 
      if($this->client_type == 1){
        $list  = $this->getC(1,1);

        //声明一个新数组,将重缓存中拿到的数据,放入新数组中
        $lists  = array();
        $_child = array();
        $hot    = array();

        foreach ($list as $k => $v) {
          //将药材的键名修改(药材)
          foreach ($v['_child'] as $key => $val) {
            $_child[$key]['id']         = $val['id'];
            $_child[$key]['goods_name'] = $val['gn'];
            $_child[$key]['cate_id']    = $v['id'];
          }
          //将药材的键名修改(热门药材)
          foreach ($v['_hot'] as $ke => $va) {
            $hot[$ke]['id']         = $va['id'];
            $hot[$ke]['goods_name'] = $va['gn'];
            $hot[$ke]['cate_id']    = $v['id'];
          }
          $lists[$k]['id']    = $v['id'];
          $lists[$k]['title'] = $v['title'];
          $lists[$k]['hot']   = $hot;
          $lists[$k]['good']  = $_child;
            
        }
        $this->ajaxDie(1,$lists);
    }else{
        $this->ajaxDie(43);
    }
  }

  // 手机app 获取分类 
  public function goods_category(){
     if($this->client_type == 1){
        $list  = $this->getC();
        $this->ajaxDie(1,$list);
     }else{
       $this->ajaxDie(43);
     }    
  }

  //手机app 根据药品分类 来获取所有该分类下的药品
  public function goods_name(){
    if($this->client_type == 1){
        $cate_id  = I('post.cate_id',0,'intval');
        $cate_id  || $this->ajaxDie(65);
        $list     = $this->getC(1,1);

        //声明数组
        $_child   = array();
        $hot      = array();
        foreach ($list as $k => $v) {
            if($v['id'] == $cate_id){
              //将药材的键名修改(药材)
              foreach ($v['_child'] as $key => $val) {
                $_child[$key]['id']         = $val['id'];
                $_child[$key]['goods_name'] = $val['gn'];
                $_child[$key]['cate_id']    = $v['id'];
              }
              //将药材的键名修改(热门药材)
              foreach ($v['_hot'] as $ke => $va) {
                $hot[$ke]['id']         = $va['id'];
                $hot[$ke]['goods_name'] = $va['gn'];
                $hot[$ke]['cate_id']    = $v['id'];
              }
            }
        }
        $this->ajaxDie(1,$_child,$hot);
    }else{
      $this->ajaxDie(43);
    }
  }
  //清除药材缓存
  public function clear(){
    $cates = S('global_cates');
    if($cates){
      S('global_cates',NULL);
    }
    $this->ajaxDie(1);
  }
}
