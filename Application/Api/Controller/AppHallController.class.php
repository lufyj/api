<?php
namespace Api\Controller;
use Think\Controller;
class AppHallController extends BaseController {
    //供应大厅
    public function supply_hall(){
        if($this->client_type == 1){
            //接收数据
            $num             =  I('post.num'  , 10,'intval');
            $page            =  I('post.page' , 0 ,'intval');
            $minId           =  I('post.minId', 0 ,'intval');
            $goods_name      =  clearXSS(I('post.goods_name'));

            $goods_attr_name =  clearXSS(I('post.goods_attr_name'));

            //组装条件
            $minId            &&   $date['id']              = array('lt',$minId);
            $goods_name       &&   $date['goods_name']      = $goods_name;
            $goods_attr_name  &&   $date['goods_attr_name'] = $goods_attr_name;
            $date['status']   = array('not in','-1');

            //支持两种分页方式(老版本使用的保留,新版本使用limit分页(老版本存在bug))
            if($page){
                //计算条数
                $total   =  ($page-1) * $num;
                $list    = M('supply')->where($date)->Field('id,uid,cate_id,cate_name,goods_id,goods_name,goods_attr_id,goods_attr_name,price_type,price,contacts,mobile')->order('weight desc,id desc')->limit($total,$num)->select();
            }else{
                $minId   && $data['id'] = array('lt',$minId);
                $list    = M('supply')->where($date)->Field('id,uid,cate_id,cate_name,goods_id,goods_name,goods_attr_id,goods_attr_name,price_type,price,contacts,mobile')->order('weight desc,id desc')->limit($num)->select();
            }

            foreach ($list as $key => $value) {
                //判断供应信息是否是企业用户
                $company_auth_status    =  M('user')->where(['id' => $value['uid']])->getfield('company_auth_status');
                $confirm_status         =  M('company_confirm')->where(['user_id' => $value['uid']])->getfield('confirm_status');
                if($company_auth_status == '2' && $confirm_status == '3'){
                    $list[$key]['auth'] = '1';
                }else{
                    $list[$key]['auth'] = '0';
                }


                $value['price_type'] == 2  &&  $list[$key]['price']= '面议';

                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
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
    //求购大厅
    public function demand_hall(){
         if($this->client_type == 1){
            //接收数据
            $num              =  I('post.num',  10 , 'intval');
            $minId            =  I('post.minId', 0 , 'intval');
            $page             =  I('post.page' , 0 ,'intval');
            $goods_name       =  clearXSS(I('post.goods_name'));
            $goods_attr_name  =  clearXSS(I('post.goods_attr_name'));

           //组装条件
            $minId            &&   $date['id']          = array('lt',$minId);
            $goods_name       &&   $date['goods_name']  = $goods_name;
            $goods_attr_name  &&   $date['goods_attr_name'] = $goods_attr_name;
            $date['status']   =    array('not in','-1');
           	
            //支持两种分页方式(老版本使用的保留,新版本使用limit分页(老版本存在bug))
            if($page){
            	//计算条数
            	$total   =  ($page-1) * $num;
            	$list    = M('demand')->where($date)->Field('id,uid,cate_id,cate_name,goods_id,goods_name,goods_attr_id,goods_attr_name,num,contacts,mobile,mobile_show')->order('weight desc,id desc')->limit($total,$num)->select();
            }else{
            	$minId   && $data['id'] = array('lt',$minId);
            	$list    = M('demand')->where($date)->Field('id,uid,cate_id,cate_name,goods_id,goods_name,goods_attr_id,goods_attr_name,num,contacts,mobile,mobile_show')->order('id desc')->limit($num)->select();
            }
            foreach ($list as $key => $value) {
                //判断求购信息是否是企业用户
                $company_auth_status     = M('user')->where(['id' => $value['uid']])->getfield('company_auth_status');
                $confirm_status          = M('company_confirm')->where(['user_id' => $value['uid']])->getfield('confirm_status');
                if($company_auth_status == '2' && $confirm_status == '3'){
                    $list[$key]['auth'] = '1';
                }else{
                    $list[$key]['auth'] = '0';
                }
                
                $list[$key]['mobile'] = maskPhone($value['mobile']);
                
                $value['price_type'] == 2  &&  $list[$key]['price']= '面议';

                if($value['origin_type'] == 1){
                    $list[$key]['origin_area'] ='较广';
                }else if($value['origin_type'] == 2){
                    $list[$key]['origin_area'] ='进口';
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
