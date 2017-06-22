<?php
namespace Api\Controller;
use Think\Controller;
class AppDirectController extends BaseController {
    /* 发布产地直销  1.4版本*/ 
    public function publish() {
        if($this->client_type == 1){
            $this->secureVerify();
            $post = I('post.');
            $post = array_map('clearXSS',$post);

            //参数校验
            if(!intval($post['cid']) || !$post['cn']){//分类id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品分类'));
            }

            if(!intval($post['gid']) || !$post['gn']){//药材id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品'));
            }

            if(!intval($post['aid']) || !$post['an']){//规格id
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药品规格'));
            }

            if(intval($post['num']) > 0){//供应数量 数量小于等于0时为面议
                if (intval($post['num']) < 0 || intval($post['num']) >= 10000000) {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请填写正确的供应数量'));
                }
            }else{
                $post['num'] = '-1';
            }

            if ((float)$post['price'] < 0 || (float)$post['price'] > 1000000 || (float)$post['mprice'] < 0 || (float)$post['mprice'] > 1000000) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的价格'));
            }elseif((float)$post['price'] == 0 && (float)$post['mprice'] == 0){//价格和最大价格都为0时
                $post['price']      = 0;
                $post['mprice']     = 0;
            }elseif((float)$post['price'] == 0 && (float)$post['mprice'] > 0){//价格和最大价格 只有一个有值时 将值保存进price中
                $post['price']      = (float)$post['mprice'];
                $post['mprice']     = 0;
            }elseif((float)$post['price'] > 0 && (float)$post['mprice'] == 0){//价格和最大价格 只有一个有值时 将值保存进price中
                $post['price']      = (float)$post['price'];
                $post['mprice']     = 0;
            }elseif((float)$post['price'] > 0 && (float)$post['mprice'] > 0){//价格和最大价格都有值时,进入下面判断
                if((float)$post['price'] < (float)$post['mprice']){//价格小于最大价格时,正常保存数据
                    $post['price']      = (float)$post['price'];
                    $post['mprice']     = (float)$post['mprice'];
                }elseif((float)$post['price'] > (float)$post['mprice']){//价格大于最大价格时,当错误数据处理
                    $post['price']      = 0;
                    $post['mprice']     = 0;

                }elseif((float)$post['price'] == (float)$post['mprice']){//价格等于最大价格时,只保存一个值进price
                    $post['price']      = (float)$post['price'];
                    $post['mprice']     = 0;
                }
            }else{
                $post['price']      = 0;
                $post['mprice']     = 0;
            }

            if(!$post['o_code'] || !$post['o_area']){
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择产地'));
            }
            
            if(!$post['s_code'] || !$post['s_area']){//库存地
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择库存地'));
            }

            if(!in_array((int)$post['pjid'], [0, 1, 2, 3])) {//药材票据
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择票据'));
            }else{
                $tickets = [
                    '0' => '不提供票据',
                    '1' => '发票',
                    '2' => '收购手续',
                    '3' => '发票或收购手续'
                ];
                $post['ticket'] = $tickets[(int)$post['pjid']];
            }

            if(!in_array((int)$post['zlid'], [0, 1, 2, 3, 4, 5])){//药材质量
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药材标准'));
            }else{
                $standards = [
                    '0' => '待确定',
                    '1' => '达到出口标准和药典标准',
                    '2' => '达到出口标准',
                    '3' => '达到省标',
                    '4' => '达到2010版药典标准',
                    '5' => '达到2015版药典标准'
                ];
                $post['standard'] = $standards[(int)$post['zlid']];
            }

            if(!$post['contacts']){//联系人
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入联系人'));
            }

            if(mb_strlen($post['contacts'], 'utf-8') > 20){//联系人
                $this->ajaxReturn(array('code' => 0, 'msg' => '联系人字数不能超过20个'));
            }

            if (!$data['mobile'] && !$data['tel']) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入手机号码或固定电话'));
            }

