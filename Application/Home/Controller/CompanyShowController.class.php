<?php
/**
 * 前台公司信息展示控制器
 * Author: jingwei
 * Date: 2016/10/15
 */
namespace Home\Controller;


class CompanyShowController extends HomeController{


    //********企业信息展示功能开始********

    //本控制器所展示信息仅仅是为其它用户浏览公司相关信息所用，不包含任何其它的业务逻辑操作

    //浏览公司主页的二级域名路由器
    public function viewPage(){

        $get=I('get.');
        $tmpArr=explode('.',$get['d']);
        $get['d']=$tmpArr[0];
        $tmpWhere['domain']='2|'.$get['d'];
        $id=D('CompanyConfirm')->where($tmpWhere)->find();
        $requestUrl=$_SERVER['REQUEST_URI'];
        $host='http://'.$_SERVER['HTTP_HOST'];

        if(!empty($get) && $id['user_id']>0){

            $comInfo=M('CompanyInfo')->where('user_id='.$id['user_id'])->find();
            if(empty($comInfo)){
                header('Location: '.$host);exit;
            }
            session('mystyle',$comInfo['style']);

            $this->assign('domain',$host);
            if($requestUrl=='/'){
                $this->_companyindexpage($comInfo);
            }else{
                $requestUrl=explode('/',$requestUrl);
                $param=intval(str_replace('.html','',$requestUrl[4]));
                if($param<=0){
                    header('Location: '.$host);exit;
                }
                switch($requestUrl[2]){
                    case 'descInfo':
                        $this->_descinfo($param);
                        break;
                    case 'newsInfo':
                        $this->_newsinfo($param);
                        break;
                    case 'storeInfo':
                        $this->_storeinfo($param);
                        break;
                    case 'packingInfo':
                        $this->_packinginfo($param);
                        break;
                    default:
                        $this->_companyindexpage($id['user_id']);
                        break;
                }
            }
        }else{
            header('Location: '.$host);
            exit;
        }
    }

    //公司简介详情
    private function _descinfo($id){

        $companyInfoModel=D('CompanyInfo');
        $comInfo=$companyInfoModel->where('id='.$id)->find();
        $comInfo['desc']=htmlspecialchars_decode($comInfo['desc']);
        $desc=explode('<br />',$comInfo['desc']);
        $descTmp='';
        foreach($desc as $k=>$v){
            $v='<p>'.$v.'</p>';
            $descTmp.=$v;
        }
        $comInfo['desc']=$descTmp;
        $this->assign('comInfo',$comInfo);
        $data=$this->headInfo($comInfo['user_id']);
        $this->assign('noticeInfo',$data['noticeInfo']);
        $this->display('Company/'.$comInfo['style'].'/descInfo');

    }

    //公司动态详情
    private function _newsinfo($id){

        $companyNewsModel=D('CompanyNews');
        $newsInfo=$companyNewsModel->where('id='.$id)->find();
        $newsInfo['add_time']=date('Y-m-d H:m:i',$newsInfo['add_time']);
        $this->assign('newsInfo',$newsInfo);
        $style=session('mystyle');

        $data=$this->headInfo($newsInfo['user_id']);
        $this->assign('comInfo',$data['comInfo']);
        $this->assign('noticeInfo',$data['noticeInfo']);
        $this->display('Company/'.$style.'/newsInfo');
    }

    //仓库信息详情
    private function _storeinfo($id){

        $companyStoreModel=D('CompanyStore');
        $storeInfo=$companyStoreModel->where('id='.$id)->find();
        $storeInfo['type']=$this->getStoreType(intval($storeInfo['type']));
        $storeInfo['address']=$this->getAddress($storeInfo['zone'],$storeInfo['address']);
        $storeInfo['img']=explode(',',$storeInfo['img']);
        $this->assign('storeInfo',$storeInfo);
        $style=session('mystyle');

        $data=$this->headInfo($storeInfo['user_id']);
        $this->assign('comInfo',$data['comInfo']);
        $this->assign('noticeInfo',$data['noticeInfo']);
        $this->display('Company/'.$style.'/storeInfo');
    }

