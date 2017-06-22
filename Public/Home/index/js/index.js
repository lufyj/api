/* 点击回车提交 */
function keyDown(evt) {
	evt = (evt) ? evt : ((window.event) ? window.event : ""); //兼容IE和Firefox获得keyBoardEvent对象
	var key = evt.keyCode ? evt.keyCode : evt.which; //兼容IE和Firefox获得keyBoardEvent对象的键值
	if(key == 13) {
		$('#submit_btn').trigger('click');
	}
}
$(function() {
	function jizhumima(html,bg,tishi){
		tishi.html(html);
		tishi.css({
			'padding-left':'16px',
			'background':'url(/Public/Home/images/icons.png) no-repeat -104px -23px'
		});
		bg.css({
			'background-color':'#fff6d2',
			'border-color':'sandybrown'
		});
	}
	//记住我点击
	$(".remb span").click(function() {
		//$(".is-remb").toggleClass("show");
		var jizhu = $('.is-remb'),
			bg    = $('.verify'),
		    tishi = $('.verify span');
		jizhu.toggleClass('show');
		if(!jizhu.hasClass('show')){
			jizhumima('药都网欢迎您！',bg,tishi);
		}else{
			jizhumima('公共场所不建议自动登录，以防账号丢失',bg,tishi);
		}
	});
	/**********************开始---登录**********************/
	function tishicontent(html,bg,tishi){
		tishi.html(html);
		tishi.css({
			'padding-left':'22px',
			'background':'url(/Public/Home/images/icons.png) no-repeat -104px -48px'
		});
		bg.css({
			'background-color':'#ffebeb',
			'border-color':'#faccc6'
		});
	}
	//登录事件
	$('#submit_btn').on('click', function() {
		var bg     = $('.verify'),
			tishi  = $('.verify span');
		var mobile = $.trim($('#loginname').val());
		if(!mobile ||　!/^1[34578]\d{9}$/.test(mobile)) {
			//$.custom('请输入您的手机号');
			tishicontent('请输入您正确的手机号',bg,tishi);
			return;
		}
		var password = $.trim($('#loginpwd').val());
		if(!password) {
			//$.custom('请输入您的密码');
			tishicontent('您的密码不正确',bg,tishi);
			return;
		}
		//提交数据
		$.ajax({
			url: urlObj.login, //请求的url地址		    		        
			type: "post", //请求方式
			dataType: "json", //返回格式为json
			data: {
				mobile: mobile,
				password: password,
			},
			beforeSend: function() {
				//请求前的处理
			},
			success: function(req) {
				//请求成功时处理
				if(req.code == 1) {
					window.location.href = urlObj.userCenter;
				}
				if(req.code == -1) {
					//$.custom('账号密码错误,请重新登录');
					tishicontent('账号密码错误,请重新登录',bg,tishi);
					window.location.href = urlObj.userlogin;
				}
			},
			complete: function() {
				//请求完成的处理
			},
			error: function() {
				//请求出错处理
				$.custom('网络超时，请稍后再试');
			}
		});
	});
	/**********************结束---登录**********************/

	/**********************开始---轮播**********************/
	var mySwiper1 = new Swiper('.banner', {
		autoplay: 3000,
		pagination: '.pagination',
		loop: true,
		speed: 1500,
		paginationClickable: true,
		autoplayDisableOnInteraction: false
	});
	var mySwiper2 = new Swiper('.serve-wp', {
		loop: true,
		speed: 1000,
		paginationClickable: true,
		autoplayDisableOnInteraction: false
	});
	$('.arrow-left1').on('click', function(e) {
		e.preventDefault()
		mySwiper2.swipePrev()
	});
	$('.arrow-right1').on('click', function(e) {
		e.preventDefault()
		mySwiper2.swipeNext();
	});
	$(".serve-wp li").hover(function() {
		$(this).children("div").stop().animate({
			"top": "0"
		})
	}, function() {
		$(this).children("div").stop().animate({
			"top": "100%"
		})
	});
	//导航点击事件
	$(".nav-list li").click(function() {
		var self = $(this);
		$(".nav-list li").removeClass("current");
		self.addClass("current");
	});
	//分类列表hover事件
	$(".drug-class>li").hover(function() {
		$(".drug-class>li").children("ul").hide();
		$(this).children("ul").show();
	}, function() {
		$(this).children("ul").hide();
	});
});