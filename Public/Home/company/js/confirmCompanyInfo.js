$(function() {
	//关于日历
	//加载时候检测date的默认
	var dates = $(".date");
	$('input[name="licContinue"]').each(function(index) {
		var self = $(this);
		if(self.is(":checked")) {
			if(index == 1) {
				dates.attr("disabled", "disabled").addClass("disabled");
			} else {
				dates.removeAttr("disabled", "disabled").removeClass("disabled");
			}
		}
	});
	$('input[name="licContinue"]').change(function() {
		$('input[name="licContinue"]').each(function(index) {
			var self = $(this);
			if(self.is(":checked")) {
				if(index == 1) {
					dates.attr("disabled", "disabled").addClass("disabled");
				} else {
					dates.removeAttr("disabled", "disabled").removeClass("disabled");
				}
			}
		});
	});
	//上传图片
	//切换营业执照和三照合一
	var img1 = $("input[name='mylicense']").val(),
		img2 = $("input[name='myorganization']").val(),
		img3 = $("input[name='mycomprehensive']").val(),
		myorganizationCode = $("input[name='myorganizationCode']").val(),
		mylicenseCode = $("input[name='mylicenseCode']").val(),
		mycomprehensiveCode = $("input[name='mycomprehensiveCode']").val();
	var license = $(".license-li"),
		domB = '<ul class="license-b"><li><span class="bf-s bf-s-a"><span> * </span>三证合一营业执照 :</span><div id="preview-div3"><img id="preview-img3" src="'+ img3 +'" style="width: 420px; height: 340px; border: solid 1px #d2e2e2;" alt="" /></div><br /><span class="at-s"><span> * </span>图片文件最大3MB，支持jpg、bmp、png的图片格式</span><br /><br /><label for="license-file3"><input type="file" name="comLic" id="license-file3" class="license-file"  size="20" /><span class="up-span">上传图片</span></label></li><li><span class="bf-s"><span> * </span>统一社会信用代码 :</span><input type="text" name="socialCreditCode" placeholder="统一社会信用代码" class="uscc" maxlength="18" value="'+ mycomprehensiveCode +'"/></li></ul>',
		domA = '<ul class="license-a"><li><span class="bf-s bf-s-a"><span> * </span>营业执照图片 :</span><div id="preview-div1"><img id="preview-img1" src="'+ img1 +'" style="width: 420px; height: 340px; border: solid 1px #d2e2e2;" alt="" /></div><br /><span class="at-s"><span> * </span>图片文件最大3MB，支持jpg、bmp、png的图片格式</span><br /><br /><label for="license-file1"><input type="file" id="license-file1" name="lic" class="license-file"  size="20" /><span class="up-span">上传图片</span></label></li><li><span class="bf-s"><span> * </span>营业执照注册号 :</span><input type="text" name="licNumber" placeholder="营业执照注册号" maxlength="15" class="blrn" value="'+ mylicenseCode +'"/></li><li><span class="bf-s bf-s-a"><span> * </span>组织机构代码证图片 :</span><div id="preview-div2"><img id="preview-img2" src="'+ img2 +'" style="width: 420px; height: 340px; border: solid 1px #d2e2e2;" alt="" /></div><br /><span class="at-s"><span> * </span>图片文件最大3MB，支持jpg、bmp、png的图片格式</span><br /><br /><label for="license-file2"><input type="file" id="license-file2" name="org" class="license-file" size="20" /><span class="up-span">上传图片</span></label></li><li><span class="bf-s"><span> * </span>组织机构代码 :</span><input type="text" name="orgCode" placeholder="组织机构代码" class="o-code" maxlength="10" value="'+ myorganizationCode +'"/></li></ul>';
	$('input[name="cerType"]').on("change", function() {
		$('input[name="cerType"]').each(function(index) {
			var self = $(this);
			if(self.is(":checked")) {
				if(index == 0) {
					license.html(domA);
				} else if(index == 1) {
					license.html(domB);
				}
			}
		});
	});
	//确定是否上传
	uploadImg("#license-file2" , "preview-img2" , "preview-div2");
	uploadImg("#license-file3" , "preview-img3" , "preview-div3");
	uploadImg("#license-file1" , "preview-img1" , "preview-div1");
	function  uploadImg(obj , imgId , divID) {
		$(".license-li").on("change", obj, function() {
			PreviewImage(this, imgId, divID);
		});
	}
	//营业日期限制
	$(".btn-li>span").on("click", function() {
		var companyName = $(".company-name").val(),
			licensePlace = $(".license-place").val(),
			licContinueBegin = $(".date1").val(),
			licContinueEnd  = $(".date2").val(),
			CommonAddress = $(".common-address").val(),
			tel = $(".mobile").val();
		var regTel = /^((0\d{2,3}[-_]\d{7,8})|1[34578]\d{9})$/;
		if ( companyName == ""){
			$.custom('请输入公司名字');
			return false;
		}else if ( licensePlace == ""){
			$.custom('营业执照所在地');
			return false;
		}else if ($('input:checked.radio-date').val() == 1 && (licContinueBegin == "" || licContinueEnd == "")){
			$.custom('请输入营业期限');
			return false;
		}else if (CommonAddress == ""){
			$.custom('请输入常用地址');
			return false;
		}else if (tel == "" || !regTel.test(tel)){
			$.custom('联系电话输入不正确，请重新输入');
			return false;
		}else if($("ul.license-a").length >= 1) {
			var reg1 = /^\d{15}$/;
			var reg2 = /[A-Z0-9]{8}-[A-Z0-9]{1}/;
			var licenseFile = $("#license-file1").val(),
				organizationFile = $("#license-file2").val();
			if (licenseFile == "" && img1 == "/Public/Home/company/images/example_1.png"){
				$.custom('请上传营业执照图片');
				return false;
			}else if(!reg1.test($(".blrn").val())) {
				$.custom('营业执照注册号错误，请重新输入');
				return false;
			} else if (organizationFile == "" && img2 == "/Public/Home/company/images/example_2.png"){
				$.custom('请上传组织机构代码图片');
				return false;
			}else if (!reg2.test($(".o-code").val())){
				$.custom('组织机构代码错误，请重新输入');
				return false;
			}
			//避免特殊bug
			if (licenseFile == "" && img1 == ""){
				$.custom('请上传营业执照图片');
				return false;
			}else if (organizationFile == "" && img2 == ""){
				$.custom('请上传组织机构代码图片');
				return false;
			}

			
		} else {
			//统一社会信用
			var reg3 = /[A-Z0-9]{18}/;
			var comprehensive = $("#license-file3").val();
			if (comprehensive == "" && img3 == '/Public/Home/company/images/example_3.png'){
				$.custom('请上传三证合一营业执照图片');
				return false;
			}else if(!reg3.test($(".uscc").val())) {
				$.custom('统一社会信用代码错误，请重新输入');
				return false;
			}
		}
		var j_money = $('input[name="regCapital"]').val();
		if (j_money && isNaN(j_money)){
			$.custom('资金格式不正确');
			return false;
		}
		$("#myform").ajaxSubmit({
			type: 'post',
			url: '/CompanyConfirm/saveStep',
			success: function(data) {
				if (typeof(data) == "object"){
					datas = data;
				}else{
					var datas = JSON.parse(data);
				}
				if (datas.path){
					window.location.href = datas.path;
				}else{
					$.custom(datas.msg);
				}
			}
		})
	});

	//点击上一步
	$(".pre").click(function() {
		window.location.href = "/CompanyConfirm/confirmInfo.html?go=1";
	});
	//账户信息页面
	$(".sub-btn").click(function() {
		var accountName = $.trim($(".account-name").val()),
			bankName = $.trim($(".bank-name").val()),
			bankCity = $.trim($(".bank-city").val()),
			bankBranch = $.trim($(".bank-branch").val()),
			publicAccount = $.trim($(".public-account").val()),
		mystep = $("#mystep").val();
					//验证对公账户
		var reg = /^\d{12,23}$/;
		if(publicAccount != "") {
			//判断 其他是否为空
			if(!reg.test(publicAccount)) {
				$.custom('对公账户输入错误，请重新输入');
				return false;
			}else if(accountName == "") {
				$.custom("请输入银行开户名");
				return false;
			} else if(bankName == "") {
				$.custom("请输入开户银行");
				return false;
			} else if(bankCity == "") {
				$.custom('请输入开户行所在城市');
				return false;
			} else if(bankBranch == "") {
				$.custom('请输入开户行支行名称');
				return false;
			}
		}
		var params = {
			countName: accountName,
			bankName: bankName,
			bankCity: bankCity,
			bankBranch: bankBranch,
			countNum: publicAccount,
			step: mystep
		}
		$.post("/CompanyConfirm/saveStep.html", params, function(data) {
			var datas = JSON.parse(data);
			if(datas.status == 1) {
				window.location.href = datas.path;
			} else {
				$.custom('提交申请失败');
				return false;
			}
		});
	});

});