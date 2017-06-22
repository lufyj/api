<?php
namespace Admin\Model;
use Think\Model;

/**
 * 前台用户管理模型
 * @author wpf
 */
class AdMachineModel extends Model {
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
			$data['create_time'] = time();
			$data['update_time'] = time();
			$res = $this->data($data)->add();
			if((int)$res > 0){
				$this->error = '新增成功';return 1;
			}
			$this->error = '新增失败';return 0;
		}
	}
    /*$goods = D('adMachine')->getGoods($id);

    *获得商品
    */
    public function getGoods($id){
        $info = M('adMachine')->where(['id' => $id])->find();
        $goods = $info['goods'];
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
}
