var repeat_click = true;
var _confirmModal = $('#confirmModal');
$(function(){	 
	/* 取消弹出的确定框 */
	_confirmModal.find('i.close,a.close').on('click', function(){
		_confirmModal.hide();		
	});	
})
/* 确定发货 */
function deliveryGoods(id,tid){	
	_confirmModal.show(0, function(){
		var _this = $(this);
		_this.find('a.ok').off('click').on('click',function(e){
			if(!repeat_click) { return; }
			repeat_click = false;
			_confirmModal.hide();
			$.ajax({
	  		    url: urls[0],
	  		    dataType: "json",     		   
	  		    data: {id: id,tid: tid,inx: 2},
	  		    type: "post",       		   
	  		    success: function(req) {       		    
	  		        if(req.code == 1){
	  		        	$.custom(req.msg);
	  		        	window.location.reload();
	  		        }else{
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