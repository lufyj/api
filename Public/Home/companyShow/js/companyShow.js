//检测备注长度
checkNum();
function checkNum() {
    var upA = '<a href="###" class="up-a">收起更多</a>',
        downA = '<a href="###" class="down-a">展开更多</a>';
    $('.span-store').each(function(){
        if($(this).text().length > 49){
            $(this).after(downA);
        }
    });
    var lastStore = $(".last-store");
    lastStore.on("click"  , ".down-a" , function (){
        var span   = $(this).siblings("span");
        span.css({
            "white-space":"normal",
            "width":"90%"
        });
        span.append(upA);
        $(this).remove();
    });
    lastStore.on("click"  , ".up-a" , function (){
        var span   = $(this).parent("span");
        span.css({
            "white-space":"nowrap",
            "width":"81%"
        });
        $(this).remove();
        span.after(downA);
    });
}