    //包装信息详情
    private function _packinginfo($id){

        $companyPackingModel=D('CompanyPacking');
        $packingInfo=$companyPackingModel->where('id='.$id)->find();
        $packingInfo['img']=explode(",",$packingInfo['img']);
        $this->assign('packingInfo',$packingInfo);
        $style=session('mystyle');

        $data=$this->headInfo($packingInfo['user_id']);
        $this->assign('comInfo',$data['comInfo']);
        $this->assign('noticeInfo',$data['noticeInfo']);
        $this->display('Company/'.$style.'/packingInfo');
    }

    //企业主页信息展示
    private function _companyindexpage($comInfo){

        //基础信息
        $comInfo['desc']=htmlspecialchars_decode($comInfo['desc']);
        $desc=explode('<br />',$comInfo['desc']);
        $descTmp='';
        foreach($desc as $k=>$v){
            $v='<p>'.$v.'</p>';
            $descTmp.=$v;
        }
        $comInfo['desc']=$descTmp;
        if(mb_strwidth($comInfo['desc'])>690){
            $comInfo['desc']=mb_strimwidth($comInfo['desc'],0,690,'...','utf-8');
        }
        //$comInfo['desc']=$this->_strSut($comInfo['desc'],850);

        //公司认证基本信息
        $confirmInfo=D('CompanyConfirm')->where('user_id='.$comInfo['user_id'])->field(array('address','mobile'))->find();
        $comInfo['address']=$confirmInfo['address'];
        $comInfo['mobile']=$confirmInfo['mobile'];
        $this->assign('comInfo',$comInfo);

        $where['user_id']=$comInfo['user_id'];
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

        $this->assign('from','show');
        if(!empty($comInfo['style'])){
            $t='Company/'.$comInfo['style'].'/viewPage';
        }else{
            $t='Company/simple/viewPage';
        }
        $this->display($t);
    }
    //********企业信息展示功能结束********

    //********首页企业相关信息展示功能开始********

    //物流信息列表
    public function deliveryList(){

        if(IS_GET){
            $get=I('get.');
            $p=empty($get['p'])?0:intval($get['p']);//当前页
            if(isset($get['t']) && ctype_digit($get['t'])){
                $type=intval($get['t']);
            }

            $where='1=1';
            if(isset($type)){
                $where='type='.$type;
            }
            $delivery=D('CompanyDelivery');
            $prePage=8;
            $total=$delivery->where($where)->count();//总页数
            $totalPage = ceil($total / $prePage);
            if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
            $start=($p-1)*$prePage;
            $limit=$start.','.$prePage;
            //数据
            $list=$delivery->where($where)->limit($limit)->order('id desc')->select();
            if($list){
                $companyConfirm=D('CompanyConfirm');
                foreach($list as $k=>$v){
                    $confirmInfo=array();
                    $confirmInfo=$companyConfirm->where('user_id='.$v['user_id'])->field(array('name','mobile'))->find();
                    $list[$k]['name']=$confirmInfo['name'];
                    $list[$k]['mobile']=$confirmInfo['mobile'];
                    $v['begin']=sliceCode($v['begin']);
                    $list[$k]['begin']=$this->getZoneInfo($v['begin']);
                    $v['end']=sliceCode($v['end']);
                    $list[$k]['end']=$this->getZoneInfo($v['end']);
                    $list[$k]['type']=$this->getCarType(intval($v['type']));
                    $list[$k]['add_time']=date('Y-m-d',$v['add_time']);
                }
            }
            $this->assign('info',$list);

            //分页
            if(isset($type)){
                $pageUrl=U('CompanyShow/deliveryList',array('t'=>$type));//分页链接
                $this->assign('t',$type);
            }else{
                $pageUrl=U('CompanyShow/deliveryList');//分页链接
                $this->assign('t',9999);
            }
            if($total>$prePage){
                $pager=new \Org\Com\Page;
                $pageHtml=$pager->show($total,$prePage,$p,$pageUrl.'?');
                $this->assign('pageHtml',$pageHtml);
            }

            $this->assign('act','del');
            $this->meta_title = '物流信息';
            $this->display('CompanyShow/deliveryList');
        }else{
            $this->redirect('Index/index');
        }
    }

