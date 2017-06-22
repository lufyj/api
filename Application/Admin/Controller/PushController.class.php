<?php

namespace Admin\Controller;

/**
 * 消息推送
 * @Author jingwei
 * @Dtae 2016/11/15
 *
 */
class PushController extends AdminController{

	/**
	 * 显示系统消息列表
	 */
	public function index(){

		$show_num = I('get.show_num', C('LIST_ROWS'), 'intval');//当前页显示条数
		$page_num = I('get.page_num', 1, 'intval');//页码

		//当点击编辑页的时候保存住当前页码，此处Cookie关闭浏览器时自动清除
		if (Cookie('__rpage__')) {
			$page_num = Cookie('__rpage__');
			Cookie('__rpage__', null);
		}

		$all_list = D('Push')->getList(I('get.condition'), $show_num, $page_num);
		//记录当前页
		Cookie('__page__', $all_list['page_num']);

		foreach($all_list['list'] as $k=>$v){
			$all_list['list'][$k]['operator']=$this->_getAdminName(intval($v['operator_id']));
		}

		$this->assign('show_num',$show_num);
		$this->assign('page_num',$all_list['page_num']);
		$this->assign('total_page',$all_list['total_page']);
		$this->assign('all_count',$all_list['all_count']);
		$this->assign('list',$all_list['list']);
		$this->meta_title = '系统消息列表';
		if (IS_AJAX) {
			$this->display('table');
			exit;
		}
		$this->display();
	}

	/* 删除一条推送信息 */
	public function del(){
		if(IS_AJAX && IS_GET){
			$id = I('get.id', 0, 'intval');
			if($id <= 0) $this->ajaxReturn(array('code' => 0,'msg' => '非法操作'));
			$res = D('Push')->pushDelete($id);
			if((int)$res > 0) $this->ajaxReturn(array('code' => 1,'msg' => '删除成功'));
			$this->ajaxReturn(array('code' => 0,'msg' => '删除失败'));
		}
	}
	/* 添加推送信息 */
	public function add()	{
		if(IS_AJAX && IS_POST){			
			$post = I('post.');
			if($post && clearXSS($post['message']) && in_array(intval($post['platform']),array(1,2,3))){
				$info = array();
				$info['type'] =1;
				$info['platform'] = intval($post['platform']);
				$info['message'] = clearXSS($post['message']);
				$info['details']=clearXSS($post['details'])==''?'':clearXSS($post['details']);
				$title=$info['message'];
				$details=$info['details'];
				$platform='all';
				if($info['platform']==1){
					$platform='all';
				}elseif($info['platform']==2){
					$platform='android';
				}elseif($info['platform']==3){
					$platform='ios';
				}
				//系统消息，成功后保存消息
				$st=$this->_sendMessage($platform,$details,$title);
				if($st){
					$info['operator_id']=UID;
					$info['add_time']=NOW_TIME;
					$info['msg_id']=$st;
					$status=M('Push')->add($info);
					if($status){
						$this->success('推送成功',U('Push/index'));
					}else{
						$this->error('推送失败');
					}
				}else{
					$this->error('推送失败');
				}
			}else{
				$this->error('推送失败');
			}			
		}else{
			$this->meta_title = '添加系统消息';
			$this->display();
		}	
	}
	//获取管理员名称
	private function _getAdminName($uid=0){
		$name='';
		if($uid>0){
			$useName=M('UcenterMember')->where('id='.$uid)->field('id,username')->find();
			if($useName){
				$name=$useName['username'];
				unset($useName);
				return $name;
			}else{
				return $name;
			}
		}else{
			return $name;
		}
	}

	//系统消息
	private function _sendMessage($platform='',$details='',$title=''){
		if($platform && $details && $title){
			Vendor('jpush.autoload');
			$app_key =C('Jpush_AppKey');
			$master_secret = C('Jpush_MasterSecret');
			$client=new \JPush\Client($app_key,$master_secret,null);

			try {
				$response = $client->push()
					->setPlatform($platform)
					->addAllAudience()
					->setNotificationAlert($details)
					->iosNotification($details, array( //ios设备系统消息
						'sound' => 'sound.caf',
						'badge' => '+1',
						'content-available' => true,
						'mutable-content' => true,
						'category' => 'jiguang',
						'extras' => array(
							'title' => $title,
							'details' => $details,
							'time'=>NOW_TIME
						),
					))
					->androidNotification($details, array( //android设备系统消息
						'title' => $title,
						'build_id' => 2,
						'extras' => array(
							'title' => $title,
							'details' => $details,
							'time'=>NOW_TIME
						),
					))
					->message($details, array(
						'title' => $title,
						'content_type' => 'text',
						'extras' => array(
							'key' => 'value',
						),
					))
					/*->options(array(
						// sendno: 表示推送序号，纯粹用来作为 API 调用标识，API 返回时被原样返回，以方便 API 调用方匹配请求与返回
						// 'sendno' => 100,

						// time_to_live: 表示离线消息保留时长(秒)，
						// 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
						// 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
						// 'time_to_live' => 1,

						// apns_production: 表示APNs是否生产环境，
						// True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境
						'apns_production' => false,

						// big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
						// 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
						// 这里设置为 1 仅作为示例
						// 'big_push_duration' => 1
					))*/
					->send();

			} catch (\JPush\Exceptions\APIConnectionException $e) {
				print $e;
			} catch (\JPush\Exceptions\APIRequestException $e) {
				print $e;
			}

			if($response['http_code']==200){
				return $response['body']['msg_id'];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
}
