<?php
/**
 * 前台公司信息控制器
 * Author: jingwei
 * Date: 2016/9/29
 */
namespace Home\Controller;


class CompanyController extends HomeController{

    private $userId=0;
    //初始化登录验证
    protected function _initialize(){
        $user=session('user_sign');
        if(empty($user) || intval($user['id'])<=0){
            $this->redirect('home/login/index');
        }

        $this->userId=$user['id'];
    }

    /*
     * 公司相关信息分成三个模块显示
     */

    //基本信息
    public function baseInfo(){

        $where['user_id']=$this->userId;
        //验证用户的业务权限
        D('CompanyConfirm')->isBusinessAuth($this->userId);
        //基础信息
        $comInfo=$this->getInfo('Info',$where,false);
        $comInfo['desc']=htmlspecialchars_decode($comInfo['desc']);
        $comInfo['desc']=str_replace(array("<br />"),array("\r\n  "),$comInfo['desc']);
        $this->assign('comInfo',$comInfo);

        //获取认证状态
        $auth=D('User')->where('id='.$this->userId)->field('id,company_auth_status')->find();
        session('user_auth',$auth['company_auth_status']);

        //获取企业用户二级域名信息
        $domainInfo=D('CompanyConfirm')->domainStatus($this->userId);
        $this->assign('domain',$domainInfo);

        //通知信息
        $noticeInfo=$this->getInfo('Notice',$where,false);
        $this->assign('noticeInfo',$noticeInfo);

        //获取全部模板风格
        $str=T();
        $str=substr($str,1);
        $pos=strrpos($str,'/');
        $str=substr($str,0,$pos).'/';
        $imgurl=$str;
        $str=$_SERVER['DOCUMENT_ROOT'].$str;
        if(is_dir($str)){
            $themeArr=scandir($str);
            foreach($themeArr as $k=>$v){
                if($v=='.' || $v=='..' || is_file($str.$v) || $v=='images'){
                    unset($themeArr[$k]);
                }
            }
        }

        $styleInfo=array();
        foreach ($themeArr as $k=>$v) {
            $tmp=array();
            $tmp['name']=$v;
            $tmp['path']=$imgurl.$v.'/'.$v.'.png';
            $styleInfo[]=$tmp;
        }
        $this->assign('themeList',$styleInfo);

        $this->assign('act','comBase');
        $this->meta_title = '基本信息';
        $this->display('CompanyInfo/baseInfo');
    }

    //预览公司主页
    public function viewPage(){

        $get=I('get.');
        if(!empty($get['style'])){

            $style=clearXSS($get['style']);
            $where['user_id']=$this->userId;
            //基础信息
            $comInfo=$this->getInfo('Info',$where,false);
            $comInfo['desc']=htmlspecialchars_decode($comInfo['desc']);
            $desc=explode('<br />',$comInfo['desc']);
            $descTmp='';
            foreach($desc as $k=>$v){
                $v='<p>'.$v.'</p>';
                $descTmp.=$v;
            }
            $comInfo['desc']=$descTmp;
            if(mb_strwidth($comInfo['desc'])>695){
                $comInfo['desc']=mb_strimwidth($comInfo['desc'],0,695,'...','utf-8');
            }
            //公司认证基本信息
            $confirmInfo=D('CompanyConfirm')->where($where)->field(array('address','mobile'))->find();
            $comInfo['address']=$confirmInfo['address'];
            $comInfo['mobile']=$confirmInfo['mobile'];
            $this->assign('comInfo',$comInfo);

            //通知信息
            $noticeInfo=$this->getInfo('Notice',$where,false);
            $this->assign('noticeInfo',$noticeInfo);

            //公司动态
            $newsInfo=$this->getInfo('News',$where);
            foreach ($newsInfo as $k=>$v) {
                $newsInfo[$k]['add_time']=date('Y-m-d',$v['add_time']);
            }
            $this->assign('newsInfo',$newsInfo);

            //仓库信息
            $storeInfo=$this->getInfo('Store',$where);
            foreach($storeInfo as $k=>$v){
                $storeInfo[$k]['type']=$this->getStoreType(intval($v['type']));
                $storeInfo[$k]['address']=$this->getAddress($v['zone'],$v['address']);
            }
            $this->assign('storeInfo',$storeInfo);

            //物流信息
            $deliveryInfo=$this->getInfo('Delivery',$where);
            foreach($deliveryInfo as $k=>$v){
                $v['begin']=sliceCode($v['begin']);
                $deliveryInfo[$k]['begin']=$this->getZoneInfo($v['begin']);

                $v['end']=sliceCode($v['end']);
                $deliveryInfo[$k]['end']=$this->getZoneInfo($v['end']);
                $deliveryInfo[$k]['type']=$this->getCarType(intval($v['type']));
            }
            $this->assign('deliveryInfo',$deliveryInfo);

            //包装信息
            $packingInfo=$this->getInfo('Packing',$where);
            foreach($packingInfo as $k=>$v){
                $packingInfo[$k]['img']=explode(",",$v['img']);
                $packingInfo[$k]['img']=$packingInfo[$k]['img'][0];
            }
            $this->assign('packingInfo',$packingInfo);

            //加工信息
            $where['type']=1;
            $processInfo=$this->getInfo('Process',$where);
            $this->assign('processInfo',$processInfo);

            //检测信息
            $where['type']=2;
            $checkInfo=$this->getInfo('Process',$where);
            $this->assign('checkInfo',$checkInfo);
            unset($where);

            $this->assign('from','view');

            //获取全部模板风格
            $str=T();
            $str=substr($str,1);
            $pos=strrpos($str,'/');
            $str=substr($str,0,$pos).'/';
            $str=$_SERVER['DOCUMENT_ROOT'].$str;
            if(is_dir($str)){
                $themeArr=scandir($str);
                foreach($themeArr as $k=>$v){
                    if($v=='.' || $v=='..' || is_file($str.$v)){
                        unset($themeArr[$k]);
                    }
                }
            }
            if(!in_array($style,$themeArr)){
                $t='Company/simple/viewPage';
            }else{
                $t='Company/'.$style.'/viewPage';
            }
            $this->display($t);
        }else{
            $this->redirect('User/index');
        }
    }

