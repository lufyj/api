$(function(){
	$(".one-l").click(function (){
		var self = $(this);
		//$(".two-l").hide();
		self.next().show();
		self.parent().siblings().children('.two-l').hide();
		$(".one-l").css({
			"color":"#000"
		}).find("span").css({
			"border-top-color":"#000"
		});
		self.css({
			"color":"#E62B2E"
		}).find("span").css({
			"border-top-color":"#E62B2E"
		});
	});
});

