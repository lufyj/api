$(function() {
	var ifm = $("#iframepage");
	var ifmWrap = $(".iframe");
	var maskLayer = $(".mask-layer");
	checkHeight();
	$(window).resize(function() {
		checkHeight();
	});

	ifm.load(function (){
		var a = document.getElementById("iframepage").contentWindow.document.getElementsByTagName("a");
		var aL = a.length;
		for (var i = 0; i < aL; i++ ) {
			a[i].setAttribute("href" , "###");
			a[i].removeAttribute("target");
		}
	});

	//检测窗口高度函数 赋值给iframe
	function checkHeight() {
		var h = $(window).height();
		var height = h - 100;
		ifm.attr("height", height);
		ifmWrap.css({
			"height": height
		});
		maskLayer.css({
			"height": height
		});

	}
	//点击预览
	$(".choose-style .look").on("click", function() {
		var val = $(this).parents("li").find("input").val();
		ifm.attr("src", '/Company/viewPage.html?style=' + val);
		//出现预览弹框
		$(".look-wrap").show();
		//body 滚动条消失
		$("body").css({
			"overflow": "hidden"
		});
	});
	//关闭预览弹框
	$(".c-ifm").on("click", function() {
		$(".look-wrap").hide();
		//body 滚动条出现
		$("body").css({
			"overflow": "scroll"
		});
		ifm.attr("src", "")
	});
	//选中模板按钮
	$(".choose-style .use").on("click", function() {
		//给当前li增加class 
		var self = $(this);
		self.parents("li").addClass("checked").siblings("li").removeClass("checked");
	});
	
	//审核域名
	var domain_name = $(".domain_name");
	var warn_text = $(".warn_span1");
	$(".domain_name").focus(function (){
		warn_text.fadeOut();
		$(".warn_span2").fadeOut();
//		$(".warn_div").text("(仅允许由数字，字母组成，长度为4到18位。唯一标识，也是二级域名前缀，创建后不可修改。)");
	});
	$(".domain_sub").click(function (){
		var d = $.trim(domain_name.val().toLowerCase());
		var d_reg = /^[0-9a-zA-Z]{4,18}$/;
		if (!d){
			warn_text.fadeIn();
			warn_text.text('提示 : 请输入域名');
			return false;
		}else if (!d_reg.test(d)){
			warn_text.fadeIn();
			warn_text.text('提示 : 域名格式不正确');
			return false;
		}
		$.get('/CompanyConfirm/subDomain.html' , {d:d}, function (data){
			console.log(data);
			var code = data.code;
			if (code == 1){
				warn_text.fadeIn();
				warn_text.text('提示 : 用户未认证');
			}else if (code == 2){
				warn_text.fadeIn();
				warn_text.text('提示 : 域名审核中');
			}else if (code == 3){
				warn_text.fadeIn();
				warn_text.text('提示 : 域名不合法');
			}else if(code == 4){
				warn_text.fadeIn();
				warn_text.text('提示 : 提交成功,正在审核,页面即将跳转');
				var timer = setTimeout(function (){
					location.reload();
				} , 1000)
			}else if (code == 5){
				warn_text.fadeIn();
				warn_text.text('提示 : 提交失败');
			}else if(code == 6){
				warn_text.fadeIn();
				warn_text.text('提示 : 域名已被注册,请重新输入');
			}
		});
		
	});
});