    //公司动态
    public function newsInfo(){

        $count=C('newsLevel');
        $this->assign('count',$count['l1']);
        $where['user_id']=$this->userId;
        //公司动态
        $newsInfo=$this->getInfo('News',$where);
        foreach ($newsInfo as $k=>$v) {
            $newsInfo[$k]['add_time']=date('Y-m-d',$v['add_time']);
        }
        $this->assign('newsInfo',$newsInfo);
        $this->assign('num',count($newsInfo));
        $this->assign('act','comNews');
        $this->meta_title = '公司动态';
        $this->display('CompanyNews/newsInfo');
    }

    //业务管理
    public function businessInfo(){

        //获取业务权限数组
        $businessAuth=D('CompanyConfirm')->businessAuth($this->userId);
        $this->assign('auth_check',$businessAuth[1]);
        $this->assign('auth_delivery',$businessAuth[2]);
        $this->assign('auth_packing',$businessAuth[3]);
        $this->assign('auth_process',$businessAuth[4]);
        $this->assign('auth_store',$businessAuth[5]);

        $where['user_id']=$this->userId;
        //仓库信息
        $storeInfo=$this->getInfo('Store',$where);
        foreach($storeInfo as $k=>$v){
            $storeInfo[$k]['type']=$this->getStoreType(intval($v['type']));
        }
        $this->assign('storeInfo',$storeInfo);

        //物流信息
        $deliveryInfo=$this->getInfo('Delivery',$where);
        foreach($deliveryInfo as $k=>$v){
            $v['begin']=sliceCode($v['begin']);
            $deliveryInfo[$k]['begin']=$this->getZoneInfo($v['begin']);

            $v['end']=sliceCode($v['end']);
            $deliveryInfo[$k]['end']=$this->getZoneInfo($v['end']);
            $deliveryInfo[$k]['type']=$this->getCarType(intval($v['type']));
        }
        $this->assign('deliveryInfo',$deliveryInfo);

        //包装信息
        $packingInfo=$this->getInfo('Packing',$where);
        $this->assign('packingInfo',$packingInfo);

        //加工信息
        $where['type']=1;
        $processInfo=$this->getInfo('Process',$where);
        $this->assign('processInfo',$processInfo);

        //检测信息
        $where['type']=2;
        $checkInfo=$this->getInfo('Process',$where);
        $this->assign('checkInfo',$checkInfo);
        unset($where);
        $this->assign('act','comBus');
        $this->meta_title = '公司业务';
        $this->display('CompanyBusiness/businessInfo');
    }

    //删除业务信息
    public function delInfo(){
        if(IS_AJAX){

            $data=I('post.');
            $arr=array('store','delivery','packing','process','check');
            if(in_array(trim($data['type']),$arr) && intval($data['id'])>0){
                $type=clearXSS($data['type']);
                $id=intval($data['id']);
                switch($type){
                    case 'store';
                        $business=A('CompanyStore');
                        break;
                    case 'packing';
                        $business=A('CompanyPacking');
                        break;
                    case 'delivery';
                        $business=A('CompanyDelivery');
                        break;
                    case 'process';
                        $business=A('CompanyProcess');
                        break;
                    case 'check';
                        $business=A('CompanyCheck');
                        break;
                }

                $st=$business->delInfo($id);
                if($st){
                    $rs['status']=1;
                    $rs['msg']='删除成功';
                }else{
                    $rs['status']=0;
                    $rs['msg']='删除失败';
                }
            }else{
                $rs['status']=0;
                $rs['msg']='删除失败';
            }
        }else{
            $rs['status']=0;
            $rs['msg']='删除失败';
        }

        $this->ajaxReturn($rs);
    }

