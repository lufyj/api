<?php
return array(
	//'配置项'=>'配置值'
	/* 主题设置 */
	'DEFAULT_THEME' =>  'default',  // 默认模板主题名称
	/* 数据缓存设置 */
	'DATA_CACHE_PREFIX' => 'ydw_', // 缓存前缀
	'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型
	'DATA_CACHE_TIME'	=> 24*60*60,//缓存默认为24个小时
	'URL_MODEL'             =>  2, //设置url规则
	/* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
			'__STATIC__' => __ROOT__ . '/Public/static',
			'__HOME__' => __ROOT__ . '/Public/' . MODULE_NAME,
			'__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
			'__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
			'__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
			'__TMPL__' => '/Application/Home/View/default/Company'//暂时待定jingwei,仅供公司选择风格使用
	),
	 /* 图片上传相关配置 移入common配置中*/
    
	/* SESSION 和 COOKIE 配置 */
	'SESSION_PREFIX' => 'ydw_home', //session前缀
	'COOKIE_PREFIX'  => 'ydw_home_', // Cookie前缀 避免冲突	
	'WPF_AUTH_KEY' =>  'FQS&R0Zzc6"2_{apnA,BvCo1%x^KIjh(De!WUuY3', //加密KEY
	/* wpf自定义配置 */
	'login_error_time_zone' => 60,
	'key_secret' => '123',
	'val_secret' => 'wpf',

	/*当前公司用户权限下可发表动态数量*/
	'newsLevel'=>array(
		'l1'=>10,
		'l2'=>20,
		'l3'=>30,
		'l4'=>40,
		'l5'=>50,
	),

	//加工方式配置
	'processMethod'=>array(
		0=>'饮片加工/生产/包装',
		1=>'花果茶生产',
		2=>'中药打粉',
		3=>'中药饮品加工/生产',
		4=>'成品配方生产',
		5=>'保健药品包装生产'
	),

	//检测方法配置
	'checkMethod'=>array(
		0=>'中药材含量检验',
		1=>'中药材常规三检',
		2=>'中药材硫磺检验'
	),

	//加载支付宝配置文件
	'LOAD_EXT_CONFIG' => 'alipay',

	//企业业务权限
	'business'=>array(
		1=>'检测',//检测
		2=>'物流',//物流
		3=>'包装',//包装
		4=>'加工',//加工
		5=>'仓库',//仓库
	)

);