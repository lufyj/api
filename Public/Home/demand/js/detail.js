var _modal = $('#modal');
var repeat_click = true;
var _confirmModal = $('#confirmModal');
$(function() {
		/* 取消弹出的确定框 */
		_confirmModal.find('i.close,a.close').on('click', function() {
			_confirmModal.hide();
		});
		/* 删除图片 */
		$("#detail1").on("click", "div.del", function() {
			//出现添加图片按钮
			$(".up-btn1").show();
			//移除dom
			var self = $(this);
			self.parent("li").remove();
			//动态修改id
			$(".img-li1").each(function(index) {
				var a = index + 1
				var self = $(this);
				self.children("img").attr("id", "store-img" + a);
			});
			//删除数据库图片
		});
		/* 动态添加图片 */
		$("#file").change(function() {
			var i = $(".img-li1").length;
			if(i >= 5) {
				return false;
			} else {
				$("#detail-form").ajaxSubmit({
					type: 'post',
					url: urls[2],
					success: function(data) {
						if(typeof(data) == "object") {
							if(data.code) {
								var index = i + 1;
								var img = $('<li class="img-li1"><img id="detail-img' + index + '" /><div class="del"><span>删除</span></div><input name="st_img[]" type="hidden" id="detail-ad' + index + '"/></li>');
								$("#detail1").append(img);
								$("#detail-img" + index).attr("src", data.file[1]);
								$("#detail-ad" + index).val(data.file[1]);
								$('#detail-form').resetForm();
								if(i >= 4) {
									$(".up-btn1").hide();
								}
							} else {
								$.custom(data.msg);
							}
						} else {
							var datas = JSON.parse(data);
							if(datas.code) {
								var index = i + 1;
								var img = $('<li class="img-li1"><img id="detail-img' + index + '" /><div class="del"><span>删除</span></div><input name="st_img[]" type="hidden" id="detail-ad' + index + '"/></li>');
								$("#detail1").append(img);
								$("#detail-img" + index).attr("src", datas.file[1]);
								$("#detail-ad" + index).val(datas.file[1]);
								$('#detail-form').resetForm();
								if(i >= 4) {
									$(".up-btn1").hide();
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
	/* 选择谁中标 */
function chooseWin(tid) {
	_confirmModal.show(0, function() {
		var _this = $(this);
		_this.find('a.ok').off('click').on('click', function(e) {
			if(!repeat_click) {
				return;
			}
			repeat_click = false;
			_confirmModal.hide();
			$.ajax({
				url: urls[0],
				dataType: "json",
				data: {
					id: params.id,
					tid: tid
				},
				type: "post",
				success: function(req) {
					if(req.code == 1) {
						$.custom(req.msg);
						window.location.reload();
					} else {
						$.custom(req.msg);
					}
					repeat_click = true;
				},
				error: function() {
					$.custom('网络连接超时，请稍后再试');
				}
			});
		});
	});
}
var contact = $('.test-contact'),
	mobiles = $('.test-phone'),
	prices = $('.test-price');
var contact_d = $('#contacts'),
	mobiles_d = $('#mobile'),
	prices_d = $('#price');
function showTest(obj,msg){
	obj.html('<em></em>'+msg).fadeIn();
}
prices_d.focus(function(){
	prices.fadeOut();
});
contact_d.focus(function(){
	contact.fadeOut();
});
mobiles_d.focus(function(){
	mobiles.fadeOut();
});
//判断输入的价格是不是数字
prices_d.blur(function() {
	var txt = $.trim($(this).val());
	reg = /^\d+(\.\d{1,2})?$/;
	if(!txt){
		return;
	}
	if(!reg.test(txt) || txt <= 0 || txt > 9999) {
		/*$.custom('请输入正确的数字~');*/
		showTest(prices,'请输入正确的价格！（可选填）');
	}
});
/* 提交投标信息 */
function submitForm() {
	var contacts = $.trim($('#contacts').val());
	var mobile = $.trim($('#mobile').val());
	if(!contacts) {
		/*$.custom('请输入联系人');*/
		showTest(contact,'请输入联系人');
		return;
	}
	if(!mobile) {
		/*$.custom('请输入电话号码');*/
		showTest(mobiles,'请输入电话号码');
		return;
	}
	if(!/^1[34578]\d{9}$/.test(mobile)) {
		/*$.custom('请输入正确的电话号码');*/
		showTest(mobiles,'请输入正确的电话号码');
		return;
	}
	if(!repeat_click) return;
	repeat_click = false;
	//对图片进行处理
	var st_img = [];
	$('input[name="st_img[]"]').each(function() {
		st_img.push($(this).val());
	});
	var data = {
		price: $('#price').val(),
		contacts: $('#contacts').val(),
		mobile: $('#mobile').val(),
		remarks: $('#remarks').val(),
		id: params.id,
		imgs: st_img
	};
	
	$.ajax({
		url: urls[3],
		dataType: "json",
		data: data,
		type: "post",
		success: function(req) {
			if(req.code == 1) {
				$.custom(req.msg);
				window.location.reload();
			} else {
				$.custom(req.msg);
			}
			repeat_click = true;
		},
		error: function() {
			//alert('网络请求错误，请稍后再试~');
			repeat_click = true;
		}
	});
}
/* 关闭模态框 */
function cancelModal() {
	_modal.hide();
}
/* 点击投标 */
function toTender() {
	if(!params.user_sign) {
		window.location.href = urls[1] + '?redirectUrl=' + encodeURIComponent(window.location.href);
		return;
	}
	_modal.show();
}