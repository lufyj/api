/**
 * Created by jyp on 2017/3/21.
 */
var _mobile = $("#mobile");
var getCode = $(".get_code");
var repeatBol = true;
function getMobileCode(t) {
    if (!repeatBol) return;
    repeatBol = false;
    var data = {};
    var phone = _mobile.val();
    if (!rule.phone(phone)) {
        rule.showMsg(1 , "手机格式不正确"  , 1000);
        repeatBol = true;
        return;
    }
    data.mobile = phone;
    if (t == 1){
        data.code_source = 1;
    }else if (t == 2){
        data.code_source = 2;
        data.type = 1;
    }else if (t == 3){
        data.code_source = 3;
    }
    $.ajax({
        url:rule.root + "/User/mobileCode",
        data:data,
        type:"post",
        dataType:"json",
        success:function (req) {
            repeatBol = true;
            var code = req.code;
            if (code == 20){
                rule.showMsg(1 , "手机格式正确"  , 1000);
            }else  if (code == 21){
                rule.showMsg(1 , "该手机已经被注册"  , 1000);
            }else if (code == 1){
                getCode.attr("disabled" , "disabled");
                //短信发送成功
                var txt = 60;
                getCode.text(txt + "秒后重试");
                var timer = setInterval(function (){
                    txt--;
                    getCode.text(txt + "秒后重试");
                    if (txt == 0){
                        getCode.removeAttr("disabled").text("获取验证码");
                        clearInterval(timer);
                    }
                } , 1000)
            }else if(code == -1){
                rule.showMsg(1 , "短信发送失败"  , 1000);
            }
        },
        error:function(err){
            console.log(err);
            repeatBol = true;
        }
    })
}
$(function(){
    _mobile.keyup(function(){
        var phone = $(this).val();
        if (rule.phone(phone)) getCode.removeAttr("disabled");
    });
    var pswReg = /^([0-9]|[a-z]|[A-Z]){6,20}$/i;
    var regBol = true;
    $(".reg_btn").click(function () {
        if (!regBol) return false;
        var phone = _mobile.val();
        var code = $("#code").val();
        var psw = $("#psw").val();
        var repsw = $("#repsw").val();
        var nickname = $("#nickname").val();
        var spread = $("#spread").val();
        if (!rule.phone(phone)){
            rule.showMsg(1 , "手机不正确"  , 1000);
            return;
        }else if (!code){
            rule.showMsg(1 , "请输入验证码"  , 1000);
            return;
        }else if (!pswReg.test(psw)){
            rule.showMsg(1 , "密码格式不正确"  , 1000);
            return;
        }else if (repsw != psw){
            rule.showMsg(1 , "两次密码不一致"  , 1000);
            return;
        }else  if (!nickname){
            rule.showMsg(1 , "请输入昵称"  , 1000);
            return;
        }
        var data = {
            mobile:phone,
            captcha:code,
            password:psw,
            repassword:repsw,
            realname:nickname,
            spreading_code:spread
        }
        regBol = false;
        $.post(rule.root+"/User/register" , data , function (req) {
            var code = req.code;
            switch (code){
                case 1:
                    rule.showMsg(1 , "注册成功正在调转"  , 1000 , "./login.html");
                    break;
                case -1:
                    rule.showMsg(1 , "注册失败请稍后再试"  , 1000 , "./reg.html");
                    break;
                case 10:
                    rule.showMsg(1 , "请输入2-10个字符的昵称"  , 1000);
                    break;
                case 41:
                    rule.showMsg(1 , "两次输入密码不一致"  , 1000);
                    break;
                case 40:
                    rule.showMsg(1 , "请输入6-20个字符的密码"  , 1000);
                    break;
                case 99:
                    rule.showMsg(1 , "手机号已经被更改"  , 1000);
                    break;
                case 38:
                    rule.showMsg(1 , "手机验证码错误"  , 1000);
                default:
                    break;
            }
            regBol = true;
        })
    })


    var findBol = true;
    $(".find_btn").click(function (){
        if (!findBol) return false;
        var phone = _mobile.val();
        var code = $("#code").val();
        var psw = $("#psw").val();
        var repsw = $("#repsw").val();
        if (!rule.phone(phone)){
            rule.showMsg(1 , "手机不正确"  , 1000);
            return;
        }else if (!code){
            rule.showMsg(1 , "请输入验证码"  , 1000);
            return;
        }else if (!pswReg.test(psw)){
            rule.showMsg(1 , "密码格式不正确"  , 1000);
            return;
        }else if (repsw != psw){
            rule.showMsg(1 , "两次密码不一致"  , 1000);
            return;
        }
        var data = {
            mobile:phone,
            code:code,
            password:psw,
            confirm_password:repsw,
        }
        findBol = false;
        $.post(rule.root+"User/find_password " , data , function (req) {
            var code = req.code;
            switch (code){
                case 1:
                    rule.showMsg(1 , "密码修改成功"  , 1000 , "./login.html");
                    break;
                case 0:
                    rule.showMsg(1 , "密码修改失败"  , 1000);
                    break;
                case 41:
                    rule.showMsg(1 , "两次输入密码不一致"  , 1000);
                    break;
                case 40:
                    rule.showMsg(1 , "请输入6-20个字符的密码"  , 1000);
                    break;
                case 99:
                    rule.showMsg(1 , "手机号已经被更改"  , 1000);
                    break;
                case 38:
                    rule.showMsg(1 , "手机验证码错误"  , 1000);
                    break;
                case 98:
                    rule.showMsg(1 , "新密码不能与原密码相同"  , 1000);
                    break;
                default:
                    break;
            }
            findBol = true;
        })
    });
})
