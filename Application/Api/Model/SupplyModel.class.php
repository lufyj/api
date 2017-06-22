<?php 
namespace Api\Model;
use Think\Model;

/**
 * 求购 模型
 * @author wpf
 *
 */
class SupplyModel extends Model{
		
	/**
	 * 返回供应+直销信息
	 * 
	 */
	public function getSupplyData(){
		//取出供应5条信息  where条件暂时不加(后台功能完善后加上)
		$supply =  $this->field('id,goods_name,goods_attr_name,price_type,price,standard,origin_type,origin_area,supply_area')
			->order('id desc')
			->limit(10)
			->select();
		foreach ($supply as $k => $v) {
			if($v['price_type'] == '2'){
				$supply[$k]['price'] = '面议';
			}

			//供应产地有进口 较广 在程序中先处理
			if($v['origin_type'] == '1'){
				$supply[$k]['origin_area'] = '较广';
			}else if($v['origin_type'] == '2'){
				$supply[$k]['origin_area'] = '进口';
			}

			$supply[$k]['type']           = '供应';
			$supply[$k]['is_recommend']   = '0';//是否是推荐 1是推荐 0是不推荐
			
		}
		/***供应结束*****/

		//取出产地直销信息
		$direct =  M('company_direct')->field('id,goods_name,goods_attr_name,price,standard,origin_area,stock_area')
			->where(['is_recommend' => 1])
			->select();

		//声明数组
		$directs = array();

		if($direct){
			//随机取出3条数据
			$rand_num  = array_rand($direct,3);

			foreach ($rand_num as $k => $v) {
            	$directs[$k] = $direct[$v];
	        }

			foreach ($directs as $k => $v) {
				if($v['price'] == '0'){
					$directs[$k]['price'] = '面议';
				}
				$directs[$k]['type']           = '直销';//是否是供应
				$directs[$k]['supply_area']    = $v['stock_area'];
				$directs[$k]['is_recommend']   = '1';//是否是推荐  1是推荐 0是不推荐
			}
		}
		/***直销结束*****/
		$data = array_merge($directs,$supply);
		
		return $data;
	}
	//获取发布供应时的配置
	public function getSupConf($type){
		if(!in_array($type,array(1,2,3,4))){
			return false;
		}
		//1 单位 2 有效期限 3票据 4质量
		if($type == '1'){
			$data = C('NUM_UNIT');
		}else if($type == '2'){
			$data = C('VALID_DAYS2');
            foreach ($data as $k => $v){
                if(is_numeric($v)){
                    $data[$k] = $v.'天';
                }
            }
		}else if($type == '3'){
			$data = array(//票据
					'0' => '不提供票据',
                    '1' => '发票',
                    '2' => '收购手续',
                    '3' => '发票或收购手续'
				);
		}else if($type == '4'){
			$data = array(//质量
					'0' =>'待确定',
					'1' =>'达到出口标准和药典标准',
					'2' =>'达到出口标准', 
					'3' =>'达到省标',
					'4' =>'达到2010版药典标准',
					'5' =>'达到2015版药典标准'
				);
		}
		return $data;
	}
	/**
     * 保存发布供应信息 1.4版本
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
            'price_type' => $data['price_type'],
            'price' => $data['price'],
            'maxprice' => $data['mprice'],
            'origin_type' => $data['o_type'],
            'origin_area' => $data['o_area'],
            'origin_code' => $data['o_code'],
            'contacts' => $data['contacts'],
            'mobile' => $data['mobile'],
            'supply_area' => $data['s_area'],
            'supply_code' => $data['s_code'],
            'qq' => $data['qq'],
            'details' => $data['details'],
            'pic' => $data['imgs'],
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
            'catalogue'  => $data['catalogue'],
            'source_type' => $data['s_type']
        );

        /*设置权重值*/
        $clean_data['weight'] = $this->_setweight($clean_data['price'], $clean_data['pic'], $clean_data['uid']);

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

    /*设置权重值 规则： 价格 50，图片 30 企业 18*/
    private function _setweight($price = 0, $pic = '', $uid = 0) {
        $weight = 0;
        if ($price > 0) {
            $weight = $weight + 50;
        }

        if ($pic) {
            $weight = $weight + 30;
        }

        if ($uid > 0) {
            $where = [
                'uid' => $uid,
                'status' => 1,
            ];
            $count = M('companyAuthen')->where($where)->count();
            if ($count) {
                $weight = $weight + 18;
            }
        }

        return $weight;
    }
    //供应编辑时,将用户删除的图片从服务器中删除
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