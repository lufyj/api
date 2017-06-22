$(function() {
	//第二版魔板样式跳转
	$('.title2-b dl').mouseover(function() {
		var imgs = $(this);
		switch(imgs.index()) {
			case 0:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav11.png" alt="" />');
				imgs.find('dd a').css('color', '#A88779');
				break;
			case 1:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav22.png" alt="" />');
				imgs.find('dd a').css('color', '#A88779');
				break;
			case 2:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav33.png" alt="" />');
				imgs.find('dd a').css('color', '#A88779');
				break;
			case 3:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav44.png" alt="" />');
				imgs.find('dd a').css('color', '#A88779');
				break;
			case 4:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav55.png" alt="" />');
				imgs.find('dd a').css('color', '#A88779');
				break;
		}
	}).mouseout(function() {
		var imgs = $(this);
		switch(imgs.index()) {
			case 0:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav01.png" alt="" />');
				imgs.find('dd a').css('color', '#666');
				break;
			case 1:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav02.png" alt="" />');
				imgs.find('dd a').css('color', '#666');
				break;
			case 2:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav03.png" alt="" />');
				imgs.find('dd a').css('color', '#666');
				break;
			case 3:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav04.png" alt="" />');
				imgs.find('dd a').css('color', '#666');
				break;
			case 4:
				imgs.find('dt').html('<img src="__TMPL__/simple/images2/nav05.png" alt="" />');
				imgs.find('dd a').css('color', '#666');
				break;
		}
	});
	//固定导航
	var ta = $('.title2-b dd a').offset().top;
	$(window).scroll(function() {
		if($(window).scrollTop() >= ta) {
			$('.fix_nav').show();
		} else {
			$('.fix_nav').hide();
		}
	});
});