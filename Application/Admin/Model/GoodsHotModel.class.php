<?php

namespace Admin\Model;
use Think\Model;

/**
 * 分类模型
 * @author 吴鹏飞重构
 */
class GoodsHotModel extends Model{
	
    /**
     * 处理提交的数据
     * @param unknown $data
     */
    public function operateData($data){
        if((int)$data['id'] > 0){
            $res = $this->save($data);
            if($res !== false){
                $this->error = '更新成功';return 1;
            }
            $this->error = '更新失败';return 0;
        }else{
            $data['create_time'] = time();
            if(M('GoodsHot')->where(['cate_id' => $data['cate_id']])->find()){ $this->error = '新增失败';return 2;};
            $res = $this->data($data)->add();
            if((int)$res > 0){
                $this->error = '新增成功';return 1;
            }
            $this->error = '新增失败';return 0;
        }
    }
      /**
     * 获取药材分类列表
     * @param unknown $params
     * @param number $show_num 每页要显示的个数
     * @param number $page_num 要显示第几页数
     */
    public function getLists($show_num = 0, $page_num = 0){
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
        foreach ($list as $k => $v) {
            $list[$k]['cate_name'] = M('goods_category')->where(['id' => $v['cate_id']])->getfield('title');
            $goods = explode(',',$v['goods_id']);
            $map['id'] = array('in', $goods);
            $goods_name= M('goods')->where($map)->getfield('goods_name',true);
            $list[$k]['goods_name'] = implode(',',$goods_name);
        }
        return array(
            'list'   => $list,
            'page_num' => $page_num,
            'show_num' => $show_num,
            'all_count'  => $count,
            'total_page' => $total_page
        );      
    } 
    /*
       *获得商品
    */
    public function getGoods($id){
        $info = M('GoodsHot')->where(['id' => $id])->find();
        $goods = $info['goods_id'];
        if($goods != ''){
            $goods_id = explode(',',$goods);
            foreach ($goods_id as $k => $v) {
                $goods_id[$k] = M('goods')->where(['id' => $v])->Field('cate_id,id,goods_name')->find();
                $good[$k]['c'] = $goods_id[$k]['cate_id'];
                $good[$k]['i'] = $goods_id[$k]['id'];
                $good[$k]['g'] = $goods_id[$k]['goods_name'];
            }
            $goods = json_encode($good);
            return $goods;
        }
    }
}
