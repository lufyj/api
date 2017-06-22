var select_cate = $(".choose-durg"),
	alert_wp = $(".alert-wp"),
	submit_form = $("#myform"),
	drug_show = $(".choose-durg>span");
var custom_input = $('input[name="custom_name"]');
//判断自定义
var cate_id = $('input[name="gid"]').val(),
	urls = $(".old_path").val();
var show_img = $(".show_img");
$(function() {
	//点击空白处 关闭弹框
	$(document).click(function() {
		alert_wp.hide('fast');
	});
	if(cate_id > 150000) {
		//是自定义
		var custom_name = drug_show.text();
		custom_input.val(custom_name);
	} else {
		createCates();
		//去掉自定义
		custom_input.parents("li").empty();
	}
	//删除图片数组
	var delArr = [];
	//展示图片
	if(urls) {
		var urlsArr = urls.split(",");
		for(var i = 0; i < urlsArr.length; i++) {
			var html = '<div class="file-preview-frame" data-url="' + urlsArr[i] + '"><div class="kv-file-content"><img src="' + urlsArr[i] + '" alt=""  class="kv-preview-data file-preview-image" style="width:140px;height:120px;"/></div><div class="file-thumbnail-footer"><div class="file-actions"><div class="file-footer-buttons"><button type="button" class="btn btn-xs btn-default remove-btn" title="删除文件"><i class="glyphicon glyphicon-trash text-danger"></i></button><div class="file-upload-indicator" title="没有上传"></div><div class="clearfix"></div></div></div></div>'
			show_img.append(html);
		}
	} else {
		show_img.remove();
	}
	//展示图片删除按钮
	$(".remove-btn").click(function() {
		var parents = $(this).parents(".file-preview-frame");
		var src = parents.find("img.file-preview-image").attr("src");
		delArr.push(src);
		parents.remove();
		if($(".show_img .file-preview-frame").length == 0) {
			$(".show_img").remove();
		}
	});
	//点击展开弹框分类
	select_cate.click(function(e) {
		select_cate.find('div.alert-wp').toggle("fast");
		var parent = select_cate.find('div.alert-wp');
		if(parent.find('li:not(:first)').hasClass('active')) {
			parent.find('li:first').removeClass('active');
		} else {
			parent.find('li:first').addClass('active');
		}
		return false;
	});
	//点击分类切换分类
	select_cate.on("click", ".classify-list>li", function(e) {
		var self = $(this);
		$(".drug-list").hide();
		self.find(".drug-list").show().parent().addClass('active').siblings().removeClass('active');
		return false;
	});
	//点击药材 选取药材
	select_cate.on("click", "ul.drug-list > li", function(e) {
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
		//根据药品异步加载对应的规格
		$.getJSON(apiArr[2], {
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
	$('div.drug-list input[type="button"]').click(function(e) {
		//讲药材名赋值给显示的span
		var drugName = $(this).siblings("label:last").find("input").val(),
			cateId = $(this).parents("li").children("span").attr("data-param");
		if(drugName.trim() == "") {
			alert("请输入药名");
			return;
		}
		drug_show.text(drugName);
		submit_form.find('input[name=\'cid\']').val(cateId);
		//清空规格
		$("#levels").html('');
		// 关闭弹框
		alert_wp.hide('fast');
		//阻止冒泡
		return false;
	});
	var prov_select = $("#prov_select"),
		city_select = $("#city_select"),
		area_select = $("#area_select"),
		supply_prvo = $("#supply_prvo"),
		supply_city = $("#supply_city"),
		supply_area = $("#supply_area");
	var origin_area = $(".origin_area").val(),
		supply_areaS = $(".goods_area").val();
	prov_select.change(function() {
		origin_area = $(this).find('option:selected').text();
		// 读取市区列表
		$.get(apiArr[0], {
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
		$.get(apiArr[0], {
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
		$.get(apiArr[1], {
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
		$.get(apiArr[1], {
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
	$(".price").change(function() {
		checkPrice();
	});
	checkPrice();
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
	//产地输入框显隐
	$(".origin_place").change(function() {
		checkPlace();
	});
	//多图上传
	$('#file-fr').fileinput({
		language: 'zh',
		uploadUrl: apiArr[3],
		allowedFileExtensions: ['jpg', 'png', 'gif'],
		showCaption: false,
		overwriteInitial: false,
	});
	//上传回调
	$("#file-fr").on("fileuploaded", function(event, data, previewId, index) {
		var u = data.response.path;
		$(".file-preview-frame").each(function() {
			if($(this).attr("data-fileindex") == index) {
				$(this).attr("data-url", u);
			}
		});
		$(".file-preview").on("click", ".kv-file-remove", function() {
			var path = $(this).parents(".file-preview-frame").attr("data-url");
			if(path) {
				$.ajax({
					url: apiArr[4],
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
	});
	$(".sub-btn").click(function() {
		$('#del-tip').text('确定修改？');
		var dialog = $("div.base-modal");
		dialog.show();
		$('button.ok').on('click', function() {
			//判断图片几张
			if($(".file-preview-frame").length > 5) {
				alert("最多上传5张图片");
				return false;
			}
			//删除图片
			if(delArr.length > 0) {
				var delStr = delArr.join(",");
				$.ajax({
					url: "{:U('Supply/delImg')}",
					data: {
						path: delStr
					},
					dataType: 'json',
					type: 'post',
					success: function(data) {
						console.log('删除成功');
					}
				})
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
	});

	function createCates() {
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
});