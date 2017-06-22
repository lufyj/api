$(function() {
	var find_search = $('.find_search');
	/* 显示隐藏搜索下拉框 */
	find_search.mouseover(function() {
		$(this).find('.search_small').show();
	}).mouseout(function() {
		$(this).find('.search_small').hide();
	});
	/* 下拉框赋值 */
	find_search.find('ul a').on('click', function() {
		find_search.find('input.serclass').val($(this).text());
		$("#search-form").attr('action', this.rel);
		find_search.find('.search_small').hide();
	});
	/**我的药都网下拉修改版**/
	$('.ydw_nav .pull_down').mouseover(function() {
		var _this = $(this);
		_this.children('a').addClass('hover').css('color', '#ff0000');
		_this.find('b').css('background', 'url(/Public/Home/images/pull_up_icon.png) no-repeat');
		_this.children('.nav_mycenter').css('display', 'block');
	}).mouseleave(function() {
		var _this = $(this);
		_this.children('a').removeClass('hover').css('color', '#666');
		_this.find('b').css('background', 'url(/Public/Home/images/pull_down_icon.png) no-repeat');
		_this.children('.nav_mycenter').css('display', 'none');
	});
	//关于替换ios和android的二维码
	$('.ercode').find('p:nth-child(2)').on('click','a',function(){
		var i = $(this).index();
		$(this).parent().prev().find('img').attr('src','/Public/Home/images/ydw_app'+(i+1)+'.png');
	});
});
/*点击搜索框的热点放在input框中*/
$('.search_middle_bottom').on('click', 'a', function(e) {
	if(e.preventDefault !== undefined) {
		e.preventDefault();
	} else {
		e.returnValue = false;
	}
	var txt = $.trim($(this).text());
	if(txt == '') {
		return;
	} else {
		$('.setext').val(txt);
	}
});
//关于自定义alert插件
jQuery.custom = function() {
	var str = "<div class='modal modal-close'><div class='modal-dialog'><div class='modal-content'>" +
		"<i class='custom-close'>&times;</i>" +
		"<p>" + (arguments[1] ? arguments[0] : '药都网温馨提示您') + "</p>" +
		"<div class='modal-context'>" + (arguments[1] || arguments[0]) + "</div>" +
		"<div class='context-a'>" +
		"<button class='custom-ok'>确定</button>" +
		"</div></div></div></div>";
	$('body').append(str);
	$('i.custom-close,button.custom-ok').click(function() {
		$('.modal-close').remove();
	});
}