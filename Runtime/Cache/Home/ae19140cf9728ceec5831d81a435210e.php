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
		
	<link rel="stylesheet" type="text/css" href="/Public/Home/dlDS/css/titleDSG.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Home/dlDS/css/dl.css" />
	<script type="text/javascript">
		(function() {
			window.cache = {
				c: []
			};
		})();
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
		
	<div class="demand-detail">
	<div class="detail-top">
		<ul class="detail-nav clearfix">
			<li>
				<a href="/">首页</a>
			</li>
			<li>
				<a href="<?php echo U('supply/lst');?>" <?php if($act == sup): ?>class="active"<?php endif; ?>>供应信息</a>
			</li>
			<li>
				<a href="<?php echo U('demand/lst');?>" <?php if($act == dem): ?>class="active"<?php endif; ?>>求购信息</a>
			</li>
			<li>
				<a href="<?php echo U('goods/market');?>" <?php if($act == mar): ?>class="active"<?php endif; ?>>药材行情</a>
			</li>
			<!--<li>-->
				<!--<a href="<?php echo U('companyShow/storeList');?>" <?php if($act == 'store'): ?>class="active"<?php endif; ?>>仓库信息</a>-->
			<!--</li>-->
			<!--<li>-->
				<!--<a href="<?php echo U('companyShow/deliveryList');?>" <?php if($act == 'del'): ?>class="active"<?php endif; ?>>物流信息</a>-->
			<!--</li>-->
			<li>
				<a href="<?php echo U('CompanyShow/mergeList');?>" <?php if($all == 'all'): ?>class="active"<?php endif; ?>>加工/检测</a>
			</li>
		</ul>
	</div>
