! function(e) {
	function t(n) {
		if(i[n]) return i[n].exports;
		var o = i[n] = {
			exports: {},
			id: n,
			loaded: !1
		};
		return e[n].call(o.exports, o, o.exports, t), o.loaded = !0, o.exports
	}
	var i = {};
	return t.m = e, t.c = i, t.p = "", t(0)
}([function(e, t) {
	"use strict";
	! function() {
		function e() {
			var e = 100,
				t = window,
				i = t.document,
				n = navigator.userAgent,
				o = n.match(/Android[\S\s]+AppleWebkit\/(\d{3})/i),
				a = n.match(/U3\/((\d+|\.){5,})/i),
				r = a && parseInt(a[1].split(".").join(""), 10) >= 80,
				c = navigator.appVersion.match(/(iphone|ipad|ipod)/gi),
				d = t.devicePixelRatio || 1;
			c || o && o[1] > 534 || r || (d = 1);
			var l = 1 / d,
				p = i.querySelector('meta[name="viewport"]');
			p || (p = i.createElement("meta"), p.setAttribute("name", "viewport"), i.head.appendChild(p)), p.setAttribute("content", "width=device-width,user-scalable=no,initial-scale=" + l + ",maximum-scale=" + l + ",minimum-scale=" + l), i.documentElement.style.fontSize = e * (i.documentElement.clientWidth / 640) + "px", window.viewportScale = d
		}
		e(), window.onresize = function() {
			e()
		}
	}()
}]);