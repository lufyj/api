var submitForm = $('#submit_form'),	selectCate = $('#select_cate');
var prov_select = $('#prov_select'), city_select = $('#city_select'), area_select = $('#area_select'), origin_area = '';
$(function(){
	/* 显示隐藏面板 */
	selectCate.on('click' ,function (e){
     	$(this).children(".alert").show();
     	return false;
    });
	selectCate.find('span:first').on('click', function(){
		$(this).next('div').toggle('fast');
	});	
	/* 点击空白处 关闭弹框 */
	$(document).on('click' ,function() {
		selectCate.find('div.alert-wp').hide();
	});
	if(params.goods_id <= 150000){
		createCates();
		//默认展开第一个药材面板
		selectCate.find('ul.classify-list > li:first').addClass("active");
		selectCate.find('ul.classify-list > li:first').find(".drug-list").show();
		/* 点击一级分类切换不同药材分类页 */
		selectCate.find('ul.classify-list > li').on('click', function(e) {
			var self = $(this);
			self.addClass('active').siblings('li').removeClass('active');
			self.children(".drug-list").show();
			self.siblings("li").children(".drug-list").hide();
			//给隐藏域赋值
			submitForm.find('input[name=\'cate_name\']').val(self.find('span:first').text());
			submitForm.find('input[name=\'cate_id\']').val(self.find('span:first').attr('data-param'));		
		});
		/* 点击弹框里面的药材 */
		selectCate.find("ul.drug-list > li").on('click', function(e) {
			var self = $(this),
				drug = self.text(),
				id = self.attr('data-param');
			selectCate.children('span:first').text(drug);
			selectCate.find('div.alert-wp').hide();
			//给隐藏域赋值
			submitForm.find('input[name=\'goods_name\']').val(drug);
			submitForm.find('input[name=\'goods_id\']').val(id);

			//根据药材异步加载对应的规格
			$.getJSON(urls[0], { id: id }, function(data) {
				var spec_str = '';
				if($.isArray(data) && data.length > 0) {
					spec_str = '<option value="-1" selected="selected">请选择</option>';
					for(var i in data) {
						spec_str += '<option value="' + data[i].id + '">' + data[i].attr_name + '</option>';
					}					
				}
				$('#select_spec').html(spec_str);
			});
			submitForm.find('input[name=\'goods_attr_id\']').val('');
			submitForm.find('input[name=\'goods_attr_name\']').val('');
			//阻止冒泡
	     	return false;
		});
		/* 动态改变属性 */
		$('#select_spec').on('change', function() {
			var obj = $(this).children('option:selected');
			if(parseInt(obj.val()) > 0) {
				submitForm.find('input[name=\'goods_attr_id\']').val(obj.val());
				submitForm.find('input[name=\'goods_attr_name\']').val(obj.text());
			} else {
				submitForm.find('input[name=\'goods_attr_id\']').val('');
				submitForm.find('input[name=\'goods_attr_name\']').val('');
			}
		});
	}	
	/* *************************对省市县进行操作********************************/	
	/* 是否显示省市县下拉框 */
	submitForm.find('input[name=\'origin_type\']').on('click', function() {
		if($(this).val() == 3) {
			$('td.place_select').show();
		} else {
			$('td.place_select').hide();
		}
	});
	/* 默认加载省份 */
	$.get(urls[1],{ id: params.prov_id }, function(data) {		
		prov_select.html(data);		
	})
	/* 改变省份选择城市 */
	prov_select.change(function() {
		origin_area = $(this).find("option:selected").text();
		// 读取市区列表
		$.get(urls[2], { id: (prov_select.val() || params.prov_id),city_id: params.city_id }, function(data) {
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
		$.get(urls[3], { id: (city_select.val() || params.city_id),area_id: params.area_id }, function(data) {
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
	$('#custom_btn').on('click', function() {
		var customName = submitForm.find('input[name=\'custom_name\']').val();
		if(!$.trim(customName) != '') {
			alert('请输入药材名称');return;
		}
		selectCate.children('span:first').text(customName);
		//去掉规格
		$('#select_spec').empty();
		selectCate.find('div.alert-wp').hide();
		//阻止冒泡
		return false;
	});
	/* ***********************结束对自定义药材进行操作******************************* */
	var repeat_click = true;
	/* 提交表单数据 */	
	$('#submitBtn').on('click', function(){		
		submitForm.find('input[name=\'origin_area\']').val(origin_area);
		$('#del-tip').text('确定编辑？');
		var dialog = $("div.base-modal"); 	
		dialog.show(0, function(){
			$('button.ok').off('click').on('click', function(){
				var _this = $(this);
				_this.addClass('disabled');
				dialog.hide();
				datas = submitForm.serialize();		
				$.ajax({
					url: urls[4],
					dataType: "json",		
					data: datas,
					type: "post",
					success: function(req) {
						if(req.code == 1) success(req.msg, req.url || true);
						else fail(req.msg, _this);
					},
					error: function() {			
						alert('网络连接超时，请稍后再试');
						_this.removeClass('disabled');
					}
				});
			});
		});
	});	
	init();
})
/* 初始化操作 */
function init(){	
	if(params.origin_type == 3){
		submitForm.find('input[name=\'origin_type\']').trigger('click');
		prov_select.trigger('change');
		city_select.trigger('change');		
	}
}
/* 生成有规律的分类药材 */
function createCates(){
	//输出分类，药材名称格式
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