/**
 * Created by admin on 2017/3/20.
 */
var rule = {
    root: 'http://192.168.2.42/ydwAPI/index.php?s=Api/',
    empty: function(obj, mess) {
        if ($.trim(obj.val()) == '') {
            obj.focus();
            return false;
        }
        return true;
    },
    objEmpty: function(obj) {
        if (obj) {
            if (typeof obj == 'object') {
                for (var name in obj) {
                    return false;
                }
            }
            return true;
        }
        return true;
    },
    phone: function(value) {
        var myReg = /^(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
        if (!myReg.test($.trim(value))) {
            return false;
        }
        return true;
    },
    email: function(obj) {
        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
        return reg.test(obj);
    },
    getLocalTime: function(nS, format) {
        if (format === false) {
            var nS = nS.replace(/-/g, "/");
            var _date = new Date(nS);
            return _date.getFullYear() + '年' + (_date.getMonth() + 1) + '月' + _date.getDate() + '日';
        }
        return new Date(parseInt(nS) * 1000).format(format || 'yyyy-MM-dd');
    },
    //获取url参数
    getArg: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return '';
    },
    getRequest:function() {
        var url = location.search; //获取url中"?"符后的字串
        var theRequest = new Object();
        if(url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for(var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    },
    isNumberKey: function(evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    },
    isFloatKey: function(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode == 46) {
            var inputValue = $(element).val();
            if (inputValue && inputValue.indexOf('.') === -1) {
                return true;
            }
            return false;
        }
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    },
    isInteger: function(value) {
        if (value && /^\d+$/.test(value)) { ///^([+-]?)(\d+)$/
            value = parseFloat(value, 10);
            if (!isNaN(value) && isFinite(value) && Math.floor(value) === value && value > 0) {
                return value;
            }
        }
        return false;
    },
    formatFloat: function(value, evt) {
        if (!value) {
            return 0;
        }
        return parseFloat(parseFloat(value).toFixed(evt));
    },
    nofind: function() {
        var img = event.srcElement;
        img.src = "../images/noImg.png";
        img.onerror = null;
    },
    emptyStyle: function(txt) {
        return '<section class="shopEmpty"><img src="../images/dingdan.png"><p>' + txt + '</p></section>';
    },
    showMsg:function (type , msg , time , url , dom) {
        var str = '';
        if (!time){
            time = 2000;
        }
        if (type == 1){
             str = '<div class="modal_container"><table align="center" height="100%" width="100%"><tbody><tr><td align="center"><p class="lay_wp">'+msg+'</p></td></tr></tbody></table></div>';
            $(document.body).append(str);
            $(".modal_container").show().delay(time).fadeOut("normal" ,function(){
                $(".modal_container").remove();
                if (url){
                    location.href = url;
                }
            });
        }
    },
    // showMsg: function(type, msg, time, url) {
    //     var str = '';
    //     if (!time) {
    //         time = 2000;
    //     }
    //     if (type == 1) {
    //         if (!msg) {
    //             msg = '操作成功';
    //         }
    //         str = "<div id='add' class='m-layer noMask'><table><tbody><tr><td><article class='lywrap'><section class='lyct clearfix'><p class='pt05'><i class='iconfont icon-xuanzhong'></i></p><p class='pt05'>" + msg + "</p></section></article></td></tr></tbody></table></div>";
    //         $(document.body).append(str);
    //         $('#add').show().delay(time).fadeOut('normal', function() {
    //             $('#add').remove();
    //             if (url) {
    //                 if (url === 'back') {
    //                     history.back();
    //                 } else {
    //                     location.href = url;
    //                 }
    //             }
    //
    //         });
    //     } else if (type == 2) { //错误提示
    //         if (!msg) {
    //             msg = '操作失败';
    //         }
    //         str = "<div id='add' class='m-layer noMask'><table><tbody><tr><td><article class='lywrap'><section class='lyct clearfix'><p>" + msg + "</p></section></article></td></tr></tbody></table></div>";
    //         $(document.body).append(str);
    //         $('#add').show().delay(time).fadeOut('normal', function() {
    //             $('#add').remove();
    //             if (url) {
    //                 location.href = url;
    //             }
    //         });
    //     } else if (type == 3) { //带有确定 取消的 提示框
    //         if (!msg) {
    //             msg = '您确定删除吗?';
    //         }
    //         var isokdel;
    //         str = "<div class='m-layer z-show' id='Quit'><table><tbody><tr><td><article class='lywrap'><section class='lyct clearfix'><h2 class='lyctTitle'>" + msg + "</h2><button class='cancel' onclick='javascript:void(0);'>取消</button><button class='confirm' onclick='javascript:void(0);'>确定</button></section></article></td></tr></tbody></table></div>";
    //         $(document.body).append(str); //插进页面
    //         $('.cancel').click(function() { //点击取消删除
    //             $('#Quit').remove();
    //         })
    //     } else if (type == 4) { //只有确定的提示框
    //         if (!msg) {
    //             msg = '您确定删除吗?';
    //         }
    //         var isokdel;
    //         str = "<div class='m-layer z-show' id='close'><table><tbody><tr><td><article class='lywrap lywrap2'><section class='lyct clearfix'><img src='../images/cha.png' class='close' style='width:1rem;position:absolute;height:auto;right:1rem;'>" + msg + "</section></article></td></tr></tbody></table></div>";
    //         $(document.body).append(str); //插进页面
    //         $('.close').click(function() { //点击取消删除
    //             $('#close').remove();
    //         })
    //     }
    // }
}
/***********************intent begin******************************
 命名规则，模块名前三个字母_文件名前三个字母_变量名
 **/
function Intent(key, value) {
    if (key) {
        sessionStorage.setItem(key, JSON.stringify(value));
        /*if(value){
         sessionStorage.setItem(key, JSON.stringify(value));
         }else{
         if(typeof value === 'undefined'){
         Intent.prototype.getIntent(key);
         }else if(typeof value === ''){
         Intent.prototype.removeIntent(key);
         }
         } */
    }
    return Intent.prototype;
}
Intent.prototype = {
    setIntent: function(key, value) {
        sessionStorage.setItem(key, JSON.stringify(value));
    },
    getIntent: function(key, destroy) {
        /***********if destroy then  single-use else repeated use **************/
        var myintent = sessionStorage.getItem(key);
        if (destroy && myintent) {
            sessionStorage.removeItem(key);
            console.log('sessionstorage %s removed success!', key);
        }
        return myintent && JSON.parse(myintent);
    },
    removeIntent: function(key) {
        if (sessionStorage.getItem(key)) {
            sessionStorage.removeItem(key);
        }
    },
    clear: function(prefix) {
        if (prefix) {
            for (var i = sessionStorage.length - 1; i >= 0; i--) {
                var _key = sessionStorage.key(i);
                if ((_key + '').indexOf(prefix) >= 0) {
                    sessionStorage.removeItem(_key);
                }
            }
        } else {
            sessionStorage.clear();
        }
    }
}

/***********************intent end********************************/
/***********************localdata begin***************************/
function LocalData(key, value) {
    if (key) {
        localStorage.setItem(key, JSON.stringify(value));
    }
    return LocalData.prototype;
}

LocalData.prototype = {
    setData: function(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    },
    getData: function(key) {
        var mydata = localStorage.getItem(key);
        return mydata && JSON.parse(mydata);
    },
    removeData: function(key) {
        if (localStorage.getItem(key)) {
            localStorage.removeItem(key);
        }
    },
    clear: function(prefix) {
        if (prefix) {
            for (var i = localStorage.length - 1; i >= 0; i--) {
                var _key = localStorage.key(i);
                if ((_key + '').indexOf(prefix) >= 0) {
                    localStorage.removeItem(_key);
                }
            }
        } else {
            localStorage.clear();
        }
    }
}

function StringBuilder() {
    this.data = Array("");
}
StringBuilder.prototype.append = function() {
    this.data.push(arguments[0]);
    return this;
};
StringBuilder.prototype.toString = function() {
    return this.data.join("");
};
/**
 * 时间对象的格式化;
 */
Date.prototype.format = function(format) {
    /*
     * eg:format="YYYY-MM-dd hh:mm:ss";
     */
    var o = {
        "M+": this.getMonth() + 1, // month
        "d+": this.getDate(), // day
        "h+": this.getHours(), // hour
        "m+": this.getMinutes(), // minute
        "s+": this.getSeconds(), // second
        "q+": Math.floor((this.getMonth() + 3) / 3), // quarter
        "S": this.getMilliseconds()
        // millisecond
    }

    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "")
            .substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
}
/**
 * 将uid token ， clientType封装;
 */