    //首页获取指定数目的物流信息
    public function getDeliveryIndex($id=0){
        if($id>0){
            $delivery=D('CompanyDelivery');
            $fields=array('id','user_id','begin','end','type','desc');
            $list=$delivery->field($fields)->limit($id)->order('id desc')->select();
            foreach($list as $k=>$v){
                $v['begin']=sliceCode($v['begin']);
                $list[$k]['begin_s']=$this->getProviceInfo($v['begin'][0]);
                $list[$k]['begin']=$this->getZoneInfo($v['begin']);
                $v['end']=sliceCode($v['end']);
                $list[$k]['end_s']=$this->getProviceInfo($v['end'][0]);
                $list[$k]['end']=$this->getZoneInfo($v['end']);
                $list[$k]['type']=$this->getCarType(intval($v['type']));
            }
            return $list;
        }else{
            return array();
        }
    }

    //仓库信息列表
    public function storeList(){

        if(IS_GET){
            $get=I('get.');
            $p=empty($get['p'])?0:intval($get['p']);//当前页
            if(isset($get['t']) && ctype_digit($get['t'])){
                $type=intval($get['t']);
            }

            $where='1=1';
            if(isset($type)){
                $where='type='.$type;
            }
            $fields=array('id','user_id','type','size','height','img','zone','address','contacts','mobile','desc');
            $store=D('CompanyStore');
            $prePage=8;
            $total=$store->where($where)->count();//总页数
            $totalPage = ceil($total / $prePage);
            if($p == 0){ $p = 1; }else if($p > $totalPage){ $p = $totalPage; }
            $start=($p-1)*$prePage;
            $limit=$start.','.$prePage;
            //数据
            $list=$store->where($where)->field($fields)->limit($limit)->order('id desc')->select();
            if($list){
                foreach($list as $k=>$v){
                    $list[$k]['type']=$this->getStoreType(intval($v['type']));
                    $list[$k]['address']=$this->getAddress($v['zone'],$v['address']);
                    $tmp=array();
                    if($v['img'])
                    {
                        $tmp=explode(',',$v['img']);
                    }else{
                        $tmp[0]='/Public/Home/images/noimg.png';
                    }
                    $list[$k]['img']=$tmp[0];
                }
            }
            $this->assign('info',$list);

            //分页
            if(isset($type)){
                $pageUrl=U('CompanyShow/storeList',array('t'=>$type));//分页链接
                $this->assign('t',$type);
            }else{
                $pageUrl=U('CompanyShow/storeList');//分页链接
                $this->assign('t',9999);
            }
            if($total>$prePage){
                $pager=new \Org\Com\Page;
                $pageHtml=$pager->show($total,$prePage,$p,$pageUrl.'?');
                $this->assign('pageHtml',$pageHtml);
            }

            $this->assign('act','store');
            $this->meta_title = '仓库信息';
            $this->display('CompanyShow/storeList');
        }else{
            $this->redirect('Index/index');
        }
    }

    //仓库信息详情
    public function storeInfoIndex(){
        $get=I('get.');
        if(intval($get['i'])>0){
            $id=intval($get['i']);
            $store=D('CompanyStore');
            $info=$store->where('id='.$id)->find();
            if($info){
                $info['type']=$this->getStoreType(intval($info['type']));
                $info['address']=$this->getAddress($info['zone'],$info['address']);
                $info['img']=explode(',',$info['img']);
                $this->assign('info',$info);
                $this->assign('act','store');
                $this->meta_title = '仓库信息';
                $this->display('CompanyShow/storeInfo');
            }else{
                $this->redirect('CompanyShow/storeList');
            }
        }else{
            $this->redirect('CompanyShow/storeList');
        }
    }

    //首页获取指定数目的仓库信息
    public function getStoreIndex($id=0){
        if($id>0){
            $store=D('CompanyStore');
            $fields=array('id','user_id','type','size','height','contacts','mobile');
            $list=$store->field($fields)->limit($id)->order('id desc')->select();
            foreach($list as $k=>$v){
                $list[$k]['type']=$this->getStoreType(intval($v['type']));
            }
            return $list;
        }else{
            return array();
        }
    }

