$(function() {
	//样式预览/使用按钮显示
	$(".choose-style li").hover(function() {
		$(this).children(".choose").show();
	}, function() {
		$(this).children(".choose").hide();
	});

	//业务列表hover事件
	$(".bsns-ul ul").each(function() {
		var self = $(this);
		self.children("li:gt(0)").hover(function() {
			var self = $(this);
			if(self.attr("data-id")) {
				self.css({
					'border': ' 1px solid #ffb040',
					"background-color": "#fff7ec"
				})
				self.prev("li").css({
					'border-bottom': 'none'
				})

				self.append('<div class="op r"><span class="edit-s"><b></b> 编辑</span><span class="clear-s"><b></b> 删除</span></div>')
			}
		}, function() {
			var self = $(this);
			if(self.attr("data-id")) {
				self.css({
					'border': '1px solid #d6d7dc',
					"border-top": "none",
					"background-color": "#fff"
				})
				self.prev("li").css({
					'border-bottom': '1px solid #d6d7dc'
				})
				self.find(".op").remove();
			}
		});
	});
	var empty = $(".empty"),
		alertDiv = $(".alert-wrap>div");
	function alertDivShow(index){
		empty.show();
		alertDiv.hide();
		alertDiv.eq(index).show();
	}
	//点击添加按钮
	$(".add .add_span").each(function(index) {
		var self = $(this);
		var i = index;
		self.on("click" ,function (){
			alertDivShow(i);
			if( i== 0){
				getZone('st_provice');
			}else if(i == 1){
				getZone('de_begin_provice');
				getZone('de_end_provice');
			}
		});
	});

	//动态获取省市区(jingwei)
	function getZone(business) {
		$.get("/Region/index", {
			parent: 0
		}, function(data) {
			var html = '<option value="" selected="selected">请选择省</option>';
			var l = data.length;
			for(var i = 0; i < l; i++) {
				var pro = data[i].area;
				var code = data[i].code;
				var op = '<option value="' + code + '">' + pro + '</option>';
				html += op;
			}
			$("#" + business).html(html);
		});

	}
});