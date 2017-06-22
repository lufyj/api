<?php 
namespace Home\Model;
use Think\Model;

/**
 * Banner 模型
 * @author wpf
 *
 */
class BannerModel extends Model{

	/**
	 * 获取用于展示的banner信息
	 * @param  string  $platform  [展示平台]-->PC,APP,WAP
	 * @param  integer $region_id [区域id]
	 * @return [array]             [banner信息]
	 */
	public function getAllBanners($platform = 1){
		if ($platform == 'PC' || $platform == 1) {
			$platform_id = 1;
		}else if ($platform == 'APP' || $platform == 2) {
			$platform_id = 2;
		}else if ($platform == 'WAP' || $platform == 3) {
			$platform_id = 3;
		}
		$conditon = array(
			'platform' => $platform_id,
			'status' => 1				
		);		
		$banners = $this->field('title,img_url,link_url,key_words')
			->where($conditon)->order('sort desc')->select();

		return $banners;
	}

}




?>