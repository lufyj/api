/**
 * 公司动态处理操作
 * @Author jingwei
 * @Date 2016-10-10
 */

$(function() {

	var newsSub = true; //防止重复提交公司动态信息
	//动态添加编辑按钮
	$(".news-ul>li").hover(function() {
		var self = $(this);
		if(self.attr("data-id")) {
			self.css({
				"background-color": "#fff7ec"
			})
			self.append('<div class="op r op2"><span class="edit-s"><b></b> 编辑</span><span class="clear-s"><b></b> 删除</span></div>')
		}

	}, function() {
		var self = $(this);
		self.css({
			"background-color": "#fff"
		})
		self.find(".op2").remove();
	});
	//删除公司动态信息(单条)
	$(".news-ul>li").on('click', '.clear-s', function() {
		var self = $(this);
		var id = self.parents("li").attr('data-id');
		//self.parents("li").remove();
		$.confirm('确定要删除信息数据吗？', function(isConfirm) {
			if(isConfirm) {
				$.post("/CompanyNews/delInfo.html", {
					id: id
				},
				function(data) {
					if(data.status == 1) {
						$.custom('删除成功');
						setTimeout(function(){
							location.reload();
						},300);
					} else {
						$.custom('删除失败');
						return false;
					}
				}, "json");
			}
		}, '来自药都网的温馨提示：');
		/*if(window.confirm("确定要删除信息数据吗？")) {
			$.post("/CompanyNews/delInfo.html", {
					id: id
				},
				function(data) {
					if(data.status == 1) {
						$.custom('删除成功');
						location.reload();
					} else {
						$.custom('删除失败');
						return false;
					}
				}, "json");
		}*/
	});

	//清空添加表格信息
	$('.add-del').on('click', function() {
		$("#title").val("");
		$("#content").val("");
		$("#news-ad").val("");
		$("#news-img").attr("src", "/Public/Home/company/images/add_news.png");
	});

	//保存公司动态信息
	$('.add-save').on('click', function() {
		if(!newsSub) {
			return false;
		}
		var t = $("#title");
		var c = $("#content");
		var i = $("#news-ad");
		var title = t.val();
		var content = c.val();
		var img = i.val();

		if(title == '' || title == '请输入标题(最多输入30个中文)') {
			$.custom('请输入标题');
			return false;
		}

		if(title.length > 30) {
			$.custom('标题过长');
			return false;
		}

		newsSub = false;
		$.post("/CompanyNews/saveInfo.html", {
				title: title,
				content: content,
				img: img
			},
			function(data) {
				if(data.status == 1) {
					$.custom('保存成功');
					t.val('');
					c.val('');
					i.val('');
					location.reload();
				} else {
					$.custom('保存失败');
					newsSub = true;
					return false;
				}
			}, "json");

	});

	//编辑按钮事件
	$(".news-ul>li").on("click", ".edit-s", function() {
		var self = $(this);
		var id = self.parents("li").attr('data-id');
		var editBox = $(".news-empty");

		$.get("/CompanyNews/editInfo.html", {
				id: id
			},
			function(data) {
				if(data.status == 1) {
					editBox.find('#ed_title').val(data.info.title);
					editBox.find('#ed_content').val(data.info.content);
					if(data.info.img != '') {
						editBox.find('#edit-news-img').attr("src", data.info.img);
					}
					var inHidden = '<input type="hidden" name="ed_id" id="ed_id" value="' + data.info.id + '">';
					editBox.append(inHidden);
					//弹框出现
					editBox.show();
				} else {
					$.custom('编辑失败');
					return false;
				}
			});
	});

	//编辑时候确认按钮
	$(".edit-save").on("click", function() {
		//关闭弹框
		var self = $(this);
		var par = self.parents(".news-empty");
		var id = par.find("#ed_id").val();
		var title = par.find("#ed_title").val();
		var content = par.find("#ed_content").val();
		var img = par.find("#edit-news-ad").val();

		$.post("/CompanyNews/saveInfo.html", {
				id: id,
				title: title,
				content: content,
				img: img
			},
			function(data) {
				if(data.status == 1) {
					$.custom('保存成功');
					par.find("#ed_id").remove();
					par.find("#ed_title").val("");
					par.find("#ed_content").val("");
					par.find("#edit-news-ad").val("");
					par.hide();
					location.reload();
				} else {
					$.custom('保存失败');
					return false;
				}
			}, "json");
	});

	//编辑时候取消按钮
	$(".edit-del").on("click", function() {
		var self = $(this);
		var par = self.parents('.news-empty');
		par.find("#ed_title").val("");
		par.find("#ed_content").val("");
		par.find("#edit-news-ad").val("");
		par.find("#edit-news-img").attr("src", "/Public/Home/company/images/add_news.png");
		par.find("#ed_id").remove();
		par.hide();
	});
	// 关闭按钮
	$("b.close").on("click", function() {
		$(".news-empty").hide();
	});
	//关于弹出确定取消对话框的插件函数
	$.confirm = function(txt, callback) {
		var str = "<div class='modal modal-close' id='modal'><div class='modal-dialog'><div class='modal-content'>" +
			"<i class='custom-close'>&times;</i>" +
			"<p>" + (arguments[2] ? arguments[2] : "药都网温馨提示您：") + "</p>" +
			"<div class='modal-context'>" + txt + "</div>" +
			"<div class='context-a'>" +
			"<button class='custom-ok'>确定</button>" +
			"<a href='javascript:;' class='custom-close'>取消</a>" +
			"</div></div></div></div>";
		$('body').append(str);
		$('.custom-close,button.custom-ok').click(function(e) {
			if(e.preventDefault !== undefined) {
				e.preventDefault();
			} else {
				e.returnValue = false;
			}
			$('.modal-close').remove();
			if($(this).hasClass('custom-ok')) {
				typeof callback === 'function' && callback(true);
			} else {
				typeof callback === 'function' && callback(false);
			}
		});
	}
});