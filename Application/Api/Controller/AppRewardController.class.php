<?php
namespace Api\Controller;
use Think\Controller;
class AppRewardController extends BaseController {
    //打赏文章
    public function reward(){
        if($this->client_type == 1){
            $this->secureVerify();

            //接收参数
            $data = array();
            $data['reward_id']      = I('post.uid',0,'intval');//打赏人id
            $data['reward_name']    = M('user')->where(['id' => $data['reward_id']])->getField('realname');//打赏人姓名
            $data['pay_pass']       = I('post.pay_pass');//支付密码
            $data['money']          = I('post.money');//打赏金额
            $data['getreward_id']   = I('post.getreward_id',0,'intval');//被打赏人id
            $data['getreward_name'] = M('user')->where(['id' => $data['getreward_id']])->getField('realname');//被赏人姓名
            $data['market_id']      = I('post.market_id',0,'intval');//被打赏文章id
            $data['create_time']    = time();

            //校验参数
            $data['reward_id']            || $this->ajaxDie(30); //打赏人id未传
            $data['getreward_id']         || $this->ajaxDie(113);//被打赏人id未传
            $data['market_id']            || $this->ajaxDie(114);//被打赏文章id未传
            //查询打赏人支付密码
            $pay_pass  = M('user_balance')->where(['uid' => $data['reward_id']])->getField('pay_pass');
            $pay       = password_md5($data['pay_pass']);
            if($pay != $pay_pass){
                $this->ajaxDie(123);
            }
            //获取最小打赏金额
            $min_r = C('MIN_REWARD_AMOUNT');
            if(!is_numeric($data['money']) || $data['money'] <  $min_r){//判断金额
                $this->ajaxDie(116,$min_r);
            }
            //安全性校验 判断该文章是不是被打赏人发的
            $belong_id = M('goods_market')->where(['id' => $data['market_id']])->getField('belong_id');//对应文章id
            $g_uid     = M('user_goods_market')->where(['id' => $belong_id])->getField('uid');//对应文章所属用户uid
            if(!$belong_id || $g_uid != $data['getreward_id']){//该文章不是被打赏人发的
                $this->ajaxDie(117);
            }
          
            //查询打赏人余额信息
            $balanceModel = M('user_balance');
            $balance      = $balanceModel->where(['uid' => $data['reward_id']])->getField('balance');//账户余额
           
            if(!$balance || $balance < $data['money']){//该用户无余额或打赏金额大于余额
                $this->ajaxDie(115);
            }else{
                $rewardModel = M('user_reward');
                //开启事物处理
                $balanceModel ->startTrans();//开启事物
                $rewardModel ->startTrans();
                //给打赏人的余额减去打赏金额
                $reward    = $balanceModel->where(['uid' => $data['reward_id']])->setDec('balance',$data['money']); 
                //先查询被打赏人是否有账户信息  没有账户信息给被打赏人注册一个
                $balanceDetalis = $balanceModel->where(['uid' =>  $data['getreward_id']])->find();//查询被打赏人账户信息
                if(!$balanceDetalis){
                    $map['uid']         = $data['getreward_id'];
                    $map['create_time'] = time();
                    $map['update_time'] = time();
                    $balanceModel->add($map);
                }
                //给被打赏人的余额加上打赏金额
                $getreward = $balanceModel->where(['uid' =>  $data['getreward_id']])->setInc('balance',$data['money']);
                //添加打赏记录
                $rewards = $rewardModel->add($data);

                if($reward && $getreward && $rewards){
                    $balanceModel -> commit();
                    $rewardModel  -> commit();
                    $this->ajaxDie(1);
                }else{
                    $balanceModel -> rollback();
                    $rewardModel  -> rollback();
                    $this->ajaxDie(0);
                }
            }
        }else{
            $this->ajaxDie(43);
        }
    }
    //提现
    public function get_cash(){
        if($this->client_type == 1){
            $this->secureVerify();
            $data['get_cash_id']  = I('post.uid',0,'intval');//打赏人id
            $data['pay_pass']     = I('post.pay_pass',0,'intval');//支付密码
            $data['money']        = I('post.money');//提现金额
            $data['apply_time']   = time();

            //提现时判断提现金额是否大于可用余额
            $cashModel    = M('user_get_cash');
            $balanceModel = M('user_balance');
            $user_b = $balanceModel->where(['uid' => $data['get_cash_id']])->getField('balance');

            $data['get_cash_id']  || $this->ajaxDie(30);
            //提现限制
            $min = C('MORE_WITHDRAWAL_AMOUNT');
            if($data['money'] < $min){
                $this->ajaxDie(119,$min);
            }
            //当前是否有正在提现中的申请
            $cash = $cashModel->where(['get_cash_id' => $data['get_cash_id'],'status' => '1'])->find();
            if($cash){
                $this->ajaxDie(125);
            }
            //查询打赏人支付密码
            $pay_pass  = M('user_balance')->where(['uid' => $data['get_cash_id']])->getField('pay_pass');
            $pay       = password_md5($data['pay_pass']);
            if($pay_pass != $pay){
                $this->ajaxDie(123);
            }
            //判断该用户是否绑定了银行卡
            $bank_n  = M('user_balance')->where(['uid' => $data['get_cash_id']])->getField('bank_card_number');
            if(!$bank_n){//没绑定银行卡 提示用户去网站绑定银行卡
                 $this->ajaxDie(120);
            }
          
            if($user_b){
                if($user_b  < $data['money']){
                    $this->ajaxDie(118);
                }else{
                    $cashModel    ->startTrans();//开启事物
                    $balanceModel ->startTrans();//开启事物
                    $cash    = $cashModel->add($data);//添加提现记录
                    $balance = $balanceModel->where(['uid' => $data['get_cash_id']])->setDec('balance',$data['money']);//账户余额减去提现金额
                    if($cash != false &&  $balance != false){
                        $cashModel     -> commit();
                        $balanceModel  -> commit();
                        $this->ajaxDie(1);
                    }else{
                        $cashModel     -> rollback();
                        $balanceModel  -> rollback();
                        $this->ajaxDie(0);
                    }
                }
            }else{
                $this->ajaxDie(118); 
            }
        }else{
            $this->ajaxDie(43); 
        }  
    }

