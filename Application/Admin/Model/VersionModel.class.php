<?php
namespace Admin\Model;
use Think\Model;

/**
 * 前台用户管理模型
 * @author wpf
 */
class VersionModel extends Model {
    /**
     * 获取广告机列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getList($show_num = 0, $page_num = 0){
        $count = $this->count();//获取总条数
        $total_page = ceil($count / $show_num);//获取总页数
        if($total_page > 0 && $total_page < $page_num){
            $page_num = $total_page;
        }elseif ($total_page == 1){
            $total_page = 1;
        }
        $limit = ($page_num - 1) * $show_num . ',' .$show_num;
        $list = $this
            ->order('id desc')
            ->limit($limit)
            ->select();
        return array(
            'list'   => $list,
            'page_num' => $page_num,
            'show_num' => $show_num,
            'all_count'  => $count,
            'total_page' => $total_page
        );      
    } 
      /**
     * 处理提交的数据
     * @param unknown $data
     */
    public function operateData($data){
        if((int)$data['id'] > 0){
            $data['update_time'] = time();
            $res = $this->save($data);
            if($res !== false){
                $this->error = '更新成功';return 1;
            }
            $this->error = '更新失败';return 0;
        }else{
            //判断手机号是否已经存在
            $data['create_time'] = time();
            $data['update_time'] = time();
            $res = $this->data($data)->add();
            if((int)$res > 0){
                $this->error = '新增成功';return 1;
            }
            $this->error = '新增失败';return 0;
        }
    }
}
