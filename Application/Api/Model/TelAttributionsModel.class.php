<?php
namespace Api\Model;
use Think\Model;
/**
 * 商品模型
 * @author wpf
 */

class TelAttributionsModel extends Model {
	
    /**
     * 获取手机号归属地
     */
    public function getTel_attr($mobile){
        $number   = substr($mobile,0,7);
        $tel_attr = $this->where(['number' => $number])->field('province,city')->find();
        if($tel_attr['province'] == $tel_attr['city'] ){
            return  $tel_attr['province']; 
        }else{
            return  $tel_attr['province'].$tel_attr['city']; 
        }

    }
}