    //设置支付密码
    public function set_pay_pass(){
        if($this->client_type == 1){
            $this->secureVerify();
            $data = array();
            $data['uid']        = I('post.uid',0,'intval');//用户id
            $pay_pass           = I('post.pay_pass',0,'intval');//支付密码
            $repeat_pay_pass    = I('post.repeat_pay_pass',0,'intval');//确认支付密码

            if($pay_pass  != $repeat_pay_pass)$this->ajaxDie(122);
            $data['uid']  ||  $this->ajaxDie(30);
            if(!is_numeric($pay_pass) ||  strlen($pay_pass) != 6){
                $this->ajaxDie(121);
            }

            $data['pay_pass']  = password_md5($pay_pass);
            $balance           = M('user_balance')-> where(['uid' => $data['uid']])->find();

            if(!$balance){
               $result = M('user_balance')->add($data);
            }else{
               $result = M('user_balance')-> where(['uid' => $data['uid']])->save(array('pay_pass' => $data['pay_pass']));
            }
            if($result){
                $this->ajaxDie(1);
            }else{
                $this->ajaxDie(0);
            }
        }else{
            $this->ajaxDie(43);
        }
    }

    //判断用户是否设置了支付密码
    public function is_pay(){
        if($this->client_type == 1){
            $this->secureVerify();
            $data           = array();
            $data['uid']    = I('post.uid',0,'intval');//用户id
             //账户余额
            $pay_pass       = M('user_balance')->where(['uid' => $data['uid']])->getField('pay_pass');
            //是否设置了支付密码
            if($pay_pass){
                $result = '1';
            }else{
                $result = '0';
            }
            $this->ajaxDie($result);
        }else{
            $this->ajaxDie(43);
        }
    }

