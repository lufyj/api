var select_cate = $(".choose-durg");
var alert_wp = $(".alert-wp");
var submit_form = $("#myform"),
	drug_show = $(".choose-durg>span");
var custom_input = $('input[name="custom_name"]');
$(function() {
	//初始化药材分类
	init();
	select_cate.click(function() {
		alert_wp.toggle('fast');
		if(alert_wp.find('li:not(:first)').hasClass('active')) {
			alert_wp.find('li:first').removeClass('active');
		} else {
			alert_wp.find('li:first').addClass('active');
		}
		return false;
	});
	select_cate.on("click", ".classify-list>li", function() {
		var self = $(this);
		$(".drug-list").hide();
		self.find(".drug-list").show().parent().addClass('active').siblings().removeClass('active');
		//阻止冒泡
		return false;
	});
	select_cate.on("click", "ul.drug-list > li", function() {
		var self = $(this),
			drug = self.text(),
			id = self.attr('data-param');
		var cId = self.parents("li").find('span:first').attr('data-param'),
			cName = self.parents("li").find('span:first').text();
		submit_form.find('input[name=\'c_name\']').val(cName);
		submit_form.find('input[name=\'cid\']').val(cId);
		drug_show.text(drug);
		alert_wp.hide('fast');
		//给隐藏域赋值
		submit_form.find('input[name="g_name"]').val(drug);
		submit_form.find('input[name="gid"]').val(id);
		custom_input.val('');
		//根据药品异步加载对应的规格
		$.getJSON(apiArr[3], {
			id: id
		}, function(data) {
			var length = data.length;
			if(length > 0) {
				var spec_str = '<option value="-1" selected="selected">请选择</option>';
				for(var i = 0; i < length; i++) {
					spec_str += '<option value="' + data[i].id + '">' + data[i].attr_name + '</option>';
				}
				$("#levels").html(spec_str);
			} else {
				$("#levels").html('');
			}
		});
		//阻止冒泡
		return false;
	});
	//自定义类里面点击确定按钮
	$('div.drug-list input[type="button"]').click(function() {
		//		讲药材名赋值给显示的span
		var drugName = $(this).siblings("label:last").find("input").val();
		if(drugName.trim() == "") {
			alert("请输入药名");
			return;
		}
		drug_show.text(drugName);
		// 关闭弹框
		alert_wp.hide('fast');
		//清空规格
		$("#levels").html('');
		//阻止冒泡
		return false;
	});
	//点击空白处 关闭弹框
	$(document).click(function() {
		alert_wp.hide('fast');
	});

	//三级联动部分
	var prov_select = $("#prov_select"),
		city_select = $("#city_select"),
		area_select = $("#area_select"),
		supply_prvo = $("#supply_prvo"),
		supply_city = $("#supply_city"),
		supply_area = $("#supply_area");
	var origin_area = '',
		supply_areaS = '';
	$.get(apiArr[0], function(data) {
		if(data) {
			prov_select.html(data);
			supply_prvo.html(data);
		}
	});
	prov_select.change(function() {
		origin_area = $(this).find('option:selected').text();
		// 读取市区列表
		$.get(apiArr[1], {
			id: prov_select.val()
		}, function(data) {
			city_select.html(data);
		});
		//清空地区
		area_select.html("<option value='0'>请选择</option>");
	});
	supply_prvo.change(function() {
		supply_areaS = $(this).find('option:selected').text();
		// 读取市区列表
		$.get(apiArr[1], {
			id: supply_prvo.val()
		}, function(data) {
			supply_city.html(data);
		});
		//清空地区
		supply_area.html("<option value='0'>请选择</option>");
	});
	//改变城市选择区域
	city_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + $(this).find('option:selected').text();
		} else {
			origin_area = prov_select.find('option:selected').text();
		}
		$.get(apiArr[2], {
			id: city_select.val()
		}, function(data) {
			area_select.html(data);
		});
	});
	supply_city.change(function() {
		if(parseInt($(this).val()) > 0) {
			supply_areaS = supply_city.find('option:selected').text() + $(this).find('option:selected').text();
		} else {
			supply_areaS = supply_city.find('option:selected').text();
		}
		$.get(apiArr[2], {
			id: supply_city.val()
		}, function(data) {
			supply_area.html(data);
		});
	});
	area_select.change(function() {
		if(parseInt($(this).val()) > 0) {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text() + $(this).find("option:selected").text();
		} else {
			origin_area = prov_select.find('option:selected').text() + city_select.find('option:selected').text();
		}
	});
	supply_area.change(function() {
		if(parseInt($(this).val()) > 0) {
			supply_areaS = supply_prvo.find('option:selected').text() + supply_city.find('option:selected').text() + $(this).find("option:selected").text();
		} else {
			supply_areaS = supply_prvo.find('option:selected').text() + supply_city.find('option:selected').text();
		}
	});
	//检测价格类型和地区类型
	checkPrice()
	checkPlace();

	function checkPrice() {
		$(".price").each(function() {
			if($(this).prop("checked") && $(this).val() == 1) {
				$(".price_input").show();
			} else {
				$(".price_input").hide();
			}
		});
	}

	function checkPlace() {
		$(".origin_place").each(function() {
			if($(this).prop("checked") && $(this).val() == 3) {
				$(".place_select").show();
			} else {
				$(".place_select").hide();
			}
		});
	}
	//价格输入框显隐
	$(".price").change(function() {
		checkPrice();
	});
	//产地输入框显隐
	$(".origin_place").change(function() {
		checkPlace();
	});
	//多图上传
	$('#file-fr').fileinput({
		language: 'zh',
		uploadUrl: apiArr[4],
		allowedFileExtensions: ['jpg', 'png', 'gif'],
		showCaption: false,
		maxFileSize: 4000,
		maxFileCount: 5,
	});
	//上传回调
	var urlArr = [];
	$("#file-fr").on("fileuploaded", function(event, data, previewId, index) {
		var u = data.response.path;
		urlArr.push(u);
		$(".file-preview-frame").each(function() {
			if($(this).attr("data-fileindex") == index) {
				$(this).attr("data-url", u);
			}
		});
	});
	$(".file-preview").on("click", ".kv-file-remove", function() {
		var path = $(this).parents(".file-preview-frame").attr("data-url");
		if(path) {
			$.ajax({
				url: apiArr[5],
				data: {
					path: path
				},
				dataType: 'json',
				type: 'post',
				success: function(data) {
					console.log(data);
				}
			})
		}
	});
	//规格
	$(".sub-btn").click(function() {
		//先验证表单
		var result = checkForm();
		if(!result) {
			return false;
		}
		var str = '',
			id = '';
		if($("#levels").val() > 0) {
			str = $("#levels").find('option:selected').text();
			id = $("#levels").val();
		}
		$(".levels_name").val(str);
		$(".levels_id").val(id);
		$(".goods_area").val(supply_areaS);
		$(".origin_area").val(origin_area);
		var arr = [];
		$(".file-preview-frame").each(function() {
			var urls = $(this).attr("data-url");
			if (urls){
				arr.push(urls);
			}
		});
		$(".img_url").val(arr.join(","));
		$("#myform").submit();
	});
	//验证表单
	function checkForm() {
		var reg_phone = /^1[34578]\d{9}$/;
		var reg_qq = /^[0-9]*$/;
		var phone = $('input[name="mobile"]').val(),
			g_name = $('input[name="g_name"]').val(),
			qq = $('input[name="qq"]').val(),
			price = $('input[name="price"]').val(),
			numbers = $('input[name="num"]').val(),
			c_ad = $("#prov_select").val(),
			g_ad = $("#supply_prvo").val(),
			details = $('textarea[name="details"]').val(),
			det_ad = $('input[name="supplyDetail"]').val(),
			contacts = $('input[name="contacts"]').val();
		if (contacts == ''){
			alert('请填写联系人');
			return false;
		}else if(!reg_phone.test(phone)) {
			alert("请填写正确手机号");
			return false;
		} else if(numbers == '' || !reg_qq.test(numbers)) {
			alert("数量不能为空，或者数量格式不正确");
			return false;
		}else if(!reg_qq.test(qq) && qq) {
			alert("qq格式错误，请重新输入，如果不想填，可不填");
			return false;
		} else if($(".unit_price").prop("checked") && price == '') {
			alert("请填写单价");
			return false;
		}  else if(c_ad == 0 && $(".pca").prop("checked")) {
			alert("请选择省市县");
			return false;
		} else if(g_ad == 0) {
			alert("请选择货源所在地");
			return false;
		} else if (det_ad == ''){
			alert("请填写详细地址");
			return false;
		}else if(details == '') {
			alert("请填写详情");
			return false;
		} else if(custom_input.val() == '') {
			//不是自定义
			if(g_name == '') {
				alert("请填写药材名称")
				return false;
			}
		}else if($(".file-preview-frame").length > 5) {
			alert("最多上传5张图片");
			return false;
		}
		return true;
	}
})

