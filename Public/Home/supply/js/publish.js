var select_cate = $('#select_cate'),
	submit_form = $('#submit_form');
//加载时候 默认药材分类第一个选中
$(".publish-ul .classify-list>li:first").addClass("click");
$(".publish-ul .classify-list>li:first").find(".drug-list").show();
$(function() {
	function preventDefault(e) {
		var e = e || window.event;
		if(e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	//显示错误
	function showWarn(obj, msg) {
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
		warn_details = $(".warn_details"),
		warn_spy = $(".warn_spy"),
		warn_price = $(".warn_price"),
		warn_spy_details = $(".warn_spy_details");
	var num_input = $(".num_input"),
		qq_input = $("input[name='qq']"),
		contacts_input = $("input[name='contacts']"),
		mobile_input = $("input[name='mobile']"),
		details_input = $("textarea[name='details']"),
		custom_input = $("input[name=\'custom_name\']"),
		price_input = $("input[name=\'price\']");
	detailPlace_input = $("input[name=\'supply_details\']");
	//产地_省
	var prov_select = $('#prov_select'),
		//货源地_省
		supply_prvo = $('#supply_prvo'),
		//产地_市
		city_select = $('#city_select'),
		//货源地_市
		supply_city = $('#supply_city'),
		//产地_县
		area_select = $('#area_select'),
		//货源地_县
		supply_area_select = $('#supply_area_select'),
		//记录地址
		origin_area = '',
		supply_area = '';
	//自定义
	custom_input.focus(function() {
		warn_custom.fadeOut();
	});
	//验证数量
	num_input.focus(function() {
		warn_num.fadeOut();
	});
	num_input.blur(function() {
		var txt = $.trim($(this).val()),
			//reg = /^\d+(\.\d{1,2})?$/;
			reg = /^(\d{1,7})$/;
		if(txt) {
			//if(txt!="大货"){
				if(txt <= 0 || !reg.test(txt)) {
					showWarn(warn_num, '请输入正确的数字');
					$(this).val("");
				}
			//}	
		}
	});
	//单价
	price_input.focus(function() {
		warn_price.fadeOut();
	});
	//省地县改变
	prov_select.change(function() {
		//地址提示消失
		warn_place.fadeOut();
	});
	//货源所在地
	supply_prvo.change(function() {
		//地址提示消失
		warn_spy.fadeOut();
	});
	//联系人
	contacts_input.focus(function() {
		warn_contacts.fadeOut();
	});
	//电话
	mobile_input.focus(function() {
		warn_mobile.fadeOut();
	});
	//详情
	details_input.focus(function() {
		warn_details.fadeOut();
	});
	//验证QQ号是否正确
	qq_input.focus(function() {
		warn_qq.fadeOut();
	});
	//地址
	detailPlace_input.focus(function() {
		warn_spy_details.fadeOut();
	});

	//点击空白处 关闭弹框
	$(document).click(function() {
		$(".alert").hide();
	});
	//点击价格出现输入框
	var price_wp = $("#price_wp");
	$('input[name="price_type"]').click(function() {
		if($(this).val() == "1") {
			price_wp.show();
		} else {
			price_wp.hide();
		}
	});
	//点击数量出现数量输入框
	var num_wp = $('#num_wp');
	$('input[name="num_sel"]').click(function(){
		if($(this).val()=="1"){
			num_wp.show();
		}else{
			num_wp.hide();
		}
	})
	//点击省地县出现select
	$(".place-li>label").each(function(index) {
		var self = $(this);
		self.click(function() {
			if(index == 2) {
				$(".place-div").show();
			} else {
				$(".place-div").hide();
			}
		})
	});
	//显示或隐藏药品面板
	$(".drug-name").click(function(e) {
		$(this).children(".alert").toggle('fast');
		preventDefault(e);
	});
	//点击一级分类切换不同药品分类页 
	select_cate.find('ul.classify-list > li').on('click', function(e) {
		var self = $(this);
		self.addClass("click").siblings("li").removeClass("click");
		self.children(".drug-list").show();
		self.siblings("li").children(".drug-list").hide();
		preventDefault(e);
	});
	//点击弹框里面的药材 
	select_cate.find("ul.drug-list > li").on('click', function(e) {
		var self = $(this),
			drug = self.text(),
			id = self.attr('data-param');
		var cateName = self.parent("ul").siblings("span").text(),
			cateId = self.parent("ul").siblings("span").attr("data-param");
		$(".drug-name>span").text(drug);
		warn_drug.fadeOut();
		$(".alert").hide();
		//给隐藏域赋值
		submit_form.find('input[name=\'cate_name\']').val(cateName);
		submit_form.find('input[name=\'cate_id\']').val(cateId);
		submit_form.find('input[name=\'goods_name\']').val(drug);
		submit_form.find('input[name=\'goods_id\']').val(id);
		//根据药品异步加载对应的规格
		$.getJSON(urls[0], {
			id: id
		}, function(data) {
			if($.isArray(data) && data.length > 0) {
				var spec_str = '<option value="-1" selected="selected">请选择</option>';
				for(var i in data) {
					spec_str += '<option value="' + data[i].id + '">' + data[i].attr_name + '</option>';
				}
				$('#select_spec').html(spec_str);
			}
		});
		submit_form.find('input[name=\'goods_attr_id\']').val('');
		submit_form.find('input[name=\'goods_attr_name\']').val('');
		submit_form.find('input[name=\'custom_name\']').val('');
		//阻止冒泡
		preventDefault(e);
	});
	//自定义类里面点击确定按钮
	$('div.drug-list input[type="button"]').click(function(e) {
		//讲药材名赋值给显示的span
		var drugName = $.trim($(this).siblings("label:last").find("input").val());
		var reg = /[\u4e00-\u9fa5]{1,}/;
		if(drugName == "") {
			showWarn(warn_custom, '请输入药名');
			return;
		} else {
			if(!reg.test(drugName)) {
				showWarn(warn_custom, '请输入正确的药名');
				return;
			}
		}
		$(".drug-name>span").text(drugName);
		warn_drug.fadeOut();
		// 关闭弹框
		$(".alert").hide();
		//去掉规格
		$('#select_spec').html("<option value='0'>请选择</option>");
		//阻止冒泡
		preventDefault(e);
	});
	//规格
	$('#select_spec').change(function() {
		var obj = $(this).children('option:selected');
		if(parseInt(obj.val()) > 0) {
			submit_form.find('input[name=\'goods_attr_id\']').val(obj.val());
			submit_form.find('input[name=\'goods_attr_name\']').val(obj.text());
		} else {
			submit_form.find('input[name=\'goods_attr_id\']').val('');
			submit_form.find('input[name=\'goods_attr_name\']').val('');
		}
	});
	/**************************对省市县进行操作********************************/
	/* 
	 * 产地省市县
	 */
	submit_form.find('input[name=\'origin_type\']').on('click', function() {
		if($(this).val() == 3) {
			$(".upload-btn").css({
				'top': "708px"
			});
			$("#spy").css({
				'top': "708px"
			});
			$('div.place-div').show();
		} else {
			$(".upload-btn").css({
				'top': "662px"
			});
			$("#spy").css({
				'top': "662px"
			})
			$('div.place-div').hide();
		}
	});
	/* 
	 * 默认读取所有省份
	 */
	$.get(urls[1], function(data) {
			if(data) {
				prov_select.html(data);
				supply_prvo.html(data);
			}
		})
		//改变省份选择城市
	prov_select.change(function() {
		origin_area = $(this).find("option:selected").text();
		// 读取市区列表
		$.get(urls[2], {
			id: prov_select.val()
		}, function(data) {
			city_select.html(data);
		});
		//清空地区
		area_select.html("<option value='0'>请选择</option>");
	});
	//改变货源地省份选择城市
	supply_prvo.change(function() {
		supply_area = $(this).find("option:selected").text();
		$('#supply_area').val(supply_area);
		// 读取市区列表
		$.get(urls[2], {
			id: supply_prvo.val()
		}, function(data) {
			supply_city.html(data);
		});
		//清空地区
		supply_area_select.html("<option value='0'>请选择</option>");
	});
	//改变城市选择区域
	city_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + $(this).find('option:selected').text();
		} else {
			origin_area = prov_select.find('option:selected').text();
		}
		$.get(urls[3], {
			id: city_select.val()
		}, function(data) {
			area_select.html(data);
		});
	});
	//改变货源地城市城市选择区域
	supply_city.change(function() {
		if(parseInt($(this).val()) > 0) {
			supply_area = supply_prvo.find('option:selected').text() + $(this).find('option:selected').text();
		} else {
			supply_area = supply_prvo.find('option:selected').text();
		}
		$('#supply_area').val(supply_area);
		$.get(urls[3], {
			id: supply_city.val()
		}, function(data) {
			supply_area_select.html(data);
		});
	});
	area_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text() + $(this).find("option:selected").text();
		} else {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text();
		}
	});
	supply_area_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			supply_area = supply_prvo.find('option:selected').text() + supply_city.find('option:selected').text() + $(this).find("option:selected").text();
		} else {
			supply_area = supply_prvo.find('option:selected').text() + supply_city.find('option:selected').text();
		}
		$('#supply_area').val(supply_area);
	});

	/* 
	 * 提交表单
	 */
	var span_click = true; //重复标示
	$('#submit_btn').on('click', function(e) {
		//判断是否选择了分类中的药材还是自定义了药材
		var customName = submit_form.find('input[name=\'custom_name\']').val();
		if($.trim(customName) == '') {
			//这里判断是否选择了分类，药材
			var cid = parseInt(submit_form.find('input[name=\'cate_id\']').val());
			if(isNaN(cid) || cid <= 0) {
				showWarn(warn_drug, '请选择药材分类');
				return;
			}
			var gid = parseInt(submit_form.find('input[name=\'goods_id\']').val());
			if(isNaN(gid) || gid <= 0) {
				showWarn(warn_drug, '请选择药材');
				return;
			}
		}
		/*var num = $.trim(submit_form.find('input[name=\'num\']').val());
		if(num == '' || num == '请输入药材的数量') {
			showWarn(warn_num, '请输入药材的数量或者"大货"');
			return;
		}*/
		var num_sel = $.trim(submit_form.find('input[name=\'num_sel\']:checked').val());
		if(num_sel == 1){
			var num = $.trim(submit_form.find('input[name=\'num\']').val());
			if(num == '' || num == '请输入药材的数量') {
				showWarn(warn_num, '请输入药材的数量');
				return;
			}
		}
		var price_type = $.trim(submit_form.find('input[name=\'price_type\']:checked').val());
		if(price_type == 1) {
			var price = $.trim(submit_form.find('input[name=\'price\']').val());
			if(price == '') {
				showWarn(warn_price, '请填写单价');
				return;
			} else {
				//var reg_price = /^\d+(\.\d{1,2})?$/;
				var reg_price = /^\d{1,5}(\.\d{1,2})?$/;
				if(!reg_price.test(price) || price <= 0) {
					showWarn(warn_price, '单价格式不正确');
					submit_form.find('input[name=\'price\']').val('');
					return;
				}
			}
		}
		var originType = submit_form.find('input[name="origin_type"]:checked').val();
		if(originType == 3) {
			var pid = parseInt(prov_select.val());
			if(isNaN(pid) || pid <= 0) {
				showWarn(warn_place, '请选择地区');
				return;
			}
			//submitForm.find('input[name=\'origin_area\']').val(origin_area);
		}
		var contacts = $.trim(submit_form.find('input[name=\'contacts\']').val());
		if(contacts == '' || contacts == '请输入联系人') {
			showWarn(warn_contacts, '请输入联系人');
			return;
		}
		var mobile = submit_form.find('input[name=\'mobile\']').val();
		if(mobile == '' || mobile == '请输入您的手机号') {
			showWarn(warn_mobile, '请输入您的手机号');
			return;
		}
		if(!/^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/.test(mobile)) {
			showWarn(warn_mobile, '请输入正确的手机号');
			return;
		}
		//qq
		var qq = $.trim(qq_input.val());
		if(qq && !/^[1-9][0-9]{4,9}$/.test(qq)) {
			showWarn(warn_qq, '请输入正确的QQ号码(可不填)');
			return;
		}
		var spyid = parseInt(supply_prvo.val());
		if(isNaN(spyid) || spyid <= 0) {
			showWarn(warn_spy, '请选择货源所在地');
			return;
		}
		var detail_place = detailPlace_input.val();
		if(detail_place.length >= 35) {
			showWarn(warn_spy_details, '地址过长');
			return;
		}
		var detail = $.trim($('textarea[name="details"]').val());
		if(detail == '') {
			showWarn(warn_details, '请输入详情介绍');
			return;
		}
		//开始提交
		if(!span_click) {
			return;
		}
		// 阻止默认事件提交
		e.preventDefault();
		//在这个时候给input【name=origin_name】
		submit_form.find('input[name=\'origin_area\']').val(origin_area);
		var params = submit_form.serialize(); // http request parameters. 
		params = decodeURIComponent(params, true);
		//在进行编码
		params = encodeURI(encodeURI(params));
		$.ajax({
			url: urls[4], //请求的url地址
			dataType: "json", //返回格式为json
			async: true, //请求是否异步，默认为异步，这也是ajax重要特性
			data: params, //参数值
			type: "post", //请求方式
			beforeSend: function() {
				span_click = false;
				//请求前的处理
			},
			success: function(req) {
				//请求成功时处理
				//弹出未定义		        
				$.custom(req.msg);
				span_click = true;
				//发布成功之后跳转到发布页面
				if(req.code) {
					// alert('发布成功之后跳转到求购列表');
					// window.location.href = '';
					setTimeout(function(){
						location.reload();
					},300);
				}
			},
			error: function() {
				//请求出错处理
				$.custom('网络请求错误，请稍后再试~');
				span_click = true;
			}
		});
	});
	//-------------------------上传图片-------------------------------------
	$("#spy").on("click", "div.del", function() {
		//出现添加图片按钮
		$(".upload-btn").show();
		$("#spy").addClass("spy");
		//移除dom
		var self = $(this);
		self.parent("li").remove();
		//动态修改id和name
		$(".img-li").each(function(index) {
			var a = index + 1
			var self = $(this);
			self.children("img").attr("id", "spy-img" + a);
			self.children("input").attr("name", "img" + a);
		});
	});
	$(".upload-btn").on('change', "#up-spy", function() {
		var i = $(".img-li").length;
		if(i >= 5) {
			return false;
		} else {
			$("#spy-form").ajaxSubmit({
				type: 'post',
				url: urls[5],
				success: function(data) {
					if(typeof(data) == "object") {
						if(data.path) {
							var path =  data.path;
							var index = i + 1;
							var img = $('<li class="img-li"><img id="spy-img' + index + '" /><div class="del"><span>删除</span></div><input name="img' + index + '" type="hidden" value="' + data.path + '"/></li>');
							$("#spy").append(img);
							$("#spy-img" + index).attr("src", path);
							$('#spy-form').resetForm();
							if(i >= 4) {
								$(".upload-btn").hide();
								$("#spy").removeClass("spy");
							}
						} else {
							$.custom(data.msg);
						}
					} else {
						var datas = JSON.parse(data);
						if(datas.path) {
							var path = datas.path;
							var index = i + 1;
							var img = $('<li class="img-li"><img id="spy-img' + index + '" /><div class="del"><span>删除</span></div><input name="img' + index + '" type="hidden" value="' + data.path + '"/></li>');
							$("#spy").append(img);
							$("#spy-img" + index).attr("src", path);
							$('#spy-form').resetForm();
							if(i >= 4) {
								$(".upload-btn").hide();
								$("#spy").removeClass("spy");
							}
						} else {
							$.custom(datas.msg);
						}
					}

				},
				error: function(error) {
					$.custom('上传失败');
				}
			})
		}
	});
})