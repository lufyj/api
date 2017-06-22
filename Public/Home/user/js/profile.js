$(function(){
	var span_click = true; //重复标示
	$('#submitBtn').on('click', function(){
		if(!span_click) return;
		span_click = false;
		var options = {
	        url: urls[0],	      
	        type: "post", //请求方式
	        dataType: "json", //返回格式为json
	        success: function (req) {
	            $.custom(req.msg);
	            repeat_click = true;
	        },
	        error: function(XmlHttpRequest, textStatus, errorThrown){  
	        	repeat_click = true;
	        	$.custom('网络连接超时，请稍后再试');
	        }
	   };
	    $("#submit-form").ajaxForm(options);
	});
})

function saveForm() {
	if(!span_click) return;
	span_click = false;
	var options = {
        url: urls[0],
        data: {
        	realname: $.trim($('#realname').val()),
        	email: $.trim($('#email').val()),
        	weixin: $.trim($('#weixin').val()),
        	qq: $.trim($('#qq').val()),
        	address: $.trim($('#address').val())
        },
      /*  type: "post", //请求方式
        dataType: "json", //返回格式为json
*/        success: function (req) {
            $.custom(req.msg);
            repeat_click = true;
        },
        error: function(XmlHttpRequest, textStatus, errorThrown){  
        	repeat_click = true;
        	$.custom('网络连接超时，请稍后再试');
        }
   };
    $("#submit-form").ajaxForm(options);
}
//关于上传图片预览的问题
//js本地图片预览，兼容ie[6-9]、火狐、Chrome17+、Opera11+、Maxthon3
function PreviewImage(fileObj, imgPreviewId, divPreviewId) {
	var allowExtention = ".jpg,.bmp,.gif,.png"; //允许上传文件的后缀名document.getElementById("hfAllowPicSuffix").value;
	var extention = fileObj.value.substring(fileObj.value.lastIndexOf(".") + 1).toLowerCase();
	var browserVersion = window.navigator.userAgent.toUpperCase();
	if(allowExtention.indexOf(extention) > -1) {
		if(fileObj.files) { //HTML5实现预览，兼容chrome、火狐7+等
			if(window.FileReader) {
				var reader = new FileReader();
				reader.onload = function(e) {
					document.getElementById(imgPreviewId).setAttribute("src", e.target.result);
				}
				reader.readAsDataURL(fileObj.files[0]);
			} else if(browserVersion.indexOf("SAFARI") > -1) {
				$.custom('不支持Safari6.0以下浏览器的图片预览!');
			}
		} else if(browserVersion.indexOf("MSIE") > -1) {
			fileObj.select();
			if(browserVersion.indexOf("MSIE 9") > -1)
				fileObj.blur(); //不加上document.selection.createRange().text在ie9会拒绝访问
			var newPreview = document.getElementById(divPreviewId + "New");
			if(newPreview == null) {
				newPreview = document.createElement("div");
				newPreview.setAttribute("id", divPreviewId + "New");
				newPreview.style.width = document.getElementById(imgPreviewId).width + "px";
				newPreview.style.height = document.getElementById(imgPreviewId).height + "px";
				newPreview.style.border = "solid 1px #d2e2e2";
			}
			newPreview.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src='" + document.selection.createRange().text + "')";
			var tempDivPreview = document.getElementById(divPreviewId);
			tempDivPreview.parentNode.insertBefore(newPreview, tempDivPreview);
			tempDivPreview.style.display = "none";
		}
	} else {
		$.custom("仅支持" + allowExtention + "为后缀名的文件!");
		fileObj.value = ""; //清空选中文件
		if(browserVersion.indexOf("MSIE") > -1) {
			fileObj.select();
			document.selection.clear();
		}
		fileObj.outerHTML = fileObj.outerHTML;
	}
	return fileObj.value; //返回路径
}