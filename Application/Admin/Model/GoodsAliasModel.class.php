<?php

namespace Admin\Model;
use Think\Model;

/**
 * 分类模型
 * @author 吴鹏飞重构
 */
class GoodsAliasModel extends Model{

    /**
     * 处理提交的数据
     * @param unknown $data
     */
    public function operateData($data){
        if((int)$data['id'] > 0){
            // $map['goods_id'] = $data['goods_id'];
            $map['alias_name'] = $data['alias_name'];
            if(M('GoodsAlias')->where($map)->find()){ $this->error = '修改失败';return 2;};
            if(M('searchHot')->where(['goods_name' => $data['old_alias_name'],'alias_name' => $data['goods_name']])->find()){
                $datas['goods_name'] = $data['alias_name'];
                M('searchHot')->where(['goods_name' => $data['old_alias_name'],'alias_name' => $data['goods_name']])->save($datas);
            }else{
                $datas['goods_name'] = $data['alias_name'];
                $datas['goods_hot'] = 1;
                $datas['alias_name'] = $data['goods_name'];
                M('searchHot')->data($datas)->add();
            }
            $res = $this->save($data);
            if($res !== false){
                $this->error = '更新成功';return 1;
            }
            $this->error = '更新失败';return 0;
        }else{
            // $map['goods_id'] = $data['goods_id'];
            $map['alias_name'] = $data['alias_name'];
            if(M('GoodsAlias')->where($map)->find()){ $this->error = '新增失败';return 2;};
                if(M('searchHot')->where(['goods_name' => $data['goods_name'],'alias_name' => $data['alias_name']])->find()){}else{
                    $datas['goods_name'] = $data['alias_name'];
                    $datas['goods_hot'] = 1;
                    $datas['alias_name'] = $data['goods_name'];
                    M('searchHot')->data($datas)->add();
                }
            };
            $res = $this->data($data)->add();
            if((int)$res > 0){
                $this->error = '新增成功';return 1;
            }
            $this->error = '新增失败';return 0;
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
            $list[$k]['goods_name'] = M('goods')->where(['id' => $v['goods_id']])->getField('goods_name');
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
        $info = M('GoodsAlias')->where(['id' => $id])->find();
        $goods = $info['goods_id'];
        $goods_id = M('goods')->where(['id' => $goods])->Field('cate_id,id,goods_name')->find();
        $good['c'] = $goods_id['cate_id'];
        $good['i'] = $goods_id['id'];
        $good['g'] = $goods_id['goods_name'];
        $goods = json_encode($good);
        return $goods;
    }
}
