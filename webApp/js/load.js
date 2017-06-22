

var scrolling = false;
var  str  = '<div style="font-size: 2.4rem;text-align: center;line-height:6rem;" id="load_wp" class="load_wp">正在加载中...</div>';
function appendLoad(obj , bol){
    obj.after(str);
    if (bol){
        $("#load_wp").css({
            'padding-bottom':'10.7rem'
        })
    }
}
function onFinish() {
    scrolling = false;
}
$(function () {
    function isBottom() {
        var scrollTop = 0;
        if (document.documentElement && document.documentElement.scrollTop) {
            scrollTop = document.documentElement.scrollTop;
        }
        else if (document.body) {
            scrollTop = document.body.scrollTop;
        }

        var clientHeight = document.documentElement.clientHeight;

        var scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);

        return Math.abs(scrollTop + clientHeight - scrollHeight) <= 600;
    }

    function onScroll() {
       var scrollTop = $(document).scrollTop();
        if (isBottom(scrollTop) && !scrolling && scrollTop > 0){
            scrolling = true;
            try {
                var scrollEvent = rule.scrollEvent;
                if (scrollEvent) {
                    scrollEvent(onFinish);
                }else{
                    onFinish();
                }
                //多容器 各自滚动
                var scrollEvents = rule.scrollEvents;
                if (scrollEvents) {
                    scrollEvents();
                }
            } catch (e) {
                onFinish();
            }
        }
    }
    window.addEventListener("scroll", onScroll, false);
});