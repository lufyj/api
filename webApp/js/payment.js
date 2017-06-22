/*支付保证金*/
var paySubmit = $('#paySubmit');//发布采购确定提交按钮
var paySelect = $('.pay-select>li');//选择转账、支付宝、不支付保证金
var moneySelA = $('.money-sel>a');//支付宝选择金额
var payZfbSel = $('.pay-zfb-sel');//显示支付宝金额选择的容器
var i = 5;//被选中的是几
var local = new LocalData();
var payObj = local.getData('supObj');
$(function(){
	moneySelA.on('click',function(){
		var _this = $(this);
		moneySelA.removeClass('active');
		_this.addClass('active');
	});
	paySelect.on('click',function(){
		var _this = $(this);
		var iActive = _this.find('dd>i');
		var pay_type = _this.attr('data-pay');
		if(_this.index()==i){
			return false;
		}
		i = _this.index();
		paySelect.each(function(){
			var self = $(this);
			self.find('dd>i').removeClass('icon-bx').addClass('icon-wx');
		});
		if(iActive.hasClass('icon-wx')){
			iActive.removeClass('icon-wx').addClass('icon-bx');
			payObj.pay_type = pay_type;
		}else{
			iActive.removeClass('icon-bx').addClass('icon-wx');
		}
		if(_this.index()==1){
			rule.showMsg(1,'支付宝正在完善中，敬请期待！',1000);
			payZfbSel.slideDown();
		}else{
			payZfbSel.slideUp();
		}
	});
	paySubmit.on('click',function(){
		if(payObj.pay_type==2){
			rule.showMsg(1,'支付宝正在完善中，敬请期待！',1000);
			return false;
		}
		$.postT(rule.root+'AppDemand/demand' , payObj , function (req){
			if(req.code == 103){
				var hrefUrl = "./login.html?ReturnUrl=" + encodeURIComponent(location.href);
				rule.showMsg(1 , "该账号已在其他地方登陆,请重新登录" , 2000 , hrefUrl);
			}
			if(req.code==126){
				rule.showMsg(1,"不是企业用户，不能发布毒麻类的药材！",2000);
			}
			if(req.code == 1){	
				rule.showMsg(1,'发布采购成功',1000);
				window.location.href="persional.html";
			}
		});
	});
});
