<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>
			<?php if($meta_title){ echo $meta_title.'|'; } ?>药都网一站式服务平台</title>
		<link rel="shortcut icon" type="image/x-icon" href="/Public/Home/images/logo-icon.ico">
		<link rel="stylesheet" type="text/css" href="/Public/Home/css/reset.css" />
		<link rel="stylesheet" type="text/css" href="/Public/Home/css/head.css" />
		
	<link rel="stylesheet" type="text/css" href="/Public/Home/index/css/swipe.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Home/index/css/index.css" />
	<script type="text/javascript">
		var catObj = <?php echo ($cateJson); ?>;
	</script>

		<script src="/Public/static/jquery-1.8.3.min.js" type="text/javascript" charset="utf-8"></script>
	</head>

	<body>
		<div class="header">
			<div class="header_nav">
				<div class="nav">
					<?php if(!empty($_SESSION['ydw_home']['user_sign'])): ?><p class="room l"><?php echo session('user_sign.realname');?> 您好，欢迎来到药都网！ &nbsp;&nbsp;&nbsp;&nbsp;
							<a href="<?php echo U('user/logout','',false);?>">退出</a>
						</p>
						<?php else: ?>
						<p class="room l">
							您好，欢迎来到药都网！ &nbsp;&nbsp;&nbsp;&nbsp;请&nbsp;&nbsp;
							<a href="<?php echo U('login/index','',false);?>">登录</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href="<?php echo U('login/register','',false);?>">免费注册</a>
						</p><?php endif; ?>
					<ul class="ydw_nav r">
						<li>
							<a href="<?php echo U('Index/index');?>" class="index-close">药都网首页</a>
						</li>
						<li class="divider index-close">|</li>
						<li class="pull_down">
							<a href="<?php echo U('user/profile');?>">我的药都网<b></b></a>
							<div class="nav_mycenter">
								<a href="<?php echo U('user/follow');?>">我的关注</a>
								<a href="<?php echo U('supply/index');?>">我的供应</a>
								<a href="<?php echo U('demand/index');?>">我的求购</a>
								<a href="<?php echo U('user/suggest');?>">意见反馈</a>
								<a href="<?php echo U('user/profile');?>">个人资料</a>
							</div>
						</li>
						<li class="divider">|</li>
						<li>
							<a href="<?php echo U('demand/publish');?>">发布求购</a>
						</li>
						<li class="divider">|</li>
						<li>
							<a href="<?php echo U('supply/publish');?>">发布供应</a>
						</li>
						<li class="divider">|</li>
						<li class="pull_down">
							<a href="<?php echo U('help/demandHelp');?>">帮助中心<b></b></a>
							<div class="nav_mycenter">
								<a href="<?php echo U('help/approve');?>">企业认证</a>
								<a href="<?php echo U('help/attention');?>">我的关注</a>
								<a href="<?php echo U('help/demandHelp');?>">求购指南</a>
								<a href="<?php echo U('help/supplyHelp');?>">供货指南</a>
								<a href="<?php echo U('help/feedback');?>">意见反馈</a>
								<a href="<?php echo U('help/personalData');?>">个人资料</a>
							</div>
							<!--求&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;购 供&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;货-->
						</li>
						<li class="divider">|</li>
						<li class="pull_down">
							<a href="#">关注药都网<b></b></a>
							<div class="nav_mycenter ercode clearfix">
								<p class="l">
									<img src="/Public/Home/images/ydw_app1.png" /><br />
									<span>扫描二维码下载</span>
								</p>
								<p class="l">
									<a href="#">IOS版下载</a>
									<a href="#">Android版下载</a>
								</p>
							</div>
						</li>
					</ul>
				</div>
			</div>
			<!--药都网的搜索框-->
			<div class="header_search clearfix">
				<!--中共包括三个部分，第一部分logo-->
				<div class="l search_left">
					<a href="/"><img src="/Public/Home/images/logo_03.png" /></a>
				</div>
				<!--第二部分搜索框-->
				<div class="l search_middle">
					<div class="search_middle_top">
						<div class="find_search l">
							<span class="s_icon"></span>
							<input type="text" class="serclass buyaogang" readonly="readonly" value="供应信息">
							<!--<span class="serclass buyaogang">供应信息</span>-->
							<ul class="search_small">
								<li>
									<a href="javascript:;" rel="/demand/lst">求购信息</a>
								</li>
								<li>
									<a href="javascript:;" rel="/supply/lst">供应信息</a>
								</li>
								<li>
									<a href="javascript:;" rel="/goods/market">药材行情</a>
								</li>
							</ul>
						</div>
						<form id="search-form" method="get" action="/supply/lst" class="search">
							<input type="text" id="q" class="setext" name="q" placeholder="请输入要搜索的词" autocomplete="off" onkeydown="javascript:if(event.keyCode==13) search();if(event.keyCode==38||event.keyCode==40) wag(event);">
							<input type="hidden" id="sub" name="sub" />
							<input type="button" class="sebottom" value="搜索" onclick="search()">
						</form>
						<!--搜索建议下拉框-->
						<ul class="search-suggest">
						</ul>
						<!--搜索建议下拉框结束-->
					</div>
					<div class="search_middle_bottom">热门搜索：
						<?php if(is_array($hots)): foreach($hots as $key=>$vo): ?><a href="##"><?php echo ($vo["goods_name"]); ?></a>&nbsp;&nbsp;<?php endforeach; endif; ?>
					</div>
				</div>
				<!--第三部分客服电话-->
				<div class="j-tel r">
					<!--<p class="hotline-txt"><b>客服Q&nbsp;Q：</b> <span>09:00-17:30</span></p>-->
					<!--<p class="hotline"><b>客服Q&nbsp;Q：</b>785831157</p>-->
					<p class="hotline"><b>客服Q&nbsp;Q：</b>785831157</p>
					<p class="hotline-txt"><b>营业时间：</b><span>09:00-17:30</span></p>
				</div>
			</div>
		</div>
		
	<!--nav开始-->
	<div class="nav-wrap clearfix">
		<div class="nav clearfix">
			<span>全服务分类</span>
			<ul class="nav-list clearfix">
				<li class="current">
					<a href="/">首页</a>
				</li>
				<li>
					<a href="<?php echo U('supply/lst');?>">供应信息</a>
				</li>
				<li>
					<a href="<?php echo U('demand/lst');?>">求购信息</a>
				</li>
				<li>
					<a href="<?php echo U('goods/market');?>">药材行情</a>
				</li>
			</ul>
		</div>
	</div>
	<!--nav结束-->
	<!--bannar开始-->
	<div class="banner-wp clearfix">
		<ul class="drug-class l">
			<!--药材分类和二级分类-->
		</ul>
		<div class="middle l">
			<!-- 如果需要分页器 -->
			<div class="pagination"></div>
			<div class="banner swiper-container">
				<div class="swiper-wrapper">
					<?php if(is_array($banners)): foreach($banners as $key=>$vo): ?><div class="swiper-slide">
							<a href="<?php echo ($vo["link_url"]); ?>" title="<?php echo ($vo["title"]); ?>"><img src="<?php echo ($vo["img_url"]); ?>" alt="<?php echo ($vo["title"]); ?>" /></a>
						</div><?php endforeach; endif; ?>
				</div>
			</div>
		</div>
		<?php if(empty($_SESSION['ydw_home']['user_sign'])): ?><!--暂定首页登录部分方框-->
			<div class="login-box l">
				<div class="title clearfix">
					<span class="l">登录药都网</span>
					<a href="<?php echo U('login/register');?>" class="r">免费注册</a>
				</div>
				<div class="verify"><span>药都网欢迎您！</span></div>
				<div class="input clearfix">
					<input type="text" class="user-name" id="loginname" maxlength="11" autocomplete="off" placeholder="请输入手机号" />
					<input type="password" class="psw" id="loginpwd" autocomplete="off" placeholder="请输入登录密码" onkeydown="keyDown(event);" />
				</div>
				<div class="title clearfix remb">
					<span class="is-remb l"></span>
					<span class="l remb-txt">记住我</span>
					<a href="<?php echo U('login/findpwd');?>" class="r">忘记密码？</a>
				</div>
				<div class="op clearfix">
					<span class="login-btn l" id="submit_btn">登 陆</span>
				</div>
				<div class="publish-div">
					<a href="<?php echo U('demand/publish');?>">发布求购</a>
					<a>|</a>
					<a href="<?php echo U('supply/publish');?>">发布供应</a>
				</div>
			</div>
			<!--关于登录部分结束-->
			<?php else: ?>
			<!--关于登录成功以后部分-->
			<div class="login-box l">
				<dl class="success-title">
					<!--/Public/Home/index/images/photo.jpg-->
					<dt><img src="/Public/Home/index/images/head.png"/></dt>
					<dd>Hi，<span><?php echo session('user_sign.realname');?></span></dd>
					<dd>欢迎来到药都网</dd>
				</dl>
				<div class="success-center">
					<a href="<?php echo U('user/profile','',false);?>">我的药都网</a>&nbsp;
					<a href="<?php echo U('user/logout','',false);?>">退出</a>
				</div>
				<div class="publish-div">
					<a href="<?php echo U('demand/publish');?>">发布求购</a>
					<a>|</a>
					<a href="<?php echo U('supply/publish');?>">发布供应</a>
				</div>
			</div>
			<!--关于登录成功以后部分结束--><?php endif; ?>
	</div>
	<!--bannar开始-->
	<!--主页内容开始-->
	<div class="content-wp">
		<div class="apply cont-box">
			<div class="title clearfix title_a">
				<span class="left">供应</span>
				<a href="<?php echo U('supply/lst');?>" class="right r">更多&gt;&gt;</a>
			</div>
			<div class="cont  clearfix">
				<div class="left l clearfix">
					<div class="img l">
						<img src="/Public/Home/images/xq_03.png" alt="" />
					</div>
					<ul class="message l txt-clip">
						<li class="top">
							<ul class="clearfix">
								<li>药名</li>
								<li>数量</li>
								<li>规格</li>
								<li>联系人</li>
								<li>电话</li>
							</ul>
						</li>
						<?php if(is_array($supplys)): foreach($supplys as $k=>$vo): if(($k) < "5"): ?><li>
									<a href="<?php echo U('supply/detail',array('id'=>$vo['id']));?>">
										<ul class="clearfix">
											<li><?php echo ($vo["goods_name"]); ?></li>
											<li>
												<?php if($vo["num"] == '-1' ): ?>大货   <?php else: ?> <?php echo ($vo["num"]); endif; ?>
											</li>
											<li><?php echo ((isset($vo["goods_attr_name"]) && ($vo["goods_attr_name"] !== ""))?($vo["goods_attr_name"]):'无'); ?></li>
											<li><?php echo ($vo["contacts"]); ?></li>
											<li><?php echo ($vo["mobile"]); ?></li>
										</ul>
									</a>
								</li><?php endif; endforeach; endif; ?>
					</ul>
				</div>
				<div class="right l">
					<div class="img l">
						<img src="/Public/Home/images/xq_12.png" alt="" />
					</div>
					<ul class="message l txt-clip">
						<li class="top">
							<ul class="clearfix">
								<li>药名</li>
								<li>数量</li>
								<li>规格</li>
								<li>联系人</li>
								<li>电话</li>
							</ul>
						</li>
						<?php if(is_array($supplys)): foreach($supplys as $k=>$vo): if(($k) > "4"): ?><li>
									<a href="<?php echo U('supply/detail',array('id'=>$vo['id']));?>">
										<ul class="clearfix">
											<li><?php echo ($vo["goods_name"]); ?></li>
											<li>
												<?php if($vo["num"] == '-1' ): ?>大货   <?php else: ?> <?php echo ($vo["num"]); endif; ?>
											</li>
											<li><?php echo ((isset($vo["goods_attr_name"]) && ($vo["goods_attr_name"] !== ""))?($vo["goods_attr_name"]):'无'); ?></li>
											<li><?php echo ($vo["contacts"]); ?></li>
											<li><?php echo ($vo["mobile"]); ?></li>
										</ul>
									</a>
								</li><?php endif; endforeach; endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="notice-ban">
			<a href="javascript:;"><img src="/Public/Home/index/images/pic1.png" alt="<?php echo ($vo["title"]); ?>" class="ad" /></a>
		</div>
		<div class="need cont-box">
			<div class="title clearfix title_b">
				<span class="left">求购</span>
				<a href="<?php echo U('demand/lst');?>" class="right r">更多&gt;&gt;</a>
			</div>
			<div class="cont  clearfix">
				<div class="left l clearfix">
					<div class="img l">
						<img src="/Public/Home/images/xq_03.png" alt="" />
					</div>
					<ul class="message l txt-clip">
						<li class="top">
							<ul class="clearfix">
								<li>药名</li>
								<li>数量</li>
								<li>规格</li>
								<li>联系人</li>
								<li>电话</li>
							</ul>
						</li>
						<?php if(is_array($demands)): foreach($demands as $k=>$vo): if(($k) < "5"): ?><li>
									<a href="<?php echo U('demand/detail',array('id'=>$vo['id']));?>">
										<ul class="clearfix demand-hover">
											<li><?php echo ($vo["goods_name"]); ?></li>
											<li><?php echo ($vo["num"]); ?></li>
											<li><?php echo ((isset($vo["goods_attr_name"]) && ($vo["goods_attr_name"] !== ""))?($vo["goods_attr_name"]):'无'); ?></li>
											<li><?php echo ($vo["contacts"]); ?></li>
											<li><?php echo ($vo["mobile"]); ?></li>
										</ul>
									</a>
								</li><?php endif; endforeach; endif; ?>
					</ul>
				</div>
				<div class="right l">
					<div class="img l">
						<img src="/Public/Home/images/xq_05.png" alt="" />
					</div>
					<ul class="message l txt-clip">
						<li class="top">
							<ul class="clearfix">
								<li>药名</li>
								<li>数量</li>
								<li>规格</li>
								<li>联系人</li>
								<li>电话</li>
							</ul>
						</li>
						<?php if(is_array($demands)): foreach($demands as $k=>$vo): if(($k) > "4"): ?><li>
									<a href="<?php echo U('demand/detail',array('id'=>$vo['id']));?>">
										<ul class="clearfix demand-hover">
											<li><?php echo ($vo["goods_name"]); ?></li>
											<li><?php echo ($vo["num"]); ?></li>
											<li><?php echo ((isset($vo["goods_attr_name"]) && ($vo["goods_attr_name"] !== ""))?($vo["goods_attr_name"]):'无'); ?></li>
											<li><?php echo ($vo["contacts"]); ?></li>
											<li><?php echo ($vo["mobile"]); ?></li>
										</ul>
									</a>
								</li><?php endif; endforeach; endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="notice-ban">
			<a href="javascript:;" title=""><img src="/Public/Home/index/images/pic2.png" alt="" class="ad" /></a>
		</div>
		<div class="other cont-box">
			<div class="cont  clearfix">
				<div class="title-left l clearfix title_c">
					<span>物流</span>
					<a href="<?php echo U('CompanyShow/deliveryList');?>" class="r">更多&gt;&gt;</a>
				</div>
				<div class="title-right clearfix l title_d">
					<span>仓库</span>
					<a href="<?php echo U('CompanyShow/storeList');?>" class="r">更多&gt;&gt;</a>
				</div>
				<div class="left l clearfix">
					<div class="img l">
						<img src="/Public/Home/images/xq_22.png" alt="" />
					</div>
					<ul class="message l logistics">
						<li class="top">
							<ul class="clearfix">
								<li>出发地</li>
								<li>目的地</li>
								<li>车辆类型</li>
								<!--<li style="width:16%">&nbsp;</li>-->
							</ul>
						</li>
						<?php if(is_array($delivery)): foreach($delivery as $key=>$del): ?><li>
								<a href="<?php echo U('CompanyShow/delivery_details',array('id'=>$del['id']));?>">
									<ul class="clearfix log-container">
										<li><?php echo ($del["begin_s"]); ?></li>
										<li><?php echo ($del["end_s"]); ?></li>
										<li><?php echo ($del["type"]); ?></li>
										<!-- 	<li class="log-hover">
											<span>简介详情&gt;&gt;</span>
											<div class="brief-visiable">
												<ul class="clearfix" id="brief-con">
													<li>
														<div>出发地</div>
														<p><?php echo ($del["begin"]); ?></p>
													</li>
													<li class="icon-r"><p></p><i></i></li>
													<li>
														<div>目的地</div>
														<p><?php echo ($del["end"]); ?></p>
													</li>
												</ul>
												<p><span style="color:#999">简介：</span><?php echo ($del["desc"]); ?></p>
											</div>
										</li> -->
									</ul>
								</a>
							</li><?php endforeach; endif; ?>
					</ul>
				</div>
				<div class="right l">
					<div class="img l">
						<img src="/Public/Home/images/xq_05.png" alt="" />
					</div>
					<ul class="message l txt-clip">
						<li class="top">
							<ul class="clearfix">
								<li>类型</li>
								<li>面积 (m&sup2;)</li>
								<li>层高 (m)</li>
								<li>联系人</li>
								<li>电话</li>
							</ul>
						</li>
						<?php if(is_array($store)): foreach($store as $key=>$st): ?><li>
								<a href="<?php echo U('CompanyShow/storeInfoIndex',array('i'=>$st['id']));?>">
									<ul class="clearfix">
										<li><?php echo ($st["type"]); ?></li>
										<li><?php echo ($st["size"]); ?></li>
										<li><?php echo ($st["height"]); ?></li>
										<li><?php echo ($st["contacts"]); ?></li>
										<li><?php echo ($st["mobile"]); ?></li>
									</ul>
								</a>
							</li><?php endforeach; endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<!--录播图开始-->
		<div class="lunb_zhan">实力代工 信誉保证</div>
		<div class="serve-wp  swiper-container">
			<div class="swiper-wrapper">
				<div class="swiper-slide">
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div class="show-msg">

							</div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
						<li class="small last">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
					</ul>
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" />
							<div></div>
						</li>
						<li class="last">
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
					</ul>
				</div>
				<div class="swiper-slide">
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/lunbo_05.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/lunbo_05.jpg" alt="" />
							<div></div>
						</li>
						<li class="small last">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
					</ul>
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/lunbo_05.jpg" alt="" />
							<div></div>
						</li>
						<li class="last">
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
					</ul>
				</div>
				<div class="swiper-slide">
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/lunbo_05.jpg" alt="" />
							<div></div>
						</li>
						<li class="small last">
							<img src="/Public/Home/images/display_14.jpg" alt="" />
							<div></div>
						</li>
					</ul>
					<ul class="clearfix">
						<li>
							<img src="/Public/Home/images/lunbo_05.jpg" alt="" />
							<div></div>
						</li>
						<li class="small">
							<img src="/Public/Home/images/display_14.jpg" />
							<div></div>
						</li>
						<li>
							<img src="/Public/Home/images/lunbo_03.jpg" alt="" />
							<div></div>
						</li>
						<li class="last">
							<img src="/Public/Home/images/display_06.jpg" alt="" />
							<div></div>
						</li>
					</ul>
				</div>

			</div>
			<!-- 如果需要分页器 -->
			<!-- 如果需要导航按钮 -->
			<a class="arrow-left1"></a>
			<a class="arrow-right1"></a>
		</div>
		<!--轮播图结束-->
		<div class="notice-ban">
			<a href="javascript:;" title=""><img src="/Public/Home/index/images/pic3.png" alt="" class="ad" /></a>
		</div>
		<div class="new-notice cont-box">
			<div class="notice-title clearfix">
				<span>最新公告</span>
			</div>
			<div class="content clearfix">
				<ul class="l">
					<?php if(is_array($notices)): foreach($notices as $k=>$vo): ?><li>
							<span><?php echo ($k+1); ?>、</span>
							<a href="<?php echo U('about/detail',array('id'=>$vo['id']));?>"><?php echo ($vo["title"]); ?></a>
						</li>
						<?php if(($k) == "4"): ?></ul>
				<ul class="l"><?php endif; endforeach; endif; ?>
				</ul>
			</div>
		</div>
		<!--友情链接-->
		<div class="friendly-link">
			<div class="top">
				友情链接
			</div>
			<ul class="content clearfix">
				<li>
					<a href="javascript:;">凤凰中医</a>
				</li>
				<li>
					<a href="javascript:;">中国平安官网</a>
				</li>
				<li>
					<a href="javascript:;">家庭医生在线</a>
				</li>
				<li>
					<a href="javascript:;">网上药店</a>
				</li>
				<li>
					<a href="javascript:;">健客</a>
				</li>
				<li>
					<a href="javascript:;">百济新特药房</a>
				</li>
				<li>
					<a href="javascript:;">药品网购</a>
				</li>
				<li>
					<a href="javascript:;">有问必答知识库</a>
				</li>
				<li>
					<a href="javascript:;">预约挂号</a>
				</li>
				<li>
					<a href="javascript:;">心理咨询</a>
				</li>
				<li>
					<a href="javascript:;">妇科疾病</a>
				</li>
				<li>
					<a href="javascript:;">百济新特药房</a>
				</li>
				<li>
					<a href="javascript:;">凤凰中医</a>
				</li>
				<li>
					<a href="javascript:;">中国平安官网</a>
				</li>
				<li>
					<a href="javascript:;">心理咨询</a>
				</li>
				<li>
					<a href="javascript:;">养生</a>
				</li>
				<li>
					<a href="javascript:;">预约挂号</a>
				</li>
				<li>
					<a href="javascript:;">百济新特药房</a>
				</li>
				<li>
					<a href="javascript:;">中华中医网</a>
				</li>
				<li>
					<a href="javascript:;">39药品</a>
				</li>
				<li>
					<a href="javascript:;">药品</a>
				</li>
				<li>
					<a href="javascript:;">心理咨询</a>
				</li>
				<li>
					<a href="javascript:;">家庭医生在线</a>
				</li>
				<li>
					<a href="javascript:;">药咨讯网</a>
				</li>
			</ul>
		</div>
	</div>
	<!--主页内容结束-->

		<!--脚-->
		<div class="footer-wrap">
			<!--药材更齐全等-->
			<!--底部：品质保障:1000-->
			<div class="foot_box_bread">
				<ul>
					<li class="bread_width">
						<img src="/Public/Home/images/ydw_easy.png" />
					</li>
					<li>
						<h2>药材更齐全</h2>
						<span>轻松购物 畅选无忧</span>
					</li>
					<li class="bread_width">
						<img src="/Public/Home/images/ydw_serve.png" />
					</li>
					<li>
						<h2>服务更精致</h2>
						<span>正品行货 优质体验</span>
					</li>
					<li class="bread_width">
						<img src="/Public/Home/images/ydw_safe.png" />
					</li>
					<li>
						<h2>今后有保障</h2>
						<span>七天无理由退换货</span>
					</li>
					<li class="bread_width">
						<img src="/Public/Home/images/ydw_health.png" />
					</li>
					<li>
						<h2>购药更安全</h2>
						<span>品质护行 安全健康</span>
					</li>
				</ul>
			</div>
			<!--新手入门等-->
			<div class="foot_box">
				<ul>
					<li>
						<h3>新手入门</h3>
					</li>
					<li>
						<a href="<?php echo U('help/register');?>">会员注册</a>
					</li>
					<li>
						<a href="<?php echo U('help/login');?>">登录</a>
					</li>
					<li>
						<a href="<?php echo U('help/findpwd');?>">找回密码</a>
					</li>
				</ul>
				<!-- 第二组 ：采购帮助 -->
				<ul>
					<li>
						<h3>企业用户</h3>
					</li>
					<li>
						<a href="<?php echo U('help/approve');?>">企业认证</a>
					</li>
					<li>
						<a href="<?php echo U('help/service');?>">企业服务</a>
					</li>
					<li>
						<a href="<?php echo U('help/card');?>">企业名片</a>
					</li>
				</ul>
				<!-- 第三组 ：支付方式 -->
				<ul>
					<li>
						<h3>支付方式</h3>
					</li>
					<li>
						<a href="<?php echo U('help/payment1');?>">在线支付</a>
					</li>
					<li>
						<a href="<?php echo U('help/payment2');?>">线下支付</a>
					</li>
				</ul>
				<!-- 第四组 ：关于我们 -->
				<ul>
					<li>
						<h3>关于我们</h3>
					</li>
					<li>
						<a href="<?php echo U('help/brief');?>">公司简介</a>
					</li>
					<li>
						<a href="<?php echo U('help/contact');?>">联系客服</a>
					</li>
				</ul>
				<div class="load_code l">
					<a href="#" style="margin-right:85px;"><img src="/Public/Home/images/ydw_app2.png" /></a>
					<a href="#"><img src="/Public/Home/images/ydw_w2.png" /></a>
				</div>
			</div>
			<!--关于我们等-->
			<div class="footer">
				<!-- 3、版权图片 -->
				<div class="imgs">
					<a href="#">
						<img src="/Public/Home/images/ydw_gy.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_gs.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_wl.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_kq.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_wzf.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_yl.png" />
					</a>
					<a href="#">
						<img src="/Public/Home/images/ydw_360.png" />
					</a>
				</div>
				<!-- 1、超链接 -->
				<div class="links">
					<a href="/about">关于我们</a>|
					<!--<a href="#">联系我们</a>|-->
					<a href="#">欢迎提供网上买药服务的正规合法药店入驻</a>|
					<a href="#">互联网药品信息服务资格证：（粤）-经营性-2015-0032</a>
				</div>
				<!-- 2、版权信息 -->
				<div class="copyright">
					&copy;2016
					<a href="#">360haoyao.com</a>
					<a href="#">360健康</a>是360旗下医药电商平台 粤ICP备15088592号
				</div>
			</div>
		</div>
		
	<script src="/Public/Home/index/js/swiper.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="/Public/Home/index/js/index.js" type="text/javascript" charset="utf-8"></script>
	<script src="/Public/Home/js/placeholder.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript">
		//药材分类数据	
		var urlObj = {
			login: "<?php echo U('Api/User/login');?>",
			userCenter: "<?php echo U('user/profile');?>",
			userlogin: "<?php echo U('login/index');?>?e=1"
		};

		//		$('.index-close').css('display','none');
		$(function() {
			//关于供货或者求购的图片放大的效果
			$('.img').each(function() {
				var imgs = $(this).find('img');
				var w = imgs.width();
				var h = imgs.height();
				imgs.mouseover(function() {
					$(this).animate({
						'width': '170px',
						'height': '210px',
						'margin-left': '-10px'
					}, 350);
				}).mouseout(function() {
					$(this).animate({
						'width': w,
						'height': h,
						'margin-left': 0
					}, 350);
				})
			});
		});
		//关于物流的‘公司简介’的弹窗的设置
		/*$('.log-hover span').mouseover(function(){
			$(this).next('.brief-visiable').show();
		}).mouseout(function(){
			$(this).next('.brief-visiable').hide();
		});
		$('.log-container li:lt(3)').each(function(){
			var a = $(this).text();
			if(a.length>9){
				$(this).text($(this).text().slice(0,9));
			}
		});*/
		//关于求购的文字超出部分的处理
		/*$('.demand-hover li:nth-child(4)').each(function(){
			var a = $(this).text();
			if(a.length>7){
				$(this).text($(this).text().slice(0,7));
			}
		});*/
	</script>
	<script type="text/javascript">
		var objLength = catObj.length;
		//把1.2  3 4 5以上长度的字 分别放在4个数组
		for(var i = 0; i < objLength; i++) {
			var arr = [];
			for(var j = 0; j < 4; j++) {
				var newArr = [];
				arr.push(newArr);
			}
			for(var j = 0; j < catObj[i].c.length; j++) {
				var cgLength = catObj[i].c[j].cg.length
				if(cgLength <= 2) {
					arr[0].push(catObj[i].c[j]);
				} else if(cgLength == 3) {
					arr[1].push(catObj[i].c[j]);
				} else if(cgLength == 4) {
					arr[2].push(catObj[i].c[j]);

				} else if(cgLength >= 5) {
					arr[3].push(catObj[i].c[j]);
				}
			}
			catObj[i].c = arr;
		}

		for(var i = 0; i < objLength; i++) {
			var html = '';
			var rm = '';
			if(catObj[i].hot != null) {
				rm = remen(catObj[i].hot);
			}
			var title = catObj[i].t,
				id = catObj[i].i;
			var li = '<li><span class="class-span">' + title + '</span><span class="nav-icon"></span><ul class="drug-list clearfix"></ul></li>';
			$(".drug-class").append(li);
			for(var j = 0; j < catObj[i].c.length; j++) {
				if(j == 0) {
					for(var c = 0; c < catObj[i].c[j].length; c++) {
						var ci = catObj[i].c[j][c].ci,
							cg = catObj[i].c[j][c].cg;
						if(c == 0) {
							html += '<li style="clear:both"><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						} else {
							html += '<li><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						}
					}
				} else if(j == 1) {
					for(var c = 0; c < catObj[i].c[j].length; c++) {
						var ci = catObj[i].c[j][c].ci,
							cg = catObj[i].c[j][c].cg;
						if(c == 0) {
							html += '<li style="width:635px;border-top:0.5px dashed #ccc;"></li><li style="clear:both"><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						} else {
							html += '<li><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						}
					}
				} else if(j == 2) {
					for(var c = 0; c < catObj[i].c[j].length; c++) {
						var ci = catObj[i].c[j][c].ci,
							cg = catObj[i].c[j][c].cg;
						if(c == 0) {
							html += '<li style="width:635px;border-top:0.5px dashed #ccc;"></li><li style="clear:both"><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';

						} else {
							html += '<li><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						}
					}
				} else if(j == 3) {
					for(var c = 0; c < catObj[i].c[j].length; c++) {
						var ci = catObj[i].c[j][c].ci,
							cg = catObj[i].c[j][c].cg;
						if(c == 0) {
							html += '<li style="width:635px;border-top:0.5px dashed #ccc;"></li><li style="clear:both"><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						} else {
							html += '<li><a href="/goods/detail/id/' + ci + '.html">' + cg + '</a></li>';
						}
					}
				}
			}
			$(".drug-class>li").eq(i).find(".drug-list").html(rm + html);
		}
		function remen(array) {
			var great = '';
			great = '<li class="remen-title">热门：</li>';
			for(var i = 0, len = array.length; i < len; i++) {
				great += '<li class="remen"><a href="/goods/detail/id/' + array[i].id + '.html">' + array[i].goods_name + '</a></li>';
			}
			great += '<li style="width:635px;border-top:0.5px dashed #ccc;"></li>';
			return great;
		}
	</script>

		<script src="/Public/Home/js/head.js"></script>
		<script type="text/javascript">
			$(function() {
				//搜索建议提示
				var suggestframe = $('.search-suggest');
				var inputvalue = $('#search-form .setext');
				var subinput = $('#sub');
				var sug_value = inputvalue.val();
				var indexli = 0;
				var reg = /\s*/g;
				var val_length = 0;
				var _val = inputvalue.val();
				inputvalue.focus(function() {
					sug_value = $(this).val();
					if(subinput.val()) {
						subinput.val('');
					}
				}).keyup(function(evt) {
					sug_value = $.trim($(this).val());
					sug_value = sug_value.split(reg).join("");
					evt = (evt) ? evt : ((window.event) ? window.event : ""); //兼容IE和Firefox获得keyBoardEvent对象
					var key = evt.keyCode ? evt.keyCode : evt.which; //兼容IE和Firefox获得keyBoardEvent对象的键值
					var li_list = suggestframe.find('li');
					var len = li_list.length;
					if(key==38){
						if(typeof this.selectionStart == 'number' &&
							typeof this.selectionEnd == 'number') {
							this.selectionStart = this.selectionEnd = this.value.length;
						}
					}
					if(len) {
						if(key == 40 || key == 38) {
							return false;
						}
					}
					/*
					 * if(sug_value.length!=val_length){
						val_length=sug_value.length;
						if(val_length==0){
							suggestframe.html('');
							return false;
						}*/
					if(_val != sug_value) {
						_val = sug_value;
						if(!sug_value) {
							suggestframe.html('');
							return false;
						}
						$.ajax({
							url: "<?php echo U('Goods/ajaxgetgoods');?>",
							type: 'GET',
							data: {
								goods_name: sug_value
							},
							success: function(req) {
								if(req == null) {
									return;
								}
								var len = req.length;
								for(var i = 0, str = ''; i < len; i++) {
									if(!req[i].alias_name) {
										str += '<li>' + req[i].goods_name + '</li>';
									} else {
										str += '<li>' + req[i].alias_name + '<span>（' + req[i].goods_name + '）</span>' + '</li>';
									}
								}
								suggestframe.html(str);
								suggestframe.show();
							},
							error: function() {
								$.custom('网络连接超时，请重试！');
							}
						});
					}
				}).blur(function() {
					var sug_values = '';
					var indexof = 0,
						sub_value = '';
					sug_value = $.trim(sug_value);
					sug_value = sug_value.split(reg).join('');
					indexof = sug_value.indexOf('（');
					if(indexof != -1) {
						sug_values = sug_value.slice(0, indexof);
						sub_value = sug_value.slice(indexof + 1, -1);
						subinput.val(sub_value);
						indexli == -1 ? inputvalue.val(this.value) : inputvalue.val(sug_values);
					} else {
						indexli == -1 ? inputvalue.val(this.value) : inputvalue.val(sug_value);
					}
					suggestframe.html('');
				});
				suggestframe.on('mouseover', 'li', function() {
					var _this = $(this);
					sug_value = _this.text();
					_this.parent().find('li').removeClass('active');
					_this.addClass('active');
					indexli = _this.index();
					i = 0;
				}).on('mouseout', 'li', function() {
					var _this = $(this);
					sug_value = inputvalue.val();
					_this.parent().find('li').removeClass('active');
					indexli = -1;
				});
				suggestframe.mouseout(function() {
					indexli = -1;
				});
			});
			/*关于keyCode=38/40时的操作*/
			var i = 0;

			function wag(evt) {
				evt = (evt) ? evt : ((window.event) ? window.event : ""); //兼容IE和Firefox获得keyBoardEvent对象
				var key = evt.keyCode ? evt.keyCode : evt.which; //兼容IE和Firefox获得keyBoardEvent对象的键值
				var suggestframe = $('.search-suggest');
				var inputvalue = $('#search-form .setext');
				var sug_value = inputvalue.val();
				var li_list = suggestframe.find('li');
				var len = li_list.length;
				if(key == 40) {
					if(len == 0) {
						inputvalue.val(sug_value);
					} else {
						i++;
						i > len && (i = 1);
						suggestframe.show();
						li_list.removeClass('active');
						var li = suggestframe.find('li:nth-child(' + i + ')');
						li.addClass('active');
						inputvalue.val(li.text());
						return false;
					}
				} else if(key == 38) {
					if(len == 0) {
						inputvalue.val(sug_value);
					} else {
						i--;
						i < 1 && (i = len);
						suggestframe.show();
						li_list.removeClass('active');
						var li = suggestframe.find('li:nth-child(' + i + ')');
						li.addClass('active');
						inputvalue.val(li.text());
						return false;
					}
				}
			}
			/* 提交表单 */
			function search() {
				var s = $('#q');
				var sub = $('#sub');
				var first = $.trim(s.val());
				var reg = /\s*/g;
				first = first.split(reg).join('');
				if(first == "") {
					return;
				}
				if(first.length > 40) {
					first = first.substring(0, 40);
					s.val(first);
				}
				var ind = first.indexOf('（');
				if(ind != -1) {
					s.val($.trim(first.substring(0, ind)));
					var subvalue = $.trim(first.substring(ind + 1, first.length - 1));
					if((ind + 1) < (first.length - 1)) {
						document.getElementById('sub').value = subvalue;
					} else {
						sub.remove();
					}
				} else {
					s.val(first);
					if(sub.val() == "") {
						sub.remove();
					}
				}
				$("#search-form").submit();
				return false;
			}
		</script>
	</body>

</html>