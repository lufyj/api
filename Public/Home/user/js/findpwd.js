$(function(){
	var repeat_click = true;
	//找回密码第一步
	$('#step1_btn').on('click', function(){		
		var username = $.trim($("#mobile").val());
		if(!checkMobile(username)) return;
		var code = $.trim($('#code').val());
		if(!code){
			$.custom('请输入验证码');
			return;
		}		
		if(!repeat_click) return;
		repeat_click = false;			
		$.ajax({
   		    url: urls[1],
   		    dataType: "json",   		   
   		    data: {
   		    	mobile:encodeURIComponent(username),
   		    	code:code
   		    },
   		    type: "post",   		    
   		    success: function(req) {
   		        if(req.code){
   		        	//回头在这里有个提示，验证通过，已向您手机发送验证码
   		        	window.location.href = urls[2];
   		        }else{
   		        	$.custom(req.msg);
   		        	//发生错误时，执行重新验证码，清除验证码文本框中的内容，重置可以再次点击
   		        	changeCode();
   		        	$('#code').val('');
   		        	repeat_click = true;
   		        }		       		        	        
   		    },		    
   		    error: function() {    
   		        $.custom('网络连接超时，请您稍后重试');
   		     	repeat_click = true;
   		    }
   		}); 
	});
	//找回密码第二步
	$('#step2_btn').on('click', function(){		
		var code = $.trim(codeObj.val());
		var pwd  = $.trim(pwdObj.val());
		var rpwd = $.trim(rpwdObj.val());
		if(!code){
			codeObj.focus();
			$.custom('请输入短信验证码');
			return;
		}
		if(pwd.length < 5 || pwd.length > 20){
			pwdObj.focus();
			$.custom('新密码最少6位，最多20位');
			return;
	    }
	    if(rpwd.length < 5 || rpwd.length > 20){
	    	rpwdObj.focus();
	    	$.custom('确认密码最少6位，最多20位');
	    	return;
	   	}
	    if(pwd != rpwd){
	    	$.custom('两次输入的密码不一致');
	    	return;
	    }
	    if(!repeat_click) return;		
	    repeat_click = false;
	    $.ajax({
   		    url: urls[0],
   		    dataType: "json",   		  
   		    data: {	code: code,pwd: pwd,rpwd: rpwd },
   		    type: "post",   		    
   		    success: function(req) {
   		        if(req.code){   		        	
   		        	window.location.href = urls[1];
   		        }else{ 	
   		        	$.custom(req.msg);
   		        	repeat_click = true;
   		        }		       		        	        
   		    },		    
   		    error: function() {    
   		        $.custom('网络连接超时，请您稍后重试');
   		     	repeat_click = true;
   		    }
   		}); 
	});
})
/* 切换验证码 */
function changeCode() {
	$('img.yanzmaimg').attr('src', urls[0]);
}
/* 验证手机是否符合规范 */
function checkMobile(username){
	if(!username){
		$('#error_tip').text('请输入您的手机号码').show();
		return;
	}
	if(!(username.match(/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/ig))){
		$('#error_tip').text('请输入正确手机号码').show();
		return;
  	}
	return true;
}
/* 失去焦点时触发的事件 */
function usernameOnblur(){	
	var username = $("#mobile").val();
	if(!username){
		$('#error_tip').text('请输入您的手机号码').show();
		return;
	}
	if(!checkMobile(username)){					
		return;
	}
	$('#error_tip').hide();				
}
/* 获得焦点时触发的事件 */
function usernameOnfocus(){
	var username = $("#mobile").val();
	if(username == "请输入您的手机号码"){
		$("#mobile").val("");
	}
	$('#error_tip').hide();
}
//关于自定义alert插件
jQuery.custom = function() {
	var str = "<div class='modal modal-close'><div class='modal-dialog'><div class='modal-content'>" +
			"<i class='custom-close'>&times;</i>" +
			"<p>" + (arguments[1] || '药都网温馨提示您') + "</p>" +
			"<div class='modal-context'>" + arguments[0] + "</div>" +
			"<div class='context-a'>" +
			"<button class='custom-ok'>确定</button>" +
			"</div></div></div></div>";
	$('body').append(str);
	$('i.custom-close,button.custom-ok').click(function() {
		$('.modal-close').remove();
	});
}