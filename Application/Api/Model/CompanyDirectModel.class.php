<?php 
namespace Api\Model;
use Think\Model;

/**
 * 直销 模型
 * @author wpf
 *
 */
class CompanyDirectModel extends Model{
	/**
     * 保存发布直销信息
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
            'maxprice' => $data['mprice'],
            'origin_area' => $data['o_area'],
            'origin_code' => $data['o_code'],
            'contacts' => $data['contacts'],
            'mobile' => $data['mobile'],
            'telephone' => $data['tel'],
            'stock_area' => $data['s_area'],
            'stock_code' => $data['s_code'],
            'qq' => $data['qq'],
            'pic' => $data['imgs'],
            'create_time' => time(),
            'update_time' => time(),
            'cate_id' => $data['cid'],
            'cate_name' => $data['cn'],
            'goods_id' => $data['gid'],
            'goods_name' => $data['gn'],
            'goods_attr_id' => $data['aid'],
            'goods_attr_name' => $data['an'],
            'source_type' => $data['s_type']
        );

        /*设置权重值*/
        $clean_data['weight'] = $this->_setweight($clean_data['price'], $clean_data['pic']);

        //判断是修改还是添加
        if(intval($data['id']) > 0){//大于0 是修改
    	  	$pic = $this->where(['id' => $data['id']])->getField('pic');
            if($pic &&  $pic != $clean_data['pic']){
               $this->diffPic($pic,$clean_data['pic']);
            }
        	$res = $this->where(['id' => $data['id']])->save($clean_data);
        }else{//添加
        	$res = $this->add($clean_data);
        }
        return $res;
    }

    /*设置权重值 规则： 价格 50，图片 30*/
    private function _setweight($price = 0, $pic = '') {
        $weight = 0;
        if ($price > 0) {
            $weight = $weight + 50;
        }

        if ($pic) {
            $weight = $weight + 30;
        }

        return $weight;
    }
    //供应直销时,将用户删除的图片从服务器中删除
    private function diffPic($o_pic,$n_pic){//删除成功与否,不影响用户提交
        //将两个字符串转化成数组
        $n_pic = explode(',',$n_pic);
        $o_pic = explode(',',$o_pic);

        $diff = array_diff($o_pic, $p_pic);
        if($diff){
             foreach ($diff as $k => $v) {
                $v   = substr($v,1);//删除图片路径中的/
                //删除原图 thumb_200x200_ 是固定的    如果想截取的话 图片路径长度固定  可以直接    substr($v,32,14) 截取
                $big = str_replace('thumb_200x200_','',$v);
                unlink($v);//删除缩略图
                unlink($big);//删除原图
       		}
        }
        return ture; 
    }	
}
?>