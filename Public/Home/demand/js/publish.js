var submitForm = $('#submit_form'),
	selectCate = $('#select_cate'),
	formData = {};
$(function(){
	function showWarn(obj , msg){
		obj.html('<i></i>' + msg).fadeIn();
	}
	//warn 对象
	var warn_drug = $(".warn_drug"),
		warn_custom = $(".warn_custom"),
		warn_num = $(".warn_num"),
		warn_place = $(".warn_place"),
		warn_contacts = $(".warn_contacts"),
		warn_mobile = $(".warn_mobile"),
		warn_qq = $(".warn_qq"),
		warn_details = $(".warn_details");
	var num_input = $(".num_input"),
	qq_input = $("input[name='qq']"),
	contacts_input = $("input[name='contacts']"),
	mobile_input = $("input[name='mobile']"),
	details_input = $("textarea[name='details']"),
	custom_input = $("input[name=\'custom_name\']");
	//自定义
	custom_input.focus(function (){
		warn_custom.fadeOut();
	});
	//验证数量
	num_input.focus(function (){
		warn_num.fadeOut();
	});
	num_input.blur(function () {
		var  txt = $.trim($(this).val()),
			//reg = /^\d+(\.\d{1,2})?$/;
			reg = /^(\d{1,7})$/;
		if (txt){
			if (txt <= 0 || !reg.test(txt)){
				showWarn(warn_num , '请输入正确的数字');
				$(this).val("");
			}
		}
	});
	//省地县改变
	$("#prov_select").change(function (){
		//地址提示消失
		warn_place.fadeOut();
	});
	//联系人
	contacts_input.focus(function (){
		warn_contacts.fadeOut();
	});
	//电话
	mobile_input.focus(function (){
		warn_mobile.fadeOut();
	});
	//详情
	details_input.focus(function (){
		warn_details.fadeOut();
	});
	//验证QQ号是否正确
	qq_input.focus(function (){
		warn_qq.fadeOut();
	});
	init();
	var prov_select = $('#prov_select'), city_select = $('#city_select'), area_select = $('#area_select'), origin_area = '';
	//默认展开第一个药材面板
	selectCate.find('ul.classify-list > li:first').addClass("click");
	selectCate.find('ul.classify-list > li:first').find(".drug-list").show();
	/* 绑定下一步按钮 */
	$('#nextStep').on('click', function(){
		//从这里开始对表单中提交的数据进行判定
		formData = {};
		//判断是否选择了分类中的药材还是自定义了药材
		var customName = submitForm.find('input[name=\'custom_name\']').val();
		if($.trim(customName) != ''){
			formData['customName'] = customName; 
		}else{
			//这里判断是否选择了分类，药材
			var cid = parseInt(submitForm.find('input[name=\'cate_id\']').val());
			if(isNaN(cid) || cid <= 0){
				//$.custom('请选择药材分类');return;
				showWarn($('.warn_drug') , '请选择药材分类');return;
			}
			var gid = parseInt(submitForm.find('input[name=\'goods_id\']').val());
			if(isNaN(gid) || gid <= 0){
				//$.custom('请选择药材');return;	
				showWarn($('.warn_drug') , '请选择药材');return;
			}
			formData['cate_name'] = $.trim(submitForm.find('input[name=\'cate_name\']').val());
			formData['goods_name'] = $.trim(submitForm.find('input[name=\'goods_name\']').val());
			formData['goods_attr_name'] = $.trim(submitForm.find('input[name=\'goods_attr_name\']').val());
		}
		var num = $.trim(submitForm.find('input[name=\'num\']').val());
		if(num == '' || num == '请输入药材的数量'){
			//$.custom('请输入药材的数量');return;
			showWarn($('.warn_num') , '请输入药材的数量');return;
		}
		formData['num'] = num;
		var originType = submitForm.find('input:radio:checked').val();
		if(originType == 3){
			var pid = parseInt(prov_select.val());
			if(isNaN(pid) || pid <= 0){
				//$.custom('请选择地区');return;
				showWarn($('.warn_place') , '请选择地区');return;
			}
			formData['origin_area'] = origin_area;
			submitForm.find('input[name=\'origin_area\']').val(origin_area);
		}else{
			formData['origin_area'] = originType==1?'较广':'进口';
		}		
		var contacts = $.trim(submitForm.find('input[name=\'contacts\']').val());
		if(contacts == '' || contacts == '请输入联系人'){
			//$.custom('请输入联系人');return;
			showWarn($('.warn_contacts') , '请输入联系人');return;
		}
		formData['contacts'] = contacts;
		var mobile = submitForm.find('input[name=\'mobile\']').val();
		if(mobile == '' || mobile == '请输入您的手机号'){
			//$.custom('请输入您的手机号');return;
			showWarn($('.warn_mobile') , '请输入您的手机号');return;
		}
		if(!/^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/.test(mobile)){
			//$.custom('请输入正确的手机号');return;
			showWarn($('.warn_mobile') , '请输入正确的手机号');return;
		}
		//qq
		var qq = $.trim(qq_input.val());
		if (qq && !/^[1-9][0-9]{4,9}$/.test(qq)){
			showWarn($('.warn_qq') , '请输入正确的QQ号码(可不填)');return;
		}
		formData['mobile'] = mobile;
		var detail = $.trim($('#details').val());
		if(detail == ''){
			//$.custom('请输入详情介绍');return;
			showWarn($('.warn_details') , '请输入详情介绍');return;
		}
		formData['detail'] = detail;
		formData['qq'] = $.trim(submitForm.find('input[name=\'qq\']').val());
		assign();
		//显示下一步页面
		submitForm.css('display', 'none');
		$('#sup02').show();		
	});
	/* 显示上一步页面 */
	$('#lastStep').on('click', function(){
		$('#sup02').css('display', 'none');
		submitForm.show();
	})
	/* 显示隐藏面板 */
	selectCate.on('click' ,function (e){
     	$(this).children(".alert").show();
		 var e = e || window.event;
		 if (e.stopPropagation) {
			 e.stopPropagation();
		 } else {
			 e.cancelBubble = true;
		 }
     });
	selectCate.find('span:first').on('click', function(){
		$(this).next('div').toggle('fast');
	});
	/* 点击一级分类切换不同药材分类页 */
	selectCate.find('ul.classify-list > li').on('click', function(e) {
		var self = $(this);
		self.addClass('click').siblings('li').removeClass('click');
		self.children(".drug-list").show();
		self.siblings("li").children(".drug-list").hide();
	});
	/* 点击弹框里面的药材 */
	selectCate.find("ul.drug-list > li").on('click', function(e) {
		warn_drug.fadeOut();
		var self = $(this),
			drug = self.text(),
			id = self.attr('data-param');
		var cateName = self.parent("ul").siblings("span").text(),
		cateId = self.parent("ul").siblings("span").attr("data-param");
		selectCate.children('span:first').text(drug);
		$(".alert").hide();
		//给隐藏域赋值
		//给隐藏域赋值
		submitForm.find('input[name=\'cate_name\']').val(cateName);
		submitForm.find('input[name=\'cate_id\']').val(cateId);
		submitForm.find('input[name=\'goods_name\']').val(drug);
		submitForm.find('input[name=\'goods_id\']').val(id);
		
		//根据药材异步加载对应的规格
		$.getJSON(urls[0], { id: id }, function(data) {
			if($.isArray(data) && data.length > 0) {
				var spec_str = '<option value="-1" selected="selected">请选择</option>';
				for(var i in data) {
					spec_str += '<option value="' + data[i].id + '">' + data[i].attr_name + '</option>';
				}
				$('#select_spec').html(spec_str);
			}
		});
		submitForm.find('input[name=\'goods_attr_id\']').val('');
		submitForm.find('input[name=\'goods_attr_name\']').val('');
		submitForm.find('input[name=\'custom_name\']').val('');
		//阻止冒泡
     	var e = e || window.event;
		 if (e.stopPropagation) {
			 e.stopPropagation();
		 } else {
		 	e.cancelBubble = true;
		 }
	});
	/* 动态改变属性 */
	$('#select_spec').on('change',function() {
		var obj = $(this).children('option:selected');
		if(parseInt(obj.val()) > 0) {
			submitForm.find('input[name=\'goods_attr_id\']').val(obj.val());
			submitForm.find('input[name=\'goods_attr_name\']').val(obj.text());
		} else {
			submitForm.find('input[name=\'goods_attr_id\']').val('');
			submitForm.find('input[name=\'goods_attr_name\']').val('');
		}
	});
	/* 点击空白处 关闭弹框 */
	$(document).on('click' ,function() {
		$(".alert").hide();
	});
	/* *************************对省市县进行操作********************************/	
	/* 是否显示省市县下拉框 */
	submitForm.find('input[name=\'origin_type\']').on('click', function() {
		if($(this).val() == 3) {
			$('div.place-div').show();
		} else {
			$('div.place-div').hide();
		}
	});
	/* 默认加载省份 */
	$.get(urls[1], function(data) {
		if(data) {
			prov_select.html(data);
		}
	})
	/* 改变省份选择城市 */
	prov_select.change(function() {
		origin_area = $(this).find("option:selected").text();
		// 读取市区列表
		$.get(urls[2], { id: prov_select.val() }, function(data) {
			city_select.html(data);
		});
		//清空地区
		area_select.html("<option value='0'>请选择</option>");
	});
	/* 改变城市选择区域 */
	city_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + $(this).find('option:selected').text();
		} else {
			origin_area = prov_select.find('option:selected').text();
		}
		$.get(urls[3], { id: city_select.val() }, function(data) {
			area_select.html(data);
		});
	});
	/* 改变区域赋值 */
	area_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text() + $(this).find("option:selected").text();
		} else {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text();
		}
	});
	/* **********************结束对省市县进行操作********************************/
	/* *************************对自定义药材进行操作******************************* */	
	$('#custom_btn').on('click', function(e) {
		var customName = $.trim(submitForm.find('input[name=\'custom_name\']').val());
		var reg=/[\u4e00-\u9fa5]{1,}/;
		if(customName == '') {
			//$.custom('请输入药材名称');
			showWarn(warn_custom , '请输入药材名称');
			return;
		}
		if(!reg.test(customName)){
			//$.custom('请输入正确的药名!');
			showWarn(warn_custom , '请输入正确的药名');
			return;
		}
		selectCate.children('span:first').text(customName);
		warn_drug.fadeOut();
		//去掉规格
		$('#select_spec').html("<option value='0'>请选择</option>");
		$(".alert").hide();
		//阻止冒泡
		var e = e || window.event;
		if (e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	});
	/* ***********************结束对自定义药材进行操作******************************* */
	/* 选择支付宝的样式替换 */
	$('.alipay01-sel input:radio').on('click', function() {
		var _this = $(this);
		if(_this.prop('checked')){
			_this.parent().addClass('active').siblings().removeClass('active');
		}
		/*if(_this.prop('checked')) {
			_this.prev('i').css('background', 'url(/public/home/user/images/icon01.png) no-repeat center center');
			_this.attr('checked',true).parent().siblings().find('input').attr('checked',false).prev('i').css('background', 'none');
			_this.parent().addClass('active').siblings().removeClass('active');
		} else {			
			_this.prev('i').css('background', 'none');
			_this.parent().removeClass('active');
		}*/
		submitForm.find('input[name=\'pay_type\']').val(this.value);
	});
	/* 关于同意发布求购的时候 */
	$('#agreen').on('click' ,function() {
		var _this = $(this);
		if(_this.prop('checked')) {
			_this.prev('i').css('background', 'url(/Public/Home/user/images/icon02.png) no-repeat center center');
		} else {
			_this.prev('i').css('background', 'none');
		}
	});
	var repeat_click = true;
	var _confirmModal = $('#confirmModal'); 
	/* 取消弹出的确定框 */
	_confirmModal.find('i.close,a.close').on('click', function(){
		_confirmModal.hide();		
	});	
	/* 提交表单数据 */
	$('#submitBtn').on('click', function(){		
		var payType = $("input[name='payType']:checked").val();
		if(isEmpty(payType)){
			$.custom('请选择一种支付方式');return;
		}
		//在这里判断是采用哪种方式
		if(!$('#agreen').attr('checked')){
			$.custom('请同意协议并勾选');return;
		}
		_confirmModal.show(0, function(){
			var _this = $(this);
			_this.find('a.ok').off('click').on('click',function(e){
				if(!repeat_click) { return; }
				repeat_click = false;
				_confirmModal.hide();
				//将支付方式传递给隐藏域里面
				datas = $('#submit_form').serialize();
				ajaxFormSubmit(datas);
			});
		});
	});
})
/* 提交表单数据 */
function ajaxFormSubmit(datas){
	$.ajax({
		url: urls[4],
		dataType: "json",		
		data: datas,
		type: "post",
		beforeSend: function() {
			        
		},
		success: function(req) {
			if(req.code == 1){
				$.custom('发布成功');
				window.location.href = redirectUrl;
			}else{
				$.custom(req.msg);
			}
		},
		error: function() {			
			$.custom('网络连接超时，请稍后再试');
		}
	});
}
/* 给下一个页面赋值 */
function assign(){	
	//开始赋值
	var sb = '';
	if(isEmpty(formData['customName'])){
		sb = '<li><span>分类：</span>'+ formData['cate_name'] +'</li>'
			+ '<li><span>药材：</span>'+ formData['goods_name'] +'</li>'
			+ '<li><span>规格：</span>'+ (formData['goods_attr_name'] || '暂无') +'</li>';
	}else{
		sb = '<li><span>名称：</span>'+ formData['customName'] +'</li>';
	}
	sb += '<li><span>数量：</span><i class="color-r">'+ formData['num'] +'公斤</i></li>'
		+ '<li><span>产地：</span>'+ formData['origin_area'] +'</li>'
		+ '<li><span>雇主：</span>'+ formData['contacts'] +'</li>'		
		+ '<li><span>手机：</span>'+ formData['mobile'] +'</li>'
		+ (formData['qq'] && '<li><span>Q&nbsp;Q：</span>'+ formData['qq'] +'</li>')
		+ '<li><span>详情：</span>'+ formData['detail'] +'</li>';
	
	$('#sup02 ul.detail').html(sb);	
}
/* 初始化操作 */
function init(){
				var objLength = catObj.length;
	//把1.2  3 4 5以上长度的字 分别放在4个数组
	for(var i = 0; i < objLength; i++) {
		var arr = [];
		for(var j = 0; j < 4; j++) {
			var newArr = [];
			arr.push(newArr);
		}
		for(var j = 0; j < catObj[i].c.length; j++) {
			var cgLength = catObj[i].c[j].cg.length
			if(cgLength <= 2) {
				arr[0].push(catObj[i].c[j]);
			} else if(cgLength == 3) {
				arr[1].push(catObj[i].c[j]);
			} else if(cgLength == 4) {
				arr[2].push(catObj[i].c[j]);

			} else if(cgLength >= 5) {
				arr[3].push(catObj[i].c[j]);
			}
		}
		catObj[i].c = arr;
	}
	for(var i = 0; i < objLength; i++) {
		var html = '';
		var title = catObj[i].t,
			id = catObj[i].i;
		var li = '<li> <span data-param="' + id + '">' + title + '</span> <ul class="drug-list clearfix"></ul> </li>'
		$(".classify-list>li:last").before(li);
		for(var j = 0; j < catObj[i].c.length; j++) {
			if(j == 0) {
				for(var c = 0; c < catObj[i].c[j].length; c++) {
					var ci = catObj[i].c[j][c].ci,
						cg = catObj[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="clear:both" data-param="' + ci + '">' + cg + '</li>'
					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 1) {
				for(var c = 0; c < catObj[i].c[j].length; c++) {
					var ci = catObj[i].c[j][c].ci,
						cg = catObj[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="width:100%;margin-top:10px;border-top:0.5px dashed #ccc;"></li><li style="clear:both" data-param="' + ci + '">' + cg + '</li>';
					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 2) {
				for(var c = 0; c < catObj[i].c[j].length; c++) {
					var ci = catObj[i].c[j][c].ci,
						cg = catObj[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="width:100%;margin-top:10px;border-top:0.5px dashed #ccc;"><li style="clear:both" data-param="' + ci + '">' + cg + '</li>';

					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 3) {
				for(var c = 0; c < catObj[i].c[j].length; c++) {
					var ci = catObj[i].c[j][c].ci,
						cg = catObj[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="width:100%;margin-top:10px;border-top:0.5px dashed #ccc;"></li><li style="clear:both" data-param="' + ci + '">' + cg + '</li>';
					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			}
		}
		$(".classify-list>li").eq(i).find(".drug-list").html(html)
	}
}
/* obj 是否为空 */
function isEmpty(obj) {
    return obj == undefined || obj == null || obj == "";
}