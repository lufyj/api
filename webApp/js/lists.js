function line(obj) {
    obj.each(function() {
        var _this = $(this);
        var li_height = _this.outerHeight();
        console.log(li_height)
        _this.find(".line").css({
            'height': li_height + 'px'
        })
    });
}


