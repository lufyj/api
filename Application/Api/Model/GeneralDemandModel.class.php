<?php 
namespace Api\Model;
use Think\Model;

/**
 * 求购 模型
 * @author wpf
 *
 */
class GeneralDemandModel extends Model{
		
	/**
     * 保存发布求购信息
     * @param unknown $data
     */
    public function savePublish($data) {
        $clean_data = array(
            'uid' => $data['uid'],
            'num' => $data['num'],
            'unit' => $data['unit'],
            'ticket_id' => $data['pjid'],
            'ticket' => $data['ticket'],
            'standard_id' => $data['zlid'],
            'standard' => $data['standard'],
            'price' => $data['price'],
            'origin_type' => $data['o_type'],
            'origin_area' => $data['o_area'],
            'origin_code' => $data['o_code'],
            'contacts' => $data['contacts'],
            'mobile' => $data['mobile'],
            'stock_area' => $data['s_area'],
            'stock_code' => $data['s_code'],
            'qq' => $data['qq'],
            'details' => $data['details'],
            'create_time' => time(),
            'update_time' => time(),
            'cate_id' => $data['cid'],
            'cate_name' => $data['cn'],
            'goods_id' => $data['gid'],
            'goods_name' => $data['gn'],
            'goods_attr_id' => $data['aid'],
            'goods_attr_name' => $data['an'],
            'remain_days' => $data['remain_days'],
            'valid_days' => $data['valid_days'],
            'source_type' => $data['s_type']
        );

        /*设置权重值*/
        $clean_data['weight'] = $this->_setweight($clean_data['uid']);

        //判断是修改还是添加
        if(intval($data['id']) > 0){//大于0 是修改
        	$res = $this->where(['id' => $data['id']])->save($clean_data);
        }else{//添加
        	$res = $this->add($clean_data);
        }
        return $res;
    }

     /*设置权重值 规则： 企业 18*/
    private function _setweight($uid = 0) {
        $weight = 0;
        if($uid>0){
            $where=[
            'uid'=>$uid,
            'status'=>3
            ];
            $count=M('companyAuthen')->where($where)->count();
            if($count){
                $weight=$weight+18;
            }
        }

        return $weight;
    }
}
?>