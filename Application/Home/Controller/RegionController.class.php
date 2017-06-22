<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 区域模型
 * @author wpf
 *
 */
class RegionController extends HomeController{
	
	/**
	 * 展示省份
	 */
    public function index(){
    	
    	$model = M('region');
    	$parent = I('get.parent',0,'intval');
    	
    	if((int)$parent > 0){
    		
    	}else{
    		//读取所有省份
    		$data = $model->field('REGION_CODE as code,REGION_NAME as area')
    			->where(array('PARENT_ID' => 1))
    			->select();
    	}
    	$this->ajaxReturn($data);       
    }

    function _get_regions ($parent = 0){
    	$model = M('region');
    	if((int)$parent > 0){
    		$model->field('REGION_CODE as code,REGION_NAME as area')
    			->where(array('PARENT_ID' => 1))
    			->select();
    	}else{
    		//读取所有省份
    	
    	}
        /* if($type){
            $sql = "SELECT region_id, region_name FROM ".C('DB_PREFIX')."region WHERE parent_id ='$parent'";
            return M()->query($sql);
        }
        return ""; */

    }

	/*
	 * 返回省份 select 列表
	 * 如果 提供ID，则此项默认会被选中
	 *
	 * param GET int 省份ID
	 *
	 * @return string option html
	 */
	public function getProHtml() {
		if(IS_AJAX){
			$id = I("get.id",0,'intval');//省id			
			//读取所有省份
			$list = M('Region')->field('REGION_CODE as code,REGION_NAME as area')
				->where(array('PARENT_ID' => 1))
				->select();
			$html = '<option value=\'0\'>请选择</option>';		
			$is_select = '';				
			
			foreach ($list as $v){
				if($v['code'] == $id){
					$isSelect = ($v['code'] == $id) ? " selected='selected'" : '';
				}
				$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
			}
			
			exit($html);	
		}		
	}

	/*
	 * 返回城市 select 列表
	 * 如果 提供city_id，则此项默认会被选中
	 *
	 * @param GET int id 所属省份ID
	 * @param GET int city_id 所属城市 ID
	 *
	 * @return string option html
	 */
	public function getCityHtml() {
		$id = I("get.id",0,'intval');
		$city_id = I("get.city_id",0,'intval');			
		$list = M()->query('select REGION_CODE as code,REGION_NAME as area from ydw_region where REGION_CODE like \''.$id.'%\' and length(REGION_CODE) = 4');
		
		$html = '<option value=\'0\'>请选择市</option>';
		$isSelect = '';
		
		foreach ($list as $v) {
			$isSelect = ($v['code'] == $city_id) ? " selected='selected'" : '';
			$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
		}
		exit($html);
	}

	/*
	 * 返回县区 select 列表
	 * 如果 提供county_id，则此项默认会被选中
	 *
	 * @param GET int id 所属市区ID
	 * @param GET int county_id 所属县区 ID
	 * @return string option html
	 *
	 * 此方法同 getCityHtmlSelect 目的 为了方便识别
	 */
	public function getAreaHtml() {
		$id = I("get.id",0,'intval');
		$area_id = I("get.area_id",0,'intval');//暂时不用
		$list = M()->query('select REGION_CODE as code,REGION_NAME as area from ydw_region where REGION_CODE like \''.$id.'%\' and length(REGION_CODE) = 6');
		
		$html = '<option value=\'0\'>请选择区/县</option>';
		$isSelect = '';
		
		foreach ($list as $v) {
			$isSelect = ($v['code'] == $area_id) ? " selected='selected'" : '';
			$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
		}
		exit($html);		
	}
}