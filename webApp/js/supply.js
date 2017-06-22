supObj={};//发布供应的对象
var supName = $('#sup-name');//获取品名
var supNorms = $('#sup-norms');//获取规格
var supNum = $('#sup-num');//获取输入的公斤
var supNum2 = $('#sup-num2');//获取公斤的固定值"大货"
var supPrice = $('#sup-price');//获取输入的单价
var supPrice2 = $('#sup-price2');//获取单价的固定值"面议"
var supAddS = $('#sup-addS');//获取产地
var supAddE = $('#sup-addE');//获取货源地
var	supContact = $('#sup-contact');//获取联系人
var supMobile = $('#sup-mobile');//获取手机号
var supDetail = $('#sup-detail');//获取备注
var supplySubmit = $('#supplySubmit');//点击发布验证逻辑并提交
var supImgs = $('.sup-imgs');//放置图片的容器
var imgsL = 0;//记录图片的数量
var local = new LocalData();
var imgss = {}
var i = 0;//判断图片的个数
supMobile.val(local.getData('user_info').mobile);//页面中放入用户的手机号
$(function(){
	autoTextarea(supDetail[0]);
	if(local.getData('hasImg')){
		var localImgs = local.getData('imgs');
		for(var key in localImgs){
			supImgs.prepend('<img data-i="'+key+'" src="'+localImgs[key][0]+'">');
			imgss[key] = [localImgs[key][0]];
			i = key;
		}
		imgsL = supImgs.find('img').length;
		if(imgsL>=5){
			supImgs.find('.sup-imgs-sc').hide();
		}
	}
	$('.supply li:nth-child(2)').click(function(){
		var  hasImg= supImgs.find('img').length;
		if(hasImg){
			local.setData('hasImg',true)
		}
		hasImg?local.setData('hasImg',true):localStorage.removeItem('hasImg');
		window.location.href="./cate.html?ReturnUrl=supply.html";
	});
	//选择产地
	$('.sup-addS').on('click',function(){
		var body = document.getElementsByTagName('body')[0];
		var p = $(this).find('p');
		var html = '<ul class="norms"><li data-ortype="2">进口</li><li data-ortype="1">较广</li><li data-ortype="3"><input type="text" id="area1" value="国内"></li></ul>';
		supModal(p,html,body);
		var area = new LArea();
	    area.init({
	        'trigger': '#area1',
	        'finishs' :'#area',
	        'valueTo': '#sup-addS',
	        'keys': {
	            id: 'value',
	            name: 'text'
	        },
	        'type': 2,
	       	'data': [provs_data, citys_data, dists_data]
	    });
	});
	//上传图片预览
	$('#sup-img').on('change',function(){
		$('#submitForm').ajaxSubmit({
			type: 'post',
			url: rule.root+"AppSupply/upload_pic",
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
					html='<img data-i="'+(++i)+'" src="'+data+'">';
					supImgs.prepend(html);
					imgss[i] = data;
					local.setData("imgs",imgss);//添加图片local
				}
			},
			error:function(){
				rule.showMsg(1,'图片加载失败请重试',1000);
			}
		});
	});
	//删除图片
	$('.sup-imgs').on('click','img',function(){
		var arrImg = [];
		var _this = $(this);
		var dataI = _this.attr('data-i');
		_this.remove();
		imgss = local.getData('imgs');
		delete imgss[dataI];//删除图片local
		local.setData('imgs',imgss);
		var len = supImgs.find('img').length;
		imgsL = len;
		if(len<5){
			supImgs.find('.sup-imgs-sc').show();
		}
	});
	//获取药材品名
	if(window.location.href.lastIndexOf('status')>-1){
		var goodsName = local.getData("goods").goods_name;
		var goodsId = local.getData("goods").goods_id;
		supName.val(goodsId);
		supName.prev().text(goodsName);
		supObj.goods_name = goodsName;
		supObj.goods_id = goodsId;
		supObj.cate_name = local.getData('goods').cate_name;
		supObj.cate_id = local.getData('goods').cate_id;
		var attrs = local.getData('goods').goods_attr;
		if(attrs){
			supObj.goods_attr_name = attrs;
			supNorms.val(attrs);
			supNorms.prev().text(attrs);
		}
	}else{
		supImgs.find('img').remove();
		supName.val('');
		supName.prev().text('请选择产品名称');
		if(local.getData('imgs') || local.getData('hasImg')){
    		localStorage.removeItem('imgs');
    		localStorage.removeItem('hasImg');
        }
	}
	$('.sup-norms').on('click',function(){
		var body = document.getElementsByTagName('body')[0];
		var p = $(this).find('p');
		if(window.location.href.lastIndexOf('status')>-1){
			var  hasImg= supImgs.find('img').length;
			hasImg?local.setData('hasImg',true):localStorage.removeItem('hasImg');
			window.location.href = './attr.html?id='+goodsId+'&ReturnUrl=supply.html';
		}else{
			supModal(p,'暂无内容',body);
		}
	});
	//获取货源地
	var area2 = new LArea();
    area2.init({
        'trigger': '#area2',
        'finishs':'#area2',
        'valueTo': '#sup-addE',
        'keys': {
            id: 'value',
            name: 'text'
        },
        'type': 2,
       	'data': [provs_data, citys_data, dists_data]
    });
	
	//判断是否需要获取公斤的固定值"大货" 或 获取单价的固定值"面议"
	$('#sup-num2,#sup-price2').on('click',function(){
		var _this = $(this);
		_this.toggleClass('sup-dm');
		if(_this.hasClass('sup-dm')){
			_this.prev().val('');
		}
	});
	supNum.on('focus',function(){
		this.value = '';
		supNum2.hasClass('sup-dm') &&　supNum2.removeClass('sup-dm');
	});
	supPrice.on('focus',function(){
		this.value = '';
		supPrice2.hasClass('sup-dm') &&　supPrice2.removeClass('sup-dm');
	});
	supplySubmit.on('click',function(){
		var arrImg = [];//存储图片地址
		supImgs.find('img').each(function(){
			arrImg.push(this.src);
		});
		if(!supName.val()){
			rule.showMsg(1,'请选择品名',1000);
			return;
		}
		if(!supNorms.val()){
			rule.showMsg(1,'请选择规格',1000);
			return;
		}
		var supNumVal = $.trim(supNum.val());//验证数量
		if(!supNum2.hasClass('sup-dm')){
			if(!supNumVal || supNumVal<=0 || !/^\d{1,7}$/.test(supNumVal)){
				rule.showMsg(1,'请输入公斤',1000);
				supNum.val('');
				return;
			}
			supObj.num = supNumVal;
		}else{
			supNum.val('');
			supObj.num = supNum2.val();
		}
		var supPriceVal = $.trim(supPrice.val());//验证单价
		if(!supPrice2.hasClass('sup-dm')){
			if(!supPriceVal || supPriceVal<=0 || !/^[1-9]\d{0,7}(\.\d{1,2})?$/.test(supPriceVal)){
				rule.showMsg(1,'请输入单价',1000);
				supPrice.val('');
				return;
			}
			supObj.price = supPriceVal;
			supObj.price_type = 1;
		}else{
			supPrice.val('');
			supObj.price = supPrice2.val();
			supObj.price_type = 2;
		}
		if($('#area').text() == '请选择产地' || $('#area').text()==''){
			rule.showMsg(1,'请输入产地',1000);
			return;
		}
		if(supObj.origin_type == 3){
			supObj.origin_area = $.trim($('#area').text());
			supObj.origin_code = $('input[name="origin_code"]').val();
		}else{
			if(supObj.origin_area){
				delete supObj.origin_area;
			}
			if(supObj.origin_code){
				delete supObj.origin_code;
			}
		}
		if($('#area2').text() == '请选择货源地'){
			rule.showMsg(1,'请输入货源地',1000);
			return;
		}
		//将货源地的地址输入到supObj的对象中
		supObj.supply_area = $.trim($('#area2').text());
		supObj.supply_code = $('input[name="supply_code"]').val();
		var supContactV = $.trim(supContact.val());
		if(!supContactV){
			rule.showMsg(1,'请输入联系人',1000);
			return;
		}
		supObj.contacts = supContactV;
		var supMobileV = $.trim(supMobile.val());
		if(!supMobileV || !/^1[34578]\d{9}$/.test(supMobileV)){
			rule.showMsg(1,'请输入手机号',1000);
			return;
		}
		supObj.mobile = supMobileV;
		var supDetailV = $.trim(supDetail.val());
		if(!supDetailV){
			rule.showMsg(1,'请输入备注',1000);
			return;
		}
		if(supDetailV.length>200){
			rule.showMsg(1,'请输入备注的长度不超过200个字',1000);
			return;
		}
		supObj.details = supDetailV;
		if(arrImg){
			supObj.pic = arrImg.join(',');
		}
		//提交表单
		$.postT(rule.root+'AppSupply/supply' , supObj , function (req){
			if(req.code == 103){
				var hrefUrl = "./login.html?ReturnUrl=" + encodeURIComponent(location.href);
				rule.showMsg(1 , "该账号已在其他地方登陆,请重新登录" , 2000 , hrefUrl);
			}
			if(req.code==126){
				rule.showMsg(1,"不是企业用户，不能发布毒麻类的药材！",2000);
			}
			if(req.code == 1){	
				rule.showMsg(1,'发布供应成功',1000);
				window.location.href="persional.html";
			}
		});
	})
});
/*显示模态框*/
function supModal(txt,html,obj){
	var str = '';
	str+='<div class="modals"><div class="modal-dialogs">'+html+'</div></div>';
	$(obj).append(str);
	var modal = $('.modals');//模态框
	//点击事件
	$('.norms').on('click','li',function(){
		var _this = $(this);
		var type = _this.attr('data-ortype');
		var index = _this.index();
		if(index<2){
			txt.html(_this.text());
			txt.next().val(_this.text());
			supObj.origin_type=type;
			modal.remove();
		}else if(index==2){
			txt.html('请选择产地');
			txt.next().val('');
			supObj.origin_type=type;
			modal.remove();
		}
	});
	modal.on('click',function(){
		var _this = $(this);
		_this.remove();
		return false;
	});
}