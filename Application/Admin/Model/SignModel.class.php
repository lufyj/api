<?php
namespace Admin\Model;
use Think\Model;
/**
 * 商品模型
 * @author wpf
 */

class SignModel extends Model {
	
    /**
     * 获取药品列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($params, $show_num = 0, $page_num = 0){
        $m=Date('n');
        $y=Date('Y');
        if($m == 1){
             $month = ($y-1).'12';
        }else{
            $month = date('Ym')-1;
        }
        $data = array('month' => $month);
        $all = $this->where($data)->field('uid,max(num)')->group('uid')->select();//获取总条数
        $count=count($all);
        $total_page = ceil($count / $show_num);//获取总页数
        if($total_page > 0 && $total_page < $page_num){
            $page_num = $total_page;
        }elseif ($total_page == 1){
            $total_page = 1;
        }
        $limit = ($page_num - 1) * $show_num . ',' .$show_num;
        $list = $this->where($data)
            ->field('uid,max(num)')
            ->group('uid')
            ->limit($limit)
            ->select();
               
        foreach ($list as $k => $v) {
            $list[$k]['realname'] = M('user')->where(['id' => $v['uid']])->getField('realname');
            $list[$k]['mobile'] = M('user')->where(['id' => $v['uid']])->getField('mobile');
        }
        return array(
            'list'   => $list,
            'page_num' => $page_num,
            'show_num' => $show_num,
            'all_count'  => $count,
            'total_page' => $total_page
        );      
    } 
}
