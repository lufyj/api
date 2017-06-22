$(function (){
    //关于用户中心中标签页的切换
    $('.user-manage .navs li').each(function(){
    	var index = $(this).index();
    	$(this).click(function(){
    		if(index >= 0 && index <= 2){
    			$(this).addClass('show').siblings().removeClass('show');
    			$($('.user-manage .msg02').get(index)).show().siblings(':not(.navs)').hide();
    		}
    	});
    });
});