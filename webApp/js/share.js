var ydwshare = {
	iphoneSchema: 'yaoduwang://',
	iphoneDownUrl: 'https://itunes.apple.com/us/app/%E8%8D%AF%E9%83%BD%E7%BD%91/id1193133333?l=zh&ls=1&mt=8',
	androidSchema: 'yaoduwang://',
	androidDownUrl: 'http://a.app.qq.com/o/simple.jsp?pkgname=com.yaoduphone',
	iphoneOrandroid: '',
	sPlat: '',
	openApp: function() {
		var this_ = this;
		var sPlat = this_.sPlat;
		if(sPlat == 'WX') {
			window.location.href = "http://a.app.qq.com/o/simple.jsp?pkgname=com.yaoduphone";
		} else {
			if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)) {
				if(sPlat == 'QQ') {
					setTimeout(function() {
						window.location.href = this_.iphoneDownUrl;
					}, 1500);
				}else{
					//苹果手机上的浏览器
					window.location.href = this_.iphoneDownUrl;
				}
			} else if(navigator.userAgent.match(/android/i)) {
				this_.isandroid(this_.androidSchema, this_.androidDownUrl, function(opened, url) {
					if(opened == 0) {
						window.location = url;
					}
				});
			}
		}
	},
	isandroid: function(openUrl, appUrl, callback) {
		//检查app是否打开
		function checkOpen(cb) {
			var _clickTime = +(new Date());

			function check(elsTime) {
				if(elsTime > 3000 || document.hidden || document.webkitHidden) {
					cb(1);
				} else {
					cb(0);
				}
			}
			//启动间隔20ms运行的定时器，并检测累计消耗时间是否超过3000ms，超过则结束
			var _count = 0,
				intHandle;
			intHandle = setInterval(function() {
				_count++;
				var elsTime = +(new Date()) - _clickTime;
				if(_count >= 100 || elsTime > 1000) {
					clearInterval(intHandle);
					check(elsTime);
				}
			}, 20);
		}
		//在iframe 中打开APP
		var ifr = document.createElement('iframe');
		ifr.src = openUrl;
		ifr.style.display = 'none';
		if(callback) {
			checkOpen(function(opened) {
				callback && callback(opened, appUrl);
			});
		}
		document.body.appendChild(ifr);
		setTimeout(function() {
			document.body.removeChild(ifr);
		}, 2000);
	}
}
ydwshare.CheckPlat = function() {
	//QQ
	if(new RegExp(' QQ').test(navigator.userAgent)) {
		ydwshare.sPlat = "QQ";
		return "QQ";
	}
	//微信
	if(navigator.userAgent.match("MicroMessenger")) {
		ydwshare.sPlat = "WX";
		return "WX";
	}
}
ydwshare.CheckPlat();
if (ydwshare.sPlat == "QQ" && navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)){
	$(".download_btn").attr("href", ydwshare.iphoneSchema);
}
$(".download_btn").click(function (){
	ydwshare.openApp();
});
//if(ydwshare.iphoneOrandroid == "android" && ydwshare.sPlat != "WX") {
//	$(".download_btn").attr("href", ydwshare.androidDownUrl);
//} else if(ydwshare.iphoneOrandroid == "iphone" && ydwshare.sPlat != "WX") {
//	$(".download_btn").attr("href", ydwshare.iphoneDownUrl);
//} else {
//	$(".download_btn").attr("href", 'http://a.app.qq.com/o/simple.jsp?pkgname=com.yaoduphone');
//}


function GetRequest() {
	var url = location.search; //获取url中"?"符后的字串
	var theRequest = new Object();
	if(url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i++) {
			theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}

function line(obj) {
	obj.each(function() {
		var _this = $(this);
		var li_height = _this.height();
		_this.find(".line").css({
			'height': li_height + 'px'
		})
	});
}

$(".img_div").on("click", "img", function() {
	var i = $(this).index();
	$(".modal").show();
	$("body").css({
		"height": '100%',
		'overflow': 'auto'
	})
	$("html").css({
		"height": '100%',
		'overflow': 'auto'
	})
	if(i == 0) {
		$(".swiper-wrapper").css({
			'transform': 'translate3d(0px, 0px, 0px)'
		})
	}
	var swiper = new Swiper('.swiper-container', {
		pagination: '.swiper-pagination',
		initialSlide: i,
		spaceBetween: 40,
		autoHeight: true,
	});
});

$(".modal").click(function() {
	$(this).hide();
	$("body").css({
		"height": 'auto',
		'overflow': 'auto'
	})
	$("html").css({
		"height": 'auto',
		'overflow': 'auto'
	})
});