    //给app返回卡号后4位
    public function is_card(){
        if($this->client_type == 1){
            $this->secureVerify();
            $data['uid']       = I('post.uid',0,'intval');//用户id
            //提现最小金额  
            $min = C('MORE_WITHDRAWAL_AMOUNT');

            $data['uid'] || $this->ajaxDie(30);

            $balance  = M('user_balance')->field('pay_pass,balance,bank_card_number')->where(['uid' => $data['uid']])->find();
            if($balance){
                if($balance['pay_pass']){//支付密码
                    $result['pay_pass'] = '1';
                }else{
                    $result['pay_pass'] = '0';
                }

                if($balance['balance']){//余额
                    $result['balance'] = $balance['balance'];
                }else{
                    $result['balance'] = '0.00';
                }

                if($balance['bank_card_number']){//绑定卡号
                    $result['bank_card_number'] = substr($balance['bank_card_number'], -4);
                }else{
                    $result['bank_card_number'] = '';
                }
                $result['min'] = $min;
                $this->ajaxDie(1,$result);   
            }else{
                $result['pay_pass'] = '0';
                $result['balance'] = '0.00';
                $result['bank_card_number'] = '';
                $result['min'] = $min;
                $this->ajaxDie(1,$result);  
            }
        }else{
            $this->ajaxDie(43); 
        }
    }

    //打赏记录表
    public function reward_list(){
        if($this->client_type == 1){
            $this->secureVerify();

            //接收参数
            $data['uid']       = I('post.uid',0,'intval');//用户id

            //校验参数
            $data['uid']       || $this->ajaxDie(30);

            $list = M('user_reward')->field('market_id,create_time,money')->where(['reward_id' => $data['uid']])->order('create_time desc')->select();

            foreach ($list as $k => $v) {
                $list[$k]['create_time']  = date('y-m-d H:i', $v['create_time']);
                $list[$k]['title']        = M('goods_market')->where(['id' => $v['market_id']])->getField('title');
            }

            if($list){
                $this->ajaxDie(1,$list); 
            }else{
                $this->ajaxDie(0);   
            }
        }else{
            $this->ajaxDie(43); 
        }
    }

    //被打赏记录表
    public function be_reward_list(){
          if($this->client_type == 1){
            $this->secureVerify();

            //接收参数
            $data['uid']       = I('post.uid',0,'intval');//用户id

            //校验参数
            $data['uid']       || $this->ajaxDie(30);

            $list = M('user_reward')->field('reward_name,create_time,money')->where(['getreward_id' => $data['uid']])->order('create_time desc')->select();

            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('y-m-d H:i', $v['create_time']);
            }

            if($list){
                $this->ajaxDie(1,$list); 
            }else{
                $this->ajaxDie(0);   
            }
        }else{
            $this->ajaxDie(43); 
        }
    }
    //充值记录表
    public function recharge_list(){
         if($this->client_type == 1){
            $this->secureVerify();

            //接收参数
            $data['uid']       = I('post.uid',0,'intval');//用户id

            //校验参数
            $data['uid']      || $this->ajaxDie(30);

            $list = M('user_recharge')->field('money,status,create_time')->where(['uid' => $data['uid'],'status' => '1'])->order('create_time desc')->select();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('y-m-d H:i', $v['create_time']);
            }
            if($list){
                $this->ajaxDie(1,$list); 
            }else{
                $this->ajaxDie(0);   
            }
        }else{
            $this->ajaxDie(43); 
        }
    }
    //提现记录表
    public function get_cash_list(){
         if($this->client_type == 1){
            $this->secureVerify();

            //接收参数
            $data['uid']       = I('post.uid',0,'intval');//用户id

            //校验参数
            $data['uid']       || $this->ajaxDie(30);

            $list = M('user_get_cash')->field('money,apply_time,status')->where(['get_cash_id' => $data['uid']])->order('apply_time desc')->select();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('y-m-d H:i', $v['apply_time']);
            }
            if($list){
                $this->ajaxDie(1,$list); 
            }else{
                $this->ajaxDie(0);   
            }
        }else{
            $this->ajaxDie(43); 
        }
    }
}