</div>
	<div class="detail-middle">
		<div class="detail-bread">
			<i></i>
			<b>当前位置：</b>&nbsp;
			<a href="/">首页</a>&nbsp;&gt;&nbsp;
			<a href="<?php echo U('supply/lst');?>">供应信息</a>
			<?php if($q): ?>&nbsp;&gt;&nbsp;
				<a href=""><?php echo ($q); ?></a><?php endif; ?>
			<?php if($alias_name): ?><a href="">(<?php echo ($alias_name); ?>)</a><?php endif; ?>
		</div>
		<div class="drugs-top" id="cate_nav">
			<?php if(!empty($goods_name)): ?><h2><i></i>已选择：<?php echo ((isset($cate_name) && ($cate_name !== ""))?($cate_name):''); ?>&nbsp;--&gt;&nbsp;<?php echo ((isset($goods_name) && ($goods_name !== ""))?($goods_name):''); ?></h2><?php endif; ?>
			<h2>请选择类别：</h2>
			<ul class="drugs-title clearfix">
				<?php if(!empty($_GET['cate_id'])): ?><li onclick="rload();">全部</li>
					<?php else: ?>
					<li onclick="rload();" class="active first">全部</li><?php endif; ?>
				<?php if(is_array($cates)): foreach($cates as $k=>$vo): ?><li <?php if(I( 'get.cate_id')==$vo[ 'id']){echo 'class="active"';} ?> onclick="connector(<?php echo ($vo["id"]); ?>)"><?php echo ($vo["title"]); ?></li><?php endforeach; endif; ?>
			</ul>
			<div class="drugs-name"></div>
			<?php if(is_array($cates)): foreach($cates as $k=>$vo): ?><div class="drugs-name" id="c-<?php echo ($vo["id"]); ?>" <?php if(I( 'get.cate_id')==$vo[ 'id']){echo 'style="display:block;"';}else{echo 'style="display:none;"';} ?> ></div><?php endforeach; endif; ?>
		</div>
		<div class="drugs-main clearfix">
			<?php foreach($list as $k => $v):?>
			<div class="competes clearfix l">
				<div class="competes-img l">
					<a href="<?php echo U('supply/detail',array('id'=>$v['id']));?>">
						<img src="<?php echo ((isset($v["imgs"]) && ($v["imgs"] !== ""))?($v["imgs"]):'/Public/Home/images/noimg.png'); ?>" />
					</a>
				</div>
				<div class="competes-detail l">
					<ul class="clearfix">
						<li>商品名称：
							<?php echo $v['goods_name'] ?>
						</li>
						<li>商品规格：
							<?php echo $v['goods_attr_name'] ?>
						</li>
						<li>供应数量：
							<!--<?php echo $v['num'] ?>-->
							<?php if($v['num']==-1){echo '大货';}else if(is_numeric($v['num']) && $v['num']!=-1){echo $v['num'].'公斤';}else{echo $v['num'];} ?>
						</li>
						<li class="cd-color">联&nbsp;&nbsp;系&nbsp;&nbsp;人：
							<?php echo $v['contacts'] ?>
						</li>
						<li class="cd-color">联系电话：
							<?php echo $v['mobile'] ?>
						</li>
						<li class="cd-color">发布时间：
							<?php echo date('Y-m-d', $v['create_time']); ?>
						</li>
					</ul>
				</div>
			</div>
			<?php endforeach;?>
		</div>
		<?php echo ($pageHtml); ?>
	</div>

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
		
	<!--脚本自定义开始-->
	<!-- <script src="/Public/Home/user/js/user.js" type="text/javascript" charset="utf-8"></script> -->
	<script type="text/javascript">
		$(function() {
				//设置competes的右外边距
				$('.competes').each(function(i) {
					if(i % 2 == 0) {
						$(this).css('margin-right', '1.2%');
					}
				});
				//获取分类导航对象
				var cate_nav = $('#cate_nav');
				cate_nav.find('ul>li').on('click', function() {
					var inx = $(this).index();
					$(this).addClass('active').siblings().removeClass();
					if(this.innerHTML == '全部') {
						$(this).addClass('first');
					} else {
						$(this).removeClass('first');
					}
					if(inx != 0) {
						cate_nav.find('div.drugs-name').eq(inx).show().siblings('div').hide();
					} else {
						cate_nav.find('div.drugs-name').hide();
					}
				});
				//绑定初始化药品分类
				var cate_id = "<?php echo ($_GET['cate_id']); ?>" || '';
				if(parseInt(cate_id) > 0) {
					connector(cate_id);
				}
			})
			/* 加载求购列表页 */
		function rload() {
			window.location.href = "<?php echo U('supply/lst');?>";
		}
		var gid = <?php echo ((isset($_GET['id']) && ($_GET['id'] !== ""))?($_GET['id']):0); ?> ;
		/*关于热门药材*/
		function remen(cateid,array){
			var great='';
			great='<div class="remen"><span>热门：</span>';
			for(var i=0,len=array.length;i<len;i++){
				great+='<a href="/supply/lst/cate_id/'+cateid+'/id/'+array[i].id+'.html">'+array[i].goods_name+'</a>';
			}
			great+='</div>';
			return great;	
		}
		/* 异步调取药品方法 */
		function connector(cate_id) {
			if(window.cache.c[cate_id]) {
				return;
			}
			var wrap_id = '#c-' + cate_id;
			$.ajax({
				url: "<?php echo U('Demand/ajaxGetGoodsByCateId');?>",
				dataType: "json",
				type: "get",
				data: { cate_id: cate_id, id:gid, type: 1 },
				beforeSend: function() {
					$(wrap_id).html('<img class="loadingimg" src="/Public/Home/images/loading.gif" />');
				},
				success: function(req) {
					var datas = req.data.goods,
						hot = req.data.hot,
						datasL = datas.length,
						max = datas[0].goods_name.length,
						arr = [];
					for(var i = 0; i < 4; i++) {
						var newArr = [];
						arr.push(newArr);
					}

					for(var i = 0; i < datasL; i++) {
						if(datas[i].goods_name.length <= 2) {
							arr[0].push(datas[i]);
						} else if(datas[i].goods_name.length == 3) {
							arr[1].push(datas[i]);
						} else if(datas[i].goods_name.length == 4) {
							arr[2].push(datas[i]);
						} else if(datas[i].goods_name.length >= 5) {
							arr[3].push(datas[i]);
						}
					}
					var str = '';
					if(hot!=null){
						str = remen(cate_id,req.data.hot);
					}
					for(var i = 0; i < arr.length; i++) {
						var classS = "";
						if(i == 0) {
							for(var j = 0; j < arr[i].length; j++) {
								if(gid == arr[i][j].id){
									classS = " class='active' ";
								}else{
									classS="";
								}
								if(j == arr[i].length - 1) {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a><br/>";
								} else {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a>";
								}

							}
						} else if(i == 1) {
							for(var j = 0; j < arr[i].length; j++) {
								if(gid == arr[i][j].id){
									classS = " class='active' ";
								}else{
									classS="";
								}
								if(j == arr[i].length - 1) {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a><br/>";
								} else {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a>";
								}
							}
						} else if(i == 2) {
							for(var j = 0; j < arr[i].length; j++) {
								if(gid == arr[i][j].id){
									classS = " class='active' ";
								}else{
									classS="";
								}
								if(j == arr[i].length - 1) {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a><br/>";
								} else {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a>";
								}
							}
						} else if(i == 3) {
							for(var j = 0; j < arr[i].length; j++) {
								if(gid == arr[i][j].id){
									classS = " class='active' ";
								}else{
									classS="";
								}
								if(j == arr[i].length - 1) {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a><br/>";
								} else {
									str += "<a href='/supply/lst/cate_id/" + cate_id + "/id/" + arr[i][j].id + ".html'"+ classS +">" + arr[i][j].goods_name + "</a>";
								}
							}
						}
					}
					$(wrap_id).html(str);
					window.cache.c[cate_id] = true;
				},
				error: function() {
					$.custom('网络连接超时，请您稍后重试');
				}
			});
		}
	</script>
	<!--脚本自定义结束-->

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