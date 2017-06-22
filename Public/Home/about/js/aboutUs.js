$('.aboutUs-list li').click(function(){
	$(this).find('a').addClass('active');
	$(this).siblings().find('a').removeClass('active');
});
