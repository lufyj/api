var demand_id = 0;
var _confirmModal = $('#confirmModal');
var repeat_click = true;	
$(function(){
	/* 取消弹出的确定框 */
	_confirmModal.find('i.close,a.close').on('click', function(){
		_confirmModal.hide();		
	});
	/* 选择支付宝的样式替换 */
	$('ul.alipay01-sel input:radio').on('click', function() {
		var _this = $(this);
		if(_this.prop('checked')){
			_this.parent().addClass('active').siblings().removeClass('active');
		}
		/*if(_this.prop('checked')) {
			_this.prev('i').css('background', 'url(/Public/Home/user/images/icon01.png) no-repeat center center');			
			_this.parent().addClass('active').siblings().removeClass('active');
		} else {			
			_this.prev('i').css('background', 'none');
			_this.parent().removeClass('active');
		}	*/	
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
	/* 关闭支付对话框 */
	$('#modal i.i-close').on('click', function(){
		$('#modal').hide();
	});	
	/* 提交支付表单数据 */
	$('#submitBtn').on('click', function(){		
		var payType = $("input[name='payType']:checked").val();
		if(isNaN(parseInt(payType))){
			$.custom('请选择一种支付方式');return;
		}
		//在这里判断是采用哪种方式
		if(!$('#agreen').attr('checked')){
			$.custom('请同意协议并勾选');return;
		}		
		if(isNaN(parseInt(demand_id)) && demand_id <= 0){
			$.custom('非法操作');return;
		}
		if(!repeat_click) { return; }
		repeat_click = false;
		$.ajax({
			url: urls[1],
			dataType: "json",		
			data: { id: demand_id,type: payType,inx:1 },
			type: "post",	
			success: function(req) {
				if(req.code == 1){
					$.custom(req.msg);
					window.location.reload();
				}else{
					$.custom(req.msg);
				}
			},
			error: function() {			
				$.custom('网络连接超时，请稍后再试');
			}
		});
	});
})
/* 弹出支付托管资金模态框 */
function payModal(id){
	id = parseInt(id);
	if(isNaN(id) || id <= 0) return;
	$.ajax({
	    url: urls[0],
	    dataType: "json",		   
	    data: {id: id},
	    type: "get",       		   
	    success: function(req) {	    
	        if(req.code == 1){
	        	var data = req.msg;
	        	var sb =  '<li><span>联&nbsp;&nbsp;系&nbsp;&nbsp;人：</span>'+ data.contacts +'</li>'
	        			+ '<li><span>联系电话：</span>'+ data.mobile +'</li>'
	        			+ '<li><span>价&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;格：</span><span class="color-r">'+ data.price +'元/公斤</span></li>'
	        			+ '<li><span>详细说明：</span>'+ data.remarks +'</li>';
	            $modal = $('#modal');
	            $modal.find('ul.tender').html(sb);
	        	$('#modal').show();
	        	demand_id = id;
	        }else{
	        	$.custom(req.msg);
	        }	        
	    },		    
	    error: function() {        
	        $.custom('网络连接超时，请稍后再试');
	    }
	});    
}
/* 确认签收 */
function confirmSign(id){
	id = parseInt(id);
	if(isNaN(id) || id <= 0) return;
	_confirmModal.show(0, function(){
		var _this = $(this);
		_this.find('a.ok').off('click').on('click',function(e){
			if(!repeat_click) { return; }
			repeat_click = false;
			_confirmModal.hide();
			$.ajax({
			    url: urls[1],
			    dataType: "json",		   
			    data: {id: id,inx: 3},
			    type: "post",       		   
			    success: function(req) {       		    
			        if(req.code == 1){
			        	$.custom(req.msg);
			        	window.location.reload();
			        }else{
			        	$.custom(req.msg);
			        }	        
			    },		    
			    error: function() {
			        $.custom('网络连接超时，请稍后再试');
			    }
			});
		});
	});	
}