jQuery.extend({
    postT:function(url,data,success ,beforeSend , error ){
        var token = rule.token || LocalData().getData("user_info").token;
        var uid = rule.uid || LocalData().getData("user_info").uid;
        var clientType = 1;
        var datas = data || {};
        datas.token = token;
        datas.uid = uid;
        datas.clientType = clientType;
        $.ajax({
            url:url,
            data:datas,
            type:'post',
            beforeSend:function (data){
                beforeSend&&beforeSend(data)
            },
            success:function (req){
                success&&success(req);
            },
            error:function (err){
                error&&error(err);
            }
        })
    }
})

/**
 * 对于需要登陆的用户，在每次访问页面之前都要进行验证
 */

function islogin(){
    if (!LocalData().getData("user_info")){
        // localStorage.clear();
        return false;
    }
    return true;
}

//判断手机用户还是微信用户
//先用 islogin 再用isMobile
function isMobile(){
    if (!LocalData().getData("user_info").mobile){
        return false;
    }
    return true;
}


//判断微信登录
rule.requestWx = function (url) {
    var returnUrl = encodeURI(url);
    location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd0f494d8eb878d31&redirect_uri='+ returnUrl +'&response_type=code&scope=snsapi_userinfo&state=ydw#wechat_redirect '
}
//token错误时候需要做的
rule.reLogin = function () {
    var hrefUrl = "./login.html?ReturnUrl=" + encodeURIComponent(location.href);
    rule.showMsg(1 , "该账号已在其他地方登陆,请重新登录" , 1500 , hrefUrl);
}
/**
 * 文本框根据输入内容自适应高度
 * @param                {HTMLElement}        输入框元素
 * @param                {Number}                设置光标与输入框保持的距离(默认0)
 * @param                {Number}                设置最大高度(可选)
 */
