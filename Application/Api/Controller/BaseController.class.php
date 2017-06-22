<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 重构基类
 * @author 飞哥--wpf
 *
 */
class BaseController extends Controller {	
	protected $client_type = 0;
	
	protected function _initialize(){
		//判断来自pc还是手机
		$this->client_type = I('clientType', 0,'intval');
		
		//读取数据库中的配置
		$config = api('Config/lists');
		C($config); //添加配置
	}
	
	/**
	 * ajax返回数据
	 * @param unknown $code 状态码
	 * @param string $data 要返回的数据
	 * -1	操作失败
	 * 1	操作成功
	 * 10	请输入2-10个字符的用户名
	 * 20	请输入正确的手机号
	 * 21	手机号被占用
	 * 30	验证码不正确或已过期
	 * 35	发送短信过于频繁，请稍后再试
	 * 36	短信验证码错误或已过期
	 * 40	请输入6-20个字符的密码
	 * 41	两次密码输入的不一致
	 * 80	请查看用户协议	 
	 * 103	登录失效
	 */
	protected function ajaxDie($code, $data = '',$dataHot,$sign_status){
		$res = array( 'code' => $code );
		if($data === ''){
		}else{
			$res['data'] = $data;
		}
		if($dataHot){
			$res['dataHot'] = $dataHot;
		}
		if($sign_status){
			$res['sign_status'] = $sign_status;
		}
		$this->ajaxReturn($res);die();
	}
	/* 安全验证 */
	protected function secureVerify() {
		$param = I('post.');
		$uid = trim($param['uid']);
		$token = trim($param['token']);	
		$secure = false;
		if($uid && $token) {
			//根据uid找对应的token
			$_token = M('User')->where(array('id' => $uid))->getField('token');
			if($token != $_token){
				$this->ajaxReturn(array('code' => 103));
			}
		}else{
			$this->ajaxReturn(array('code' => 103));
		}
	}
	   /**
     * 获取分类，药材，热点药材
     * @param number $child 是否返回全部药材
     * @param number $hot	是否返回热点药材
     * @return 返回分类信息
     */
    protected function getC($child = 0, $hot = 0){
    	$cates = S('global_cates');   	
    	if(!$cates){
    		$cates = D('Goods')->getAllCatsForNav();
    		S('global_cates', $cates);
    	}
    	foreach ($cates as $k => $v){
    		if(!$child) unset($cates[$k]['_child']);
    		if(!$hot) unset($cates[$k]['_hot']);
    	}
    	return $cates;
    }
}
?>