            if ($data['mobile'] && !check_mobile($data['mobile'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的手机号'));
            }

            if ($data['tel'] && !preg_match("/^([0-9]{3,4}-)?[0-9]{7,8}$/", $data['tel'])) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的固定电话'));
            }
            //qq号不用必填
            if (!ctype_digit($post['qq']) || mb_strlen($post['qq'],'utf-8') > 15) {
                $post['qq'] = '';
            }

            $res = D('CompanyDirect')->savePublish($post);
            if ((int)$res > 0) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '发布成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '发布失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数无效'));
        }
    }
    //产地直销列表接口
    public function Direct_lists(){
        //返回请求药材的该药材的所有供应信息
        if($this->client_type == 1){
            //接收参数
            $num            =  10;
            $page           =  I('post.page',1,'intval');
            $key            =  clearXSS(I('post.key'));//1 代表筛选条件 2搜索框搜索 
            $goods_name     =  clearXSS(I('post.gn','','trim'));//品种
            $origin_code    =  clearXSS(I('post.o_code','','trim'));//产地code码
            $stock_code     =  clearXSS(I('post.s_code','','trim'));//库存地code码
            //1待确定 2 达到出口标准和药典标准 3达到出口标准 4达到省标 5达到2010版药典标准 6达到2015版药典标准
            $standard_id    =  I('post.sid',0,'intval');//质量 
            //1：不提供票据，主要用户查询，2：发票，3：收购手续，4：发票或收购手续
            $ticket_id      =  I('post.tid',0,'intval');//票据
            $price_type     =  clearXSS(I('post.p_type',0,'intval'));//价格  1 代表有价格 2 代表面议
            
            //组装条件
            //where条件
            $data['status']      =  1;

            if($key){
                $data['goods_name']  = array('like',$key.'%');
            }
            if($goods_name){
                $data['goods_name']  = $goods_name;
            }

            $origin_code    && $data['origin_code'] = array('like',$origin_code.'%');
            $stock_code     && $data['stock_code']  = array('like',$stock_code.'%');
            if($standard_id){
                if(!in_array((int)$standard_id, [ 1, 2, 3, 4, 5,6])){//药材质量
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请选择药材标准'));
                }
                $data['standard_id'] = $standard_id -1; 
            }
            if($ticket_id){
                if(!in_array((int)$ticket_id, [1, 2, 3,4])) {//药材票据
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请选择票据'));
                }
                $data['ticket_id']   = $ticket_id-1;
            } 
            if($price_type == '1'){
                $data['price'] = array('gt','0');
            }else if($price_type =='2'){
                $data['price'] = 0;
            }

            //计算条数
            $total =($page-1) * $num;
            $list  = M('companyDirect')->field('id,uid,goods_name as gn,goods_attr_name as an,origin_area as o_area,standard,stock_area as s_area,price,maxprice,images as img')
                    ->where($data)
                    ->order('weight desc,id desc')
                    ->limit($total,$num)
                    ->select();

            foreach ($list as $k => $v) {
                
                if($v['price']  == 0){
                    $list[$k]['price'] = '面议';
                }

                if($v['price'] != '0' && $v['maxprice'] != '0'){
                    $list[$k]['price']  = $v['price'].'~'.$v['maxprice'].'元/公斤';
                }else if($v['price'] != '0' && $v['maxprice'] == '0'){
                    $list[$k]['price']  = $v['price'].'元/公斤';
                }

                if($v['img']){
                    $img  = explode(',',$v['img']);
                    $list[$k]['img'] = $img[0];
                }else{
                    $list[$k]['img'] = '/Uploads/Picture/Avatar/20170622/594b7dee39413.jpg'; 
                }
                //是否是企业认证
                $where['uid']    = $v['uid'];
                $where['status'] = '1';
                $companyAuthen = M('companyAuthen')->where($where)->find();
                if($companyAuthen){
                    $list[$k]['company_status'] = '1';
                }else{
                    $list[$k]['company_status'] = '0';
                }
            }
            if($list){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $list));
             }else{
                if(intval($page) > 1){
                    $this->ajaxReturn(array('code' => 0, 'msg' => '数据加载完毕'));
                }else{
                    $this->ajaxReturn(array('code' => 0, 'msg' => '暂无数据'));
                }
          
             }
           
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
    //产地详情  1.4版本
    public function Direct_detalis(){
        if($this->client_type == 1){

            //接收参数
            $Direct_id = I('post.Direct_id',0,'intval');//供应的id 

            //校验参数
            $Direct_id || $this->ajaxReturn(array('code' => 0, 'msg' => '参数未传'));

            $info = M('companyDirect')->field('id,uid,goods_name as gn,goods_attr_name as an,images as img,create_time as time,price,maxprice,num,unit,contacts,qq,telephone as tel,mobile,standard,ticket,stock_area as s_area,origin_area as o_area')
                    ->where(['id' =>$Direct_id])
                    ->find();
            //图片
            if($info['img']){
                $img = explode(",",$info['img']);
                $info['img'] = $img; 
            }else{
                 $info['img'] = array(); 
            }
            

            //将qq转化一下
            $info['qq']  || $info['qq'] = '无';
            
            //将价格转化一下
            if($info['price'] != '0' && $info['maxprice'] != '0'){
                $info['price']  = $info['price'].'~'.$info['maxprice'].'元/公斤';
            }else{
                $info['price']  = $info['price'].'元/公斤';
            }

            if($info['price_type'] == '2'){
                $info['price'] = '面议';
            }

            
            //将时间转化一下
            $info['time']  = date('Y-m-d',$info['time']);

            //是否是企业认证
            $where['uid']    = $info['uid'];
            $where['status'] = '1';
            $companyAuthen = M('companyAuthen')->where($where)->find();
            if($companyAuthen){
                $info['company_status'] = '1';
            }else{
                $info['company_status'] = '0';
            }

            if($info){
                $this->ajaxReturn(array('code' => 1, 'msg' => '获取成功','data' => $info));
            }else{
                $this->ajaxReturn(array('code' => 0, 'msg' => '获取内容失败'));
            }
        }else{
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }
    /************1.4版本开始结束********/
}
