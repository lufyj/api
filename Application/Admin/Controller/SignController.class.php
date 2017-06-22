<?php
/*
 * 后台签到控制器
 */

namespace Admin\Controller;
use Think\Upload\Driver\Qiniu\QiniuStorage;


class SignController extends AdminController {

    public function index(){
        $show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
        $page_num = I('get.page_num', 1, 'intval');//页码
        $condition = I('get.condition');
        $data = D('Sign')->getList($condition, $show_num, $page_num);
        $this->assign('data', $data);
        $y=Date('Y');
        $m=Date('n');
        $month=($m==1)?($y-1).'年12':($m-1);
        $this->assign('y',$y); //当前年
        $this->assign('m',$m); //当前月
        $this->assign('month',$month); //上个月
        if(IS_AJAX && IS_GET){
            $this->display('table');
            exit;
        }
        $this->meta_title = '用户列表';
        $this->display();
    }

    public function ajaxGetView(){

        $uid=I('get.uid',0,'intval');
        if($uid && IS_AJAX){
            $mm = I('get.month','',trim);
            $yy = I('get.year','',trim);
            $mon = $yy.$mm;
            $list = M('Sign')->where(['month' => $mon,'uid' => $uid])->select();
            foreach($list as $k => $v){
                $list[$k]['data'] =  substr($v['time'], 4);
            }

            $rs['status']=1;
            $rs['msg']='查看成功';
            $rs['info']=$list;
        }else{
            $rs['status']=0;
            $rs['msg']='查看失败';
        }

        $this->ajaxReturn($rs);
    }
}
