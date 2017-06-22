/**
 * 公司业务处理操作
 * @Author jingwei
 * @Date 2016-10-10
 */

$(function(){
	var  store = $("#preview5").html();
	var packing = $("#preview6").html();
    var storeSub=true;//防止文本信息重复提交
    var deliverySub=true;//防止文本信息重复提交
    var packingSub=true;//防止文本信息重复提交
    var processSub=true;//防止文本信息重复提交
    var checkSub=true;//防止文本信息重复提交
    /***删除操作(公共)**/
    //删除公司业务信息(单条)
    $(".bsns-ul ul").on('click','.clear-s',function(){

        var self=$(this);
        var type=self.parents("li").attr('data-business');
        var id=self.parents("li").attr('data-id');

        if(confirm("确定要删除信息数据吗？")){
            $.post("/Company/delInfo.html",{type:type,id:id},
                function(data){
                    if(data.status==1){
                        $.custom('删除成功');
                        location.reload();
                    }else{
                        $.custom('删除失败');
                        return false;
                    }
                }, "json");
        }
    });

    //弹出编辑框
    $(".bsns-ul ul").on('click', ".edit-s" , function () {
        var self=$(this);
        var id=self.parents('li').attr('data-id');
        var business=self.parents('li').attr('data-business');
        var url='';
        var i =  $(this).parents("li").parents("li").index();
        var dataBox= $(".empty .alert-wrap>div").eq(i);
        if(business=='store'){
            url='/CompanyStore/editInfo.html';
        }else if(business=='delivery'){
            url='/CompanyDelivery/editInfo.html';
        }else if(business=='packing'){
            url='/CompanyPacking/editInfo.html';
        }else if(business=='process'){
            url='/CompanyProcess/editInfo.html';
        }else if(business=='check'){
            url='/CompanyCheck/editInfo.html';
        }else{
            return false;
        }

        $.get(url,{id:id},function(data){
            if(data.status==1){
            	 $(".empty").show();
        		$(".empty .alert-wrap>div").eq(i).show();
                if(business=='store'){
                    storeData(dataBox,data.info);
                }else if(business=='delivery'){
                    deliveryData(dataBox,data.info);
                }else if(business=='packing'){
                    packingData(dataBox,data.info);
                }else if(business=='process'){
                    processData(dataBox,data.info);
                }else if(business=='check'){
                    checkData(dataBox,data.info);
                }else{
                    return false;
                }
            }else{
                $.custom('编辑失败');
                return false;
            }
        });
	
    });


    /********仓库信息操作开始********/

    //动态获取城市
    $("#st_provice").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getCityHtml", {id:id},function(data){
            $("#st_city").html(data);
        });
        //县级恢复默认
        $("#st_dist").html('<option selected="selected">请选择区/县</option>');
    });

    //动态获取区县
    $("#st_city").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getAreaHtml", {id:id},function(data){
            $("#st_dist").html(data);
        });
    });

    //保存仓库信息
    $("#st_save").on('click',function(){
        if(!storeSub){
            return false;
        }
        var self=$(this);
        var url='/CompanyStore/saveInfo.html';
        var st_type=0;
        $(".st_type").each(function(index){
            var chk=$(this).attr('checked');
            if(chk=='checked'){
                st_type=$(this).val();
            }
        });
        var st_size=$.trim($("#st_size").val());
        var st_height=$.trim($("#st_height").val());
        var st_provice=$("#st_provice").val();
        var st_city=$("#st_city").val();
        var st_dist=$("#st_dist").val();
        var st_address=$.trim($("#st_address").val());
        var st_contacts=$.trim($("#st_contacts").val());
        var st_mobile=$.trim($("#st_mobile").val());
        var st_desc=$.trim($("#st_desc").val());
        var st_id = $("#st_id").val();
        //隐藏域value
        var st_img = [];
        var reg = /^\d+(\.\d{1,2})?$/;
        var regTel = /^1[34578]\d{9}$/;
        $('input[name="st_img[]"]').each(function (){
        	st_img.push($(this).val());
        });
        if(st_type==''){
            $.custom('请选择仓库类型');
            return false;
        }
        if(st_size=='' || st_size == '请输入数字'){
            $.custom('请输入仓库面积');
            return false;
        }else if (!reg.test(st_size)){
        	$.custom('请输入正数，最多保留两位小数');
            return false;
        }
        if (st_height == '' || st_height == '请输入数字'){
        	 $.custom('请输入仓库层高');
            return false;
        }else if (!reg.test(st_height)){
        	$.custom('请输入正数，最多保留两位小数');
            return false;
        }
        if(st_address=='' || st_address == '请输入详细地址(最多30个中文)'){
            $.custom('请输入仓库详细地址');
            return false;
        }
        if(st_address.length>30){
            $.custom('地址信息过长');
            return false;
        }
        if(st_contacts=='' || st_contacts == '请输入联系人'){
            $.custom('请输入联系人');
            return false;
        }else if (st_contacts.length > 20){
        	 $.custom('联系人过长');
        	 return false;
        }
        if(st_mobile=='' || st_mobile == '请输入联系电话'){
            $.custom('请输入联系电话');
            return false;
        }else if (!regTel.test(st_mobile)){
        	$.custom('手机号格式不正确');
            return false;
        }

        storeSub=false;
        $.post(url,{
                st_type:st_type,
                st_size:st_size,
                st_height:st_height,
                st_provice:st_provice,
                st_city:st_city,
                st_dist:st_dist,
                st_address:st_address,
                st_contacts:st_contacts,
                st_mobile:st_mobile,
                st_desc:st_desc,
                st_id:st_id,
        		st_img:st_img},
            function(data){
                if(data.status==1){
                	closeClear();
                    $.custom('保存成功');
                    $("#pa_id").val(0);
                  window.location.reload();
                }else{
                    $.custom('保存失败');
                    storeSub=true;
                    return false;
                }
            }, "json");
    });


    //相关业务赋值函数
    function storeData(obj,data){
        var i = data.type;
        $(".st_type").each(function (index){
            var val = '';
            val=$(this).val();
            if(i==val){
               $(this).attr('checked','checked');
            }
        });

        obj.find("#st_provice").html(data.zone.provice);
        if(data.zone.city!=''){
            obj.find("#st_city").html(data.zone.city);
        }

        if(data.zone.dist!=''){
            obj.find("#st_dist").html(data.zone.dist);
        }

        obj.find('#st_size').val(data.size);
        obj.find('#st_height').val(data.height);
        obj.find('#st_address').val(data.address);
        obj.find('#st_desc').val(data.desc);
        obj.find('#st_contacts').val(data.contacts);
        obj.find('#st_mobile').val(data.mobile);
        obj.find('#st_id').val(data.id);
        var imgL = data.img.length;
        if (imgL >= 5){
        	$(".up-btn1").hide();
        }
        for (var i = 0; i < imgL; i++) {
        	var index = i + 1;
        	var val = data.img[i];
			var img = $('<li class="img-li1"><img id="store-img' + index + '" src="'+ val +'"/><div class="del"><span>删除</span></div><input value="'+ val +'" name="st_img[]" type="hidden" id="store-ad' + index + '"/></li>');
        	$("#preview5").append(img);
        }
    }

    /********仓库信息操作结束********/

    /********物流信息操作开始********/

    //动态获取城市(出发地)
    $("#de_begin_provice").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getCityHtml", {id:id},function(data){
            $("#de_begin_city").html(data);
        });
         $("#de_begin_dist").html('<option selected="selected">请选择区/县</option>');
    });

    //动态获取区县（出发地）
    $("#de_begin_city").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getAreaHtml", {id:id},function(data){
            $("#de_begin_dist").html(data);
        });
    });

    //动态获取城市（目的地）
    $("#de_end_provice").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getCityHtml", {id:id},function(data){
            $("#de_end_city").html(data);
        });
         $("#de_end_dist").html('<option selected="selected">请选择区/县</option>');
        
    });

    //动态获取区县（目的地）
    $("#de_end_city").on('change',function(){
        var self=$(this);
        var id=self.val();
        $.get("/Region/getAreaHtml", {id:id},function(data){
            $("#de_end_dist").html(data);
        });
    });

    //保存物流信息
    $("#de_save").on('click',function(){
        if(!deliverySub){
            return false;
        }
        var self=$(this);
        var url='/CompanyDelivery/saveInfo.html';
        var de_type=$("#de_type").val();
        var de_begin_provice=$("#de_begin_provice").val();
        var de_begin_city=$("#de_begin_city").val();
        var de_begin_dist=$("#de_begin_dist").val();
        var de_end_provice=$("#de_end_provice").val();
        var de_end_city=$("#de_end_city").val();
        var de_end_dist=$("#de_end_dist").val();
        var de_desc=$("#de_desc").val();
        var de_id=$("#de_id").val();
        if(de_type==''){
            $.custom('请选择车辆类型');
            return false;
        }

        deliverySub=false;
        $.post(url,{
            de_type:de_type,
            de_begin_provice:de_begin_provice,
            de_begin_city:de_begin_city,
            de_begin_dist:de_begin_dist,
            de_end_provice:de_end_provice,
            de_end_city:de_end_city,
            de_end_dist:de_end_dist,
            de_desc:de_desc,
            de_id:de_id},
            function(data){
                if(data.status==1){
                	closeClear();
                    $.custom('保存成功');
                    $("#de_desc").val("");
                    $("#de_type").val(0);
                    $("#pa_id").val(0);
                    window.location.reload();
                }else{
                    $.custom('保存失败');
                    deliverySub=true;
                    return false;
                }
            }, "json");
    });

    //相关业务赋值函数
    function deliveryData(obj,data){

        //出发地
        obj.find("#de_begin_provice").html(data.begin.provice);
        if(data.begin.city!=''){
            obj.find("#de_begin_city").html(data.begin.city);
        }

        if(data.begin.dist!=''){
            obj.find("#de_begin_dist").html(data.begin.dist);
        }

        //目的地
        obj.find("#de_end_provice").html(data.end.provice);
        if(data.end.city!=''){
            obj.find("#de_end_city").html(data.end.city);
        }

        if(data.end.dist!=''){
            obj.find("#de_end_dist").html(data.end.dist);
        }

        //车辆类型
        var i = data.type;
        $("#de_type").find('option').each(function(index){
            var val=$(this).val();
            if(i==val){
                $(this).attr('selected','selected');
            }
        });

        obj.find('#de_desc').val(data.desc);
        obj.find('#de_id').val(data.id);
    }

    /********物流信息操作结束********/

    /********包装信息操作开始********/

    //保存包装信息
    $("#pa_save").on('click',function(){
        if(!packingSub){
            return false;
        }
        var self=$(this);
        var url='/CompanyPacking/saveInfo.html';
        var pa_method=$.trim($("#pa_method").val());
        var pa_material=$.trim($("#pa_material").val());
        var pa_id=$("#pa_id").val();
		//隐藏域value
        var pa_img = [];
        $('input[name="pa_img[]"]').each(function (){
        	pa_img.push($(this).val());
        });
        if(pa_method=='' || pa_method == '请输入包装方式'){
            $.custom('请输入包装方式');
            return false;
        }else if (pa_method.length > 100){
        	$.custom('包装方式内容过长');
            return false;
        }

        if(pa_material=='' || pa_material == '请输入包装材料'){
            $.custom('请输入包装材料');
            return false;
        }else if (pa_material.length > 100){
        	$.custom('包装材料内容过长');
            return false;
        }
        packingSub=false;
        $.post(url,{
                pa_method:pa_method,
                pa_material:pa_material,
                pa_id:pa_id,
        		pa_img:pa_img},
            function(data){
                if(data.status==1){
                	closeClear();
                    $.custom('保存成功');
                    $("#pa_method").val("");
                    $("#pa_material").val("");
                    $("#pa_id").val(0);
                    window.location.reload();
                }else{
                    $.custom('保存失败');
                    packingSub=true;
                    return false;
                }
            }, "json");
    });

    //相关业务赋值函数
    function packingData(obj,data){

        //包装方式
        obj.find('#pa_method').val(data.packing_method);
        //包装材料
        obj.find('#pa_material').val(data.packing_material);
        obj.find('#pa_id').val(data.id);
        var imgL = data.img.length;
        if (imgL >= 5){
        	$(".up-btn2").hide();
        }
        for (var i = 0; i < imgL; i++) {
        	var index = i + 1;
        	var val = data.img[i];
			var img = $('<li class="img-li2"><img id="packing-img' + index + '" src="' + val +'"/><div class="del"><span>删除</span></div><input value="'+ val +'" name="pa_img[]" type="hidden" id="packing-ad' + index + '"/></li>');
        	$("#preview6").append(img);
        }
    }

    /********包装信息操作结束********/

    /********代加工信息操作开始********/

    //保存加工信息
    $("#pr_save").on('click',function(){
        if(!processSub){
            return false;
        }
        var self=$(this);
        var url='/CompanyProcess/saveInfo.html';
        var pr_content=$("#pr_content").val();
        var pr_other=$.trim($("#pr_other").val());
        var pr_remarks=$.trim($("#pr_remarks").val());
        var pr_id=$("#pr_id").val();
        if (pr_other == '请输入加工方式'){
        	pr_other = '';
        }
        if (pr_content == 9999){
        	$.custom('请选择加工方式');
            return false;
        }
        if(pr_content== -1 && pr_other==''){
            $.custom('请输入加工方式');
            return false;
        }
        if(pr_other.length>30){
            $.custom('加工方式内容过长');
            return false;
        }else if (pr_remarks.length > 300){
        	 $.custom('备注内容过长');
            return false;
        }
        processSub=false;
        $.post(url,{
                pr_content:pr_content,
                pr_other:pr_other,
                pr_remarks:pr_remarks,
                pr_id:pr_id},
            function(data){
                if(data.status==1){
                	closeClear();
                    $.custom('保存成功');
                    $("#pr_other").val("");
                    $("#pr_remarks").val("");
                    $("#pr_content").val("9999");
                    $("#pr_id").val(0);
                    window.location.reload();
                }else{
                    $.custom('保存失败');
                    processSub=true;
                    return false;
                }
            }, "json");
    });

    //相关业务赋值函数
    function processData(obj,data){

        //加工方式
        if(data.other!=''){
            obj.find('#pr_other').val(data.other);
            obj.find(".pr_other").show();
           obj.find("#pr_content").val("-1");
        }else{
            var i = data.content;
            $("#pr_content").find('option').each(function(index){
                var val=$(this).val();
                if(i==val){
                    $(this).attr('selected','selected');
                }
            });
        }

        //备注
        obj.find('#pr_remarks').val(data.remarks);
        obj.find('#pr_id').val(data.id);
    }

    /********代加工信息操作结束********/

    /********代检测信息操作开始********/

    //保存检测信息
    $("#ch_save").on('click',function(){
        if(!checkSub){
            return false;
        }
        var self=$(this);
        var url='/CompanyCheck/saveInfo.html';
        var ch_content=$("#ch_content").val();
        var ch_other=$.trim($("#ch_other").val());
        var ch_remarks=$.trim($("#ch_remarks").val());
        var ch_id=$("#ch_id").val();
		if (ch_other == '请输入检测方法'){
			ch_other = '';
		}
		if (ch_content == "9999"){
			$.custom('请选择检测方法');
            return false;
		}else if(ch_content == -1  &&  ch_other==''){
        	$.custom('请输入检测方法');
            return false;
        }

        if(ch_other.length>30){
            $.custom('检测方法内容过长');
            return false;
        } else if (ch_remarks.length > 300){
			$.custom('备注内容过长');
            return false;
		}
        checkSub=false;
        $.post(url,{
                ch_content:ch_content,
                ch_other:ch_other,
                ch_remarks:ch_remarks,
                ch_id:ch_id},
            function(data){
                if(data.status==1){
                	closeClear();
                    $.custom('保存成功');
                    $("#ch_other").val("");
                    $("#ch_remarks").val("");
                    $("#ch_content").val("9999");
                    $("#ch_id").val(0);
                    window.location.reload();
                }else{
                    $.custom('保存失败');
                    checkSub=true;
                    return false;
                }
            }, "json");
    });

    //相关业务赋值函数
    function checkData(obj,data){

        //检测方法
        if(data.other!=''){
            obj.find('#ch_other').val(data.other);
            obj.find(".ch_other").show();
           obj.find("#ch_content").val("-1");
        }else{
            var i = data.content;
            $("#ch_content").find('option').each(function(index){
                var val=$(this).val();
                if(i==val){
                    $(this).attr('selected','selected');
                }
            });
        }

        //备注
        obj.find('#ch_remarks').val(data.remarks);
        obj.find('#ch_id').val(data.id);
    }

    /********代检测信息操作结束********/

    //关闭按键
     $("b.close").click(function() {
        closeClear();
	});
    //取消按键
    $(".sure-div>.del").click(function (){
		closeClear();
    });
    //清空表单内容
    var empty = $(".empty");
	function closeClear(){
		empty.hide();
        $(".empty .alert-wrap>div").hide();
        empty.find('textarea').val("");
		empty.find("input[type='text']").val("");
		empty.find("input[type='hidden']").val("");
        $(".img-li1").remove();
		empty.find('select.de_type').val("0");
		empty.find('select.default_select').val('9999');
		empty.find('select.city').html('<option selected="selected">请选择市</option>');
		empty.find('select.dist').html('<option selected="selected">请选择区/县</option>');
		var radios = $(".empty input[type='radio']:first");
		radios.attr("checked" , "checked");
		//清空图片
		$("#preview5").html(store);
		$("#preview6").html(packing);
		//其他方式输入框隐藏
		$(".other-way").hide();
	}
	//检测添加是否超过5条，如果超过5条，隐藏添加按钮出现提示
    $(".bsns-ul ul.content").each(function () {
        var _this = $(this);
        if (_this.children().length >= 6){
            _this.siblings("div").find(".warn_span").show();
        }else {
            _this.siblings("div").find(".add_span").show();
        }
    });
});