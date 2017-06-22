<?php
namespace Admin\Controller;

/**
 * 行政区域控制器
 * @Author jingwei
 *
 */
class RegionController extends AdminController {
	
	//获取指定父ID下的区域信息
    public function getRegion($parentId=1){
    	$model = M('region');
		$data = $model->field('REGION_CODE as code,REGION_NAME as area')
			->where(array('PARENT_ID' => $parentId))
			->select();
		return $data;
    }

	/*
	 * 返回省份 select 列表
	 * 如果 提供ID，则此项默认会被选中
	 *
	 * param GET int 省份ID
	 *
	 * @return string option html
	 */
	public function ajaxGetProHtml() {
		if(IS_AJAX){
			$id = I("get.id", 0, 'intval');//省id
			//读取所有省份
			$list = M('Region')->field('REGION_CODE as code,REGION_NAME as area')
				->where(array('PARENT_ID' => 1))
				->select();
			$html = '<option value=\'0\'>请选择</option>';
			$is_select = '';
			foreach ($list as $v){				
				$isSelect = ($v['code'] == $id) ? " selected='selected'" : '';				
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
	public function ajaxGetCityHtml() {
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
	public function ajaxGetAreaHtml() {
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

	/*
	 * 返回省份 select 列表
	 * 如果 提供ID，则此项默认会被选中
	 *
	 * param GET int 省份ID
	 *
	 * @return string option html
	 */
	public function getProHtmlById($id=0) {

		//读取所有省份
		$list = M('Region')->field('REGION_CODE as code,REGION_NAME as area')
			->where(array('PARENT_ID' => 1))
			->select();
		$html = '<option value=\'0\'>请选择</option>';
		$is_select = '';

		foreach ($list as $v){
			$isSelect = ($v['code'] == $id) ? " selected='selected'" : '';
			$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
		}

		return $html;
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
	public function getCityHtmlById($id=0,$city_id=0) {

		$list = M()->query('select REGION_CODE as code,REGION_NAME as area from ydw_region where REGION_CODE like \''.$id.'%\' and length(REGION_CODE) = 4');
		$html = '<option value=\'0\'>请选择市</option>';
		$isSelect = '';
		foreach ($list as $v) {
			$isSelect = ($v['code'] == $city_id) ? " selected='selected'" : '';
			$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
		}

		return $html;
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
	public function getAreaHtmlById($id=0,$area_id=0) {

		$list = M()->query('select REGION_CODE as code,REGION_NAME as area from ydw_region where REGION_CODE like \''.$id.'%\' and length(REGION_CODE) = 6');
		$html = '<option value=\'0\'>请选择区/县</option>';
		$isSelect = '';
		foreach ($list as $v) {
			$isSelect = ($v['code'] == $area_id) ? " selected='selected'" : '';
			$html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
		}

		return $html;
	}

	//根据code值不同输出对应的地址信息
	public function addressHtml($code=0){
		if(strlen($code)==2){
			$html['pro']=$this->getProHtmlById($code);
		}elseif(strlen($code)==4){
			$code=sliceCode($code);
			$html['pro']=$this->getProHtmlById($code[0]);
			$html['city']=$this->getCityHtmlById($code[0],$code[1]);
		}elseif(strlen($code)==6){
			$code=sliceCode($code);
			$html['pro']=$this->getProHtmlById($code[0]);
			$html['city']=$this->getCityHtmlById($code[0],$code[1]);
			$html['area']=$this->getAreaHtmlById($code[1],$code[2]);
		}

		return $html;
	}
}