    //获取相关模型信息
    private function getInfo($name='',$where='1=1',$more=true){

        if(empty($name)) return array();
        $modelStr='Company'.$name;
        $companyModel=M($modelStr);
        if($more){
            $data=$companyModel->where($where)->order('id desc')->select();
        }else{
            $data=$companyModel->where($where)->find();
        }
        return $data;
    }

    //获取行政区划信息
    private function getZoneInfo($code=array()){
        if(empty($code)) return '全国';
        $str='';
        $region=D('Region');
        $fileds=array('REGION_ID','REGION_CODE','REGION_NAME');
        foreach($code as $k=>$v){
            $info=array();
            if($v!=0){
                $info=$region->where('REGION_CODE='.$v)->field($fileds)->find();
                $str.=$info['region_name'];
            }
        }
        return $str;
    }

    //获取仓库类型
    private function getStoreType($code){

        $type='普通';
        switch($code){
            case 0:
                $type='普通';
                break;
            case 1:
                $type='冷藏';
                break;
            case 2:
                $type='恒温/保温';
                break;
            case 3:
                $type='特种';
                break;
            case 4:
                $type='气调';
                break;
            default:
                $type='普通';
                break;
        }

        return $type;
    }

    /*
	 * 返回省份 select 列表
	 * 如果 提供ID，则此项默认会被选中
	 * param int 省份ID
	 * @return string option html
	 */
    public function getProHtml($id) {

        //读取所有省份
        $list = M('Region')->field('REGION_CODE as code,REGION_NAME as area')
            ->where(array('PARENT_ID' => 1))
            ->select();
        $html = '<option value=\'0\'>请选择省</opt>';
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
     * @param int id 所属省份ID
     * @param int city_id 所属城市 ID
     * @return string option html
     */
    public function getCityHtml($id,$city_id) {

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
     * @param int id 所属市区ID
     * @param int county_id 所属县区 ID
     * @return string option html
     * 此方法同 getCityHtmlSelect 目的 为了方便识别
     */
    public function getAreaHtml($id,$area_id) {

        $list = M()->query('select REGION_CODE as code,REGION_NAME as area from ydw_region where REGION_CODE like \''.$id.'%\' and length(REGION_CODE) = 6');
        $html = '<option value=\'0\'>请选择县</option>';
        $isSelect = '';

        foreach ($list as $v) {
            $isSelect = ($v['code'] == $area_id) ? " selected='selected'" : '';
            $html .= "<option value='{$v['code']}'{$isSelect}>{$v['area']}</option>";
        }
        return $html;
    }

    /*
     * 返回地址详细信息
     * @param $code 行政区域代码
     * @param $address 街道地址
     * @return string $address
     */
    public function getAddress($code=0,$address='') {

        if($code==0 || !ctype_digit($code))
        {
            return $address;
        }else{
            $region=D('Region');
            $code=sliceCode($code);
            $fields=array('REGION_CODE','REGION_NAME');
            $provice=$region->where('REGION_CODE='.$code[0])->field($fields)->find();
            $city=$region->where('REGION_CODE='.$code[1])->field($fields)->find();
            $dist=$region->where('REGION_CODE='.$code[2])->field($fields)->find();

            $s=$provice['region_name'].$city['region_name'].$dist['region_name'].$address;
            return $s;
        }
    }

    //获取车辆类型
    public function getCarType($type){

        switch($type){
            case 0:
                $type='厢式/板车';
                break;
            case 1:
                $type='集装箱';
                break;
            case 2:
                $type='冷藏车';
                break;
            case 3:
                $type='危险品车辆';
                break;
            case 4:
                $type='特种车辆';
                break;
            default:
                $type='厢式/板车';
                break;
        }

        return $type;
    }

    //删除被取消/丢弃的图片---暂时不做任何处理
    public function delImg(){

        if(IS_AJAX){
            $post=I('post.');
            if(empty($post) || empty($post['path'])){
                $rs['status']=0;
                $rs['msg']='删除失败';
            }else{
                //unlink(trim($post['path']));
                $rs['status']=1;
                $rs['msg']='删除成功';
            }
        }else{
            $rs['status']=0;
            $rs['msg']='删除失败';
        }
        $this->ajaxReturn($rs);
    }

}