function init() {
	var objLength = cateJson.length;
	//把1.2  3 4 5以上长度的字 分别放在4个数组
	for(var i = 0; i < objLength; i++) {
		var arr = [];
		for(var j = 0; j < 4; j++) {
			var newArr = [];
			arr.push(newArr);
		}
		for(var j = 0; j < cateJson[i].c.length; j++) {
			var cgLength = cateJson[i].c[j].cg.length
			if(cgLength <= 2) {
				arr[0].push(cateJson[i].c[j]);
			} else if(cgLength == 3) {
				arr[1].push(cateJson[i].c[j]);
			} else if(cgLength == 4) {
				arr[2].push(cateJson[i].c[j]);

			} else if(cgLength >= 5) {
				arr[3].push(cateJson[i].c[j]);
			}
		}
		cateJson[i].c = arr;
	}
	for(var i = 0; i < objLength; i++) {
		var html = '';
		var title = cateJson[i].t,
			id = cateJson[i].i;
		var li = '<li> <span data-param="' + id + '">' + title + '</span> <ul class="drug-list clearfix"></ul> </li>'
		$(".classify-list>li:last").before(li);
		for(var j = 0; j < cateJson[i].c.length; j++) {
			if(j == 0) {
				for(var c = 0; c < cateJson[i].c[j].length; c++) {
					var ci = cateJson[i].c[j][c].ci,
						cg = cateJson[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="clear:both" data-param="' + ci + '">' + cg + '</li>'
					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 1) {
				for(var c = 0; c < cateJson[i].c[j].length; c++) {
					var ci = cateJson[i].c[j][c].ci,
						cg = cateJson[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="width:100%;margin-top:10px;border-top:0.5px dashed #ccc;"></li><li style="clear:both" data-param="' + ci + '">' + cg + '</li>';
					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 2) {
				for(var c = 0; c < cateJson[i].c[j].length; c++) {
					var ci = cateJson[i].c[j][c].ci,
						cg = cateJson[i].c[j][c].cg;
					if(c == 0) {
						html += '<li style="width:100%;margin-top:10px;border-top:0.5px dashed #ccc;"><li style="clear:both" data-param="' + ci + '">' + cg + '</li>';

					} else {
						html += '<li data-param="' + ci + '">' + cg + '</li>';
					}
				}
			} else if(j == 3) {
				for(var c = 0; c < cateJson[i].c[j].length; c++) {
					var ci = cateJson[i].c[j][c].ci,
						cg = cateJson[i].c[j][c].cg;
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