<?php
namespace Api\Controller;
use Think\Controller;
class AppGeneralDemandController extends BaseController {
    /*
    *发布求购   1.4版本
    **/
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


            if (intval($post['num']) <= 0 || intval($post['num']) >= 10000000) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '请填写正确的供应数量'));
            }
   
            if((float)$post['price']){
                if ((float)$post['price'] < 0 || (float)$post['price'] > 1000000 ) {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的价格'));
                }
            }else{
                $post['price']  = 0;
            }
            

            if(!$post['o_type']){//产地类型
                $this->ajaxReturn(array('code' => 0, 'msg' => '请选择产地类型'));
            }else{
                if(intval($post['origin_type']) == 3){//产地类型为3时 代表选择了省市县 则必须有产地code值
                    if(!$post['o_code'] || !$post['o_area']){
                        $this->ajaxReturn(array('code' => 0, 'msg' => '请选择产地'));
                    }
                }
            }

            $Days = C('VALID_DAYS2');//求购有效期
            if(!$Days[(int)$post['days']]){
                $this->ajaxReturn(array('code' => 0, 'msg' => '选择正确的有效期限'));
            }
            $post['valid_days']  = isset($Days[(int)$post['days']]) ? $Days[(int)$post['days']] : 0;
            $post['remain_days'] = $post['valid_days'] > 0 ? $post['valid_days'] : 0;

            if($post['s_code'] == '0' && !$post['s_area']){//库存地
                $post['s_code'] = '0';
                $post['s_area'] = '不限';
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

            if(!check_mobile($post['mobile'])){//电话
                $this->ajaxReturn(array('code' => 0, 'msg' => '请输入正确的手机号'));
            }

            if(mb_strlen($post['contacts'], 'utf-8') > 20){//联系人
                $this->ajaxReturn(array('code' => 0, 'msg' => '联系人字数不能超过20个'));
            }

            //qq号不用必填
            if (!ctype_digit($post['qq']) || mb_strlen($post['qq'],'utf-8') > 15) {
                $post['qq'] = '';
            }

            $res = D('general_demand')->savePublish($post);
            if ((int)$res > 0) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '发布成功'));
            } else {
                $this->ajaxReturn(array('code' => 0, 'msg' => '发布失败'));
            }
        } else {
            $this->ajaxReturn(array('code' => 0, 'msg' => '参数错误'));
        }
    }

    //搜索普通求购列表接口
    public function General_lists(){
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
            //1代表按天数降序 2 代表按天数升序
            $d_sort         =  I('post.d_sort',1,'intval');
            
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
            //将过期时间过滤掉
            $data['_string'] = '!(remain_days=0 && valid_days!=0)';
               
            if($d_sort == 2){
                $order = 'remain_days desc,weight desc,id desc';
            }else if($d_sort == 3){
                $order = 'remain_days ASC,weight desc,id desc';
            }else{
                $order = 'weight desc,id desc';
            }

            //计算条数
            $total =($page-1) * $num;
            $list  = M('generalDemand')->field('id,goods_name as gn,goods_attr_name as an,contacts,remain_days as days,num,unit,mobile,origin_type as o_type,origin_area as o_area,price')
                    ->where($data)
                    ->order($order)
                    ->limit($total,$num)
                    ->select();

            foreach ($list as $k => $v) {
                
                if($v['price']  == 0){
                    $list[$k]['price'] = '面议';
                }else{
                    $list[$k]['price'] = $v['price'].'元/千克';
                }
                if($v['o_type'] == 1){
                    $list[$k]['o_area'] ='较广';
                }else if($v['o_type'] == 2){
                    $list[$k]['o_area'] ='进口';
                }
                if($v['days'] == '0'){
                    $list[$k]['days'] = '常年';
                }else{
                    $list[$k]['days'] = $v['days'].'天';
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
}
