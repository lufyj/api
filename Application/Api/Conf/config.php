<?php
return array(
	'WPF_AUTH_KEY' =>  'FQS&R0Zzc6"2_{apnA,BvCo1%x^KIjh(De!WUuY3', //加密KEY
	//'配置项'=>'配置值'
	'SESSION_PREFIX' => 'ydw_home', //session前缀
	'COOKIE_PREFIX'  => 'ydw_home_', // Cookie前缀 避免冲突
	 /* 图片上传相关配置 移入common配置中*/

	/*极光推送AppKey和Master Secret*/
	'Jpush_AppKey'=>'728d399669f0b1b509c0e52a',
	'Jpush_MasterSecret'=>'76a6f3c95b44e67683ef52a5',

	//消息队列服务器配置
	'rabbitmq'=>array(
		'host' => '60.205.145.174',
		'port' => '5672',
		'login' => 'mqadmin',
		'password' => 'mq999999',
		'vhost'=>'/',
		'queue'=>'ydw'
	),

	//求购状态变更推送消息内容
	'pushdemandinfo'=>array(
		1=>array(
				'title'=>'药都网',
				//B用户进行投标，此时向A用户发送消息内容是：B用户进行投标
				'content'=>'【药都网】提示：有用户投标您的求购，请及时查看投标信息。',
			),
			2=>array(
				'title'=>'药都网',
				//A用户选标完成后，此时向B用户发送消息内容是：B用户中标（包括后台操作）
				'content'=>'【药都网】提示:恭喜您中标，请及时查看中标结果。',
			),
			3=>array(
				'title'=>'药都网',
				//B（后台发货）发货后，此时向A用户发送消息内容是：B用户已经发货
				'content'=>'【药都网】提示:货主已发货，请及时跟踪物流信息。',
			),
			4=>array(
				'title'=>'药都网',
				//A用户（后台）收到货后进行签售：此时向B用户发送内容是：A用户已经签收
				'content'=>'【药都网】提示:您的货物已签收，本次交易成功。',
			)
	)
);