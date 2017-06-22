<?php
/**
 * 抓取数据控制器
 * User: jingwei
 * Date: 2016-11-30
 */

namespace Home\Controller;

class GrabController extends HomeController{

    public $grab;

    //初始化
    protected function _initialize(){
        parent::_initialize();
        $this->grab=D('Grab');
    }

    //获取（周，月，季度，年）价格变化幅度
    //$id 表示抓取药材表中的当前价格
    public function priceRange(){
        $_GET['i']='247';
        $id=I('get.i',0,'intval');
        if($id<=0){
            $rs['code']=0;
            $rs['msg']='获取失败';
        }
        $rs['data']=$this->grab->priceRange($id); dump($rs['data']);exit;
        $rs['code']=1;
        $rs['msg']='获取成功';
        $this->ajaxReturn($rs);
    }

}