function autoTextarea(elem, extra, maxHeight) {
    extra = extra || 0;
    var isFirefox = !!document.getBoxObjectFor || 'mozInnerScreenX' in window,
    isOpera = !!window.opera && !!window.opera.toString().indexOf('Opera'),
            addEvent = function (type, callback) {
                    elem.addEventListener ?
                            elem.addEventListener(type, callback, false) :
                            elem.attachEvent('on' + type, callback);
                },
                getStyle = elem.currentStyle ? function (name) {
                        var val = elem.currentStyle[name];
 
                        if (name === 'height' && val.search(/px/i) !== 1) {
                            var rect = elem.getBoundingClientRect();
                            return rect.bottom - rect.top -
                                    parseFloat(getStyle('paddingTop')) -
                                    parseFloat(getStyle('paddingBottom')) + 'px';        
                        };
 
                        return val;
                } : function (name) {
                                return getComputedStyle(elem, null)[name];
                },
                minHeight = parseFloat(getStyle('height'));
 
        elem.style.resize = 'none';
 
        var change = function () {
                var scrollTop, height,
                        padding = 0,
                        style = elem.style;
 
                if (elem._length === elem.value.length) return;
                elem._length = elem.value.length;
 
                if (!isFirefox && !isOpera) {
                        padding = parseInt(getStyle('paddingTop')) + parseInt(getStyle('paddingBottom'));
                };
                scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
 
                elem.style.height = minHeight + 'px';
            if (elem.scrollHeight > minHeight) {
                    if (maxHeight && elem.scrollHeight > maxHeight) {
                            height = maxHeight - padding;
                            style.overflowY = 'auto';
                    } else {
                            height = elem.scrollHeight - padding;
                            style.overflowY = 'hidden';
                    };
                    style.height = height + extra + 'px';
                        scrollTop += parseInt(style.height) - elem.currHeight;
                        document.body.scrollTop = scrollTop;
                        document.documentElement.scrollTop = scrollTop;
                        elem.currHeight = parseInt(style.height);
                };
        };
 
        addEvent('propertychange', change);
    addEvent('input', change);
    addEvent('focus', change);
    change();
}

/**
 * 返回键
 */
$("html").on('click' , "#back_btn" , function () {
    var referrer = window.document.referrer;
    if (referrer.indexOf('login') != -1 || referrer == ''){
        location.href = './index.html';
    }else {
        window.history.back();
    }
})

