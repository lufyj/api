supObj={};//发布采购的对象
var supName = $('#sup-name');//获取品名
var supNorms = $('#sup-norms');//获取规格
var supNum = $('#sup-num');//获取输入的公斤
var supAddS = $('#sup-addS');//获取产地
var	supContact = $('#sup-contact');//获取联系人
var supMobile = $('#sup-mobile');//获取手机号
var supDetail = $('#sup-detail');//获取备注
var supplySubmit = $('#demandSubmit');//点击发布验证逻辑并提交
var local = new LocalData();
supMobile.val(local.getData('user_info').mobile);//页面中放入用户的手机号
$(function(){
	autoTextarea(supDetail[0]);
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
		supName.val('');
		supName.prev().text('请选择产品名称');
	}
	$('.sup-norms').on('click',function(){
		var body = document.getElementsByTagName('body')[0];
		var p = $(this).find('p');
		if(window.location.href.lastIndexOf('status')>-1){
			window.location.href = './attr.html?id='+goodsId+'&ReturnUrl=demand.html';
		}else{
			supModal(p,'暂无内容',body);
		}
	});
	supplySubmit.on('click',function(){
		if(!supName.val()){
			rule.showMsg(1,'请选择品名',1000);
			return;
		}
		if(!supNorms.val()){
			rule.showMsg(1,'请选择规格',1000);
			return;
		}
		var supNumVal = $.trim(supNum.val());//验证数量
		if(!supNumVal || supNumVal<=0 || !/^\d{1,7}$/.test(supNumVal)){
			rule.showMsg(1,'请输入公斤',1000);
			supNum.val('');
			return;
		}
		supObj.num = supNumVal;
		if($('#area').text() == '请选择产地'){
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
		supObj.order_type = 5;//判定自己是web pc端
		//提交表单
		local.setData('supObj',supObj);
		window.location.href="./payment.html";//跳转支付保证金页面进行发布采购提交
	});
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
			txt.html('');
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