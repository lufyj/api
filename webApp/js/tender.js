var local = new LocalData();
$('#ten-name').val(local.getData('mytender').goods_name);
$('#ten-norms').val(local.getData('mytender').goods_attr);
$('#ten-num').val(local.getData('mytender').goods_num);
$('#ten-contact').val(local.getData('user_info').realname);
$('#ten-mobile').val(local.getData('user_info').mobile);
var tenderSubmit = $('#tenderSubmit');//发布投标按钮
var repeat_click = true;//防止重复点击
var tenPrice = $('#ten-price');//输入单价
var tenContact = $('#ten-contact');//获取联系人
var tenMobile = $('#ten-mobile');//获取手机号
var tenImg = $('#ten-img');//上传图片按钮
var tenImgs = $('.ten-imgs');//放置图片的容器
var tenDetail = $('#ten-detail');//详细说明
var imgsL = 0;//判断已经添加了几张图片了
var isClick = false;//判断是否可以删除图片
var tenObj = {};//上传对象
tenObj.demand_id = local.getData('mytender').id;
$(function(){
	autoTextarea(tenDetail[0]);
	tenderSubmit.on('click',function(){
		if(!repeat_click){
			return false;
		}
		var tenPriceV = $.trim(tenPrice.val());
		if(!tenPriceV){
			rule.showMsg(1,'请输入价格！',1000);
			return false;
		}
		if(!/^[1-9]\d{0,4}(\.\d{1,2})?$/.test(tenPriceV)){
			rule.showMsg(1,'请确认价格！',1000);
			return false;
		}
		tenObj.price = tenPriceV;
		var tenContactV = $.trim(tenContact.val());
		if(!tenContactV){
			rule.showMsg(1,'必须填入联系人的姓名！',1000);
			return false;
		}
		tenObj.contacts = tenContactV;
		var tenMobileV = $.trim(tenMobile.val());
		if(!tenMobileV){
			rule.showMsg(1,'必须填入联系人的手机号！',1000);
			return false;
		}
		tenObj.mobile = tenMobileV;
		var tenDetailV = $.trim(tenDetail.val());
		if(!tenDetailV){
			rule.showMsg(1,'请填入详细说明！',1000);
			return false;
		}
		if(tenDetailV.length>256){
			rule.showMsg(1,'详细说明最多填入255个字！',1000);
			return false;
		}
		tenObj.remarks = tenDetailV;
		var arr = [];//存储图片的路径
		tenImgs.find('img').each(function(){
			arr.push(this.src);
		});
		if(arr){
			tenObj.imgs = arr.join(',');
		}
		//提交表单
		$.postT(rule.root+'AppDemand/tender' , tenObj , function (req){
			if(req.code == 103){
				var hrefUrl = "./login.html?ReturnUrl=" + encodeURIComponent(location.href);
				rule.showMsg(1 , "该账号已在其他地方登陆,请重新登录" , 2000 , hrefUrl);
			}
			if(req.code == 1){	
				rule.showMsg(1,'发布供应成功',1000);
				window.location.href="persional.html";
			}
		});
	});
	//上传图片预览
	tenImg.on('change',function(){
		$('#submitForm').ajaxSubmit({
			type: 'post',
			url: rule.root+"AppDemand/upload_pic",
			success: function(req) {
				if(req.code==1){
					var data = req.data;
					var l = data.length;
					var html = '';
					imgsL+=l;
					if(imgsL>=5){
						rule.showMsg(1,'发布的行情图片最多上传5张',1000);
						$('.sup-imgs-sc').hide();
					}else{
						$('.sup-imgs-sc').show();
					}
					html='<img src="'+data+'">';
					tenImgs.prepend(html);
					isClick = true;
				}
			},
			error:function(){
				rule.showMsg(1,'图片加载失败请重试',1000);
			}
		});
	});
	//删除图片
	tenImgs.on('click','img',function(){
		var _this = $(this);
		if(isClick){
			_this.remove();
		}
		var len = tenImgs.find('img').length;
		imgsL = len;
		if(len<5){
			tenImgs.find('.sup-imgs-sc').show();
		}
	});
});