    //加工和检测列表
     public function mergeList(){
         if(IS_GET){
             $get=I('get.');
             $p=empty($get['p'])?0:intval($get['p']);//当前页
             $type=($get['t']=='1' || $get['t']=='2')?intval($get['t']): null;

             $data=D('CompanyProcess')->getList($p,$type);
             $this->assign('info',$data['list']);
             $this->assign('t',$data['t']);

             if($data['pageHtml']){
                 $this->assign('pageHtml',$data['pageHtml']);
             }

             $act='merge';
             if($type==1){
                 $act='process';
             }elseif($type==2){
                 $act='check';
             }
             $this->assign('act',$act);
             $this->assign('all','all');
             $this->meta_title = '加工检测';
             $this->display('CompanyShow/mergeList');
         }else{
             $this->redirect('Index/index');
         }
    }

    //********首页企业相关信息展示功能结束********

    //页面公共头部信息
    private function headInfo($userId)
    {
        $comInfoModel=D('CompanyInfo');
        $comInfo=$comInfoModel->where('user_id='.$userId)->find();
        $where['user_id']=$comInfo['user_id'];
        $data=array();
        //通知信息
        $noticeInfo=$this->getInfo('Notice',$where,false);
        $data['comInfo']=$comInfo;
        $data['noticeInfo']=$noticeInfo;
        unset($comInfo,$noticeInfo);
        return $data;
    }

    //获取相关模型信息
    private function getInfo($name='',$where='1=1',$more=true){

        if(empty($name)) {
            return array();
        }

        $modelStr='Company'.$name;
        $companyModel=D($modelStr);
        if($more){
            $data=$companyModel->where($where)->order('id desc')->select();
        }else{
            $data=$companyModel->where($where)->find();
        }
        return $data;
    }

    //获取行政区划信息
    private function getZoneInfo($code=array()){
        if(empty($code)){
            return '全国';
        }

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

    //获取省份信息
    private function getProviceInfo($code=''){
        if(empty($code)){
            return '全国';
        }
        $region=D('Region');
        $fields=array('REGION_ID','REGION_CODE','REGION_NAME');
        $info=$region->where('REGION_CODE='.$code)->field($fields)->find();
        return $info['region_name'];
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

    /**
     * 截取字符串防止乱码
     * @param $str 需要截断的字符串
     * @param $length 允许字符串显示的最大长度
     * @return string
     */
    private function _strSut($str,$length){
        if(strlen($str)>$length){
            for($i=0;$i<$length;$i++){
                if(ord($str[$i])>128){
                    $i++;
                }
            }
            $str=substr($str,0,$i-1).'...';
        }
        return $str;
    }

    //物流详情页
    public function delivery_details(){
        $id = I('get.id',0,'intval');
        $info = M('company_delivery')->where(['id' => $id])->find();
        $info['type'] = $this->getTypeName($info['type']);
        $info['begin']= region($info['begin']);
        $info['end']= region($info['end']);
        if($info['begin'] === '0'){
            $info['begin'] = '全国';
        }else{
            $info['begin'] = M('region')->where(['REGION_CODE' => $info['begin']])->getField('REGION_NAME');
        }
        if($info['end'] ==='0'){
            $info['end'] = '全国';
        }else{
            $info['end'] = M('region')->where(['REGION_CODE' => $info['end']])->getField('REGION_NAME');
        }
        $this->assign('info',$info);
        $this->display('');
    } 

    //获取车辆类型
    //获取表名
    private function getTypeName($type_code){

        switch($type_code){
            case 0:
                $TypeName='厢式/板车';
                break;
            case 1:
                $TypeName='集装箱';
                break;
            case 2:
                $TypeName='冷藏车';
                break;
            case 3:
                $TypeName='危险品车辆';
                break;
            case 4:
                $TypeName='特种车';
                break;
        }
        return $TypeName;    
    }
}