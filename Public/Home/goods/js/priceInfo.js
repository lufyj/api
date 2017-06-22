var chart;
$(function() {
	$.ajax({
		url: urls[0],
		dataType: "json",		
		data: params,
		type: "get",
		success: function(req) {			
			if(req.code == 1){
				createChart(req.data.data);		         
		        //给今日价格赋值
		        $('#todayPrice').text(req.data.price);		        
			}
		},
		error: function() {			
			$.custom('网络连接超时，请稍后再试');
		}
	});
	loadYearData();	
	laydate.skin('dahong');	
    
    /* 更改日期比较 */
    $('#changeDates').on('click', function(){    
    	  var _s = document.getElementById("startDate").value;
          var _e = document.getElementById("endDate").value;          
          var startDate = stringToDate(_s);
          var endDate = stringToDate(_e);
          chart.zoomToDates(startDate, endDate);
          //loadRateFromDate(_s, _e);    	
    });
});
function stringToDate(str) {
    var dArr = str.split("-");
    var date = new Date(parseInt(dArr[0]), parseInt(dArr[1]) - 1, parseInt(dArr[2]));  
    return date;
}
/* 选中区域范围赋值 */
function handleZoom(event) {
    var _s = document.getElementById("startDate").value = AmCharts.formatDate(event.startDate, "YYYY-MM-DD");
    var _e = document.getElementById("endDate").value = AmCharts.formatDate(event.endDate, "YYYY-MM-DD");    
    changeGraphType(event);
    loadRateFromDate(_s, _e);
}
function changeGraphType(event) {
    var startIndex = event.startIndex;
    var endIndex = event.endIndex;
    if (endIndex - startIndex > 0) {
        chart.validateNow();
    }
}
/* 加载当前价格等信息 */
function _goto(replacewho, replaceval, node){
	var _this = $(node); 
	if(_this.hasClass('active')) return;
	switch(replacewho){
		case 'area':
			_this.addClass('active').siblings().removeClass('active');			
			params.area = _this.text();
			loadAttr();
			break;
		case 'attr':
			_this.addClass('active').siblings().removeClass('active');
			params.attr = _this.text();			
			loadData();
			loadYearData();
			break;		
	}	
	$('#searchStr').html(params.q+' '+params.attr+' '+params.area);
}
/* 根据省份加载规格 */
function loadAttr(){
	$.ajax({
		url: urls[1],
		dataType: "json",		
		data: params,
		type: "get",
		beforeSend: function() {
			//正在加载中提示语        
		},
		success: function(req) {			
			var attrObj = $('#attr');
			if(req.code == 1){
				var sb = '', already = false;
				for(var i in req.data){
					var active = '';
					if(req.data[i] == params.attr){
						active = 'class=\'active\'';
						already = true;
					}
					sb += '<a href="javascript:;" onclick="_goto(\'attr\',\''+ req.data[i] +'\',this);" '+ active +'>'+ req.data[i] +'</a>';
				}
				attrObj.html(sb);
				if(!already) {
					params.attr = attrObj.find('a:first').text();
					attrObj.find('a:first').addClass('active');					
				}
				loadData();
				loadYearData();
			}else{
				attrObj.html('');
			}
		},
		error: function() {			
			$.custom('网络连接超时，请稍后再试');
		}
	});
}
/* 公共加载函数 */
function loadData(){
	$.ajax({
		url: urls[0],
		dataType: "json",		
		data: params,
		type: "get",		
		success: function(req) {			
			if(req.code == 1){
				req = req.data;
				req.price && $('#todayPrice').text(req.price);		
				var _s = document.getElementById("startDate").value;// = req.startDate;
		        var _e = document.getElementById("endDate").value;// = req.endDate;
		        chart.dataProvider = req.data;
		        chart.validateNow();
		        chart.validateData();		        
		        if(_s == req.startDate && _e == req.endDate){
		        	loadRateFromDate(_s, _e);
		        }
		        document.getElementById("startDate").value = req.startDate;
		        document.getElementById("endDate").value = req.endDate;		        
			}
		},
		error: function() {			
			$.custom('网络连接超时，请稍后再试');
		}
	});		
}
/* 加载函数 */
function loadYearData(){
	var _params = $.extend({}, params, {inx:1});
	$.ajax({
		url: urls[0],
		dataType: "json",		
		data: _params,
		type: "get",		
		success: function(req) {			
			if(req.code == 1){				
				var sb = '', in_year = [], in_month = [];				
				for(var i in req.data){
					var year = req.data[i].in_month.substring(0,4);
					if($.inArray(year, in_year) == -1){
						in_year.push(year);	
						in_month[year+''] = [];
					}
					in_month[year+''].push(req.data[i].price);				
				}
				for(var m in in_month){
					sb += '<tr><td>'+ m +'</td>';
					for(var n in in_month[m]){	
						sb += '<td>'+ (parseFloat(in_month[m][n]) || '---') +'</td>';
					}
					sb += '</tr>';
				}				
				$('#tbody').html(sb);				
			}else{
				$('#tbody').empty();
			}
		},
		error: function() {			
			$.custom('网络连接超时，请稍后再试');
		}
	});
};
/* 获取该药材时间段内的涨跌率 */
function loadRateFromDate(_s, _e){	
	if(_s && _e){
		var	_params = $.extend({}, params, {inx:2,sDate:_s,eDate:_e});		
		$.ajax({
			url: urls[0],
			dataType: "json",		
			data: _params,
			type: "get",		
			success: function(req) {			
				if(req.code == 1){					
					var _price = parseFloat(req.data); 
					if(_price > 0){
						$('#rate').html('涨幅：<font class="col-r">↑</font>'+ _price +'%');
					}else if(_price < 0){
						$('#rate').html('涨幅：<font class="col-g">↓</font>'+ _price +'%');
					}else{
						$('#rate').html('涨幅：0.00%');
					}	
				}
			},
			error: function() {			
				$.custom('网络连接超时，请稍后再试');
			}
		});
	}
}
/* 生成折线图函数 */
function createChart(data){
	// SERIAL CHART
    chart = new AmCharts.AmSerialChart();
    chart.pathToImages = "/Public/static/amcharts/images/";
    chart.dataProvider = data;
    chart.dataDateFormat = "YYYY-MM-DD";
    chart.categoryField = "date";		        
    chart.marginTop = 5;
    chart.addListener('zoomed', handleZoom);

    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.parseDates = true; // as our data is date-based, we set parseDates to true
    categoryAxis.minPeriod = "DD"; // our data is daily, so we set minPeriod to DD		         
    /*categoryAxis.gridAlpha = 0.1;
    categoryAxis.minorGridAlpha = 0.1;
    categoryAxis.axisAlpha = 0;		         
    categoryAxis.minorGridEnabled = true;
    */		         
    categoryAxis.gridAlpha = 0;	  
    categoryAxis.boldPeriodBeginning = false;//去掉年份加粗
    categoryAxis.dateFormats = [{period:'DD',format:'M月D日'},{period:'WWW',format:'MMM DD'},{period:'MM',format:'YYYY年M月'},{period:'YYYY',format:'YYYY年'}];
    
    // value
    var valueAxis = new AmCharts.ValueAxis();
    valueAxis.tickLength = 0;
    valueAxis.axisAlpha = 0;
    valueAxis.showFirstLabel = false;
    valueAxis.showLastLabel = false;
    chart.addValueAxis(valueAxis);

    // GRAPH
    var graph = new AmCharts.AmGraph();         
    graph.type = "step";		         
    graph.valueField = "value";
    //graph.dashLength = 1;
    graph.bullet = "round";
    graph.bulletBorderAlpha = 1;
    graph.bulletBorderColor = "#FFFFFF"
    graph.bulletBorderThickness = 2;
    graph.lineThickness = 2;
    graph.lineColor = "#5fb503";
    graph.negativeLineColor = "#efcc26";
    graph.hideBulletsCount = 50;
    graph.balloonText = "日期：[[date]]<br/>价格：<b>[[value]]</b>";    
    chart.addGraph(graph);

    // CURSOR
    var chartCursor = new AmCharts.ChartCursor();
    chartCursor.valueLineEnabled = true;
    chartCursor.valueLineBalloonEnabled = true;
    chartCursor.categoryBalloonDateFormat = "YYYY年MM月DD日";
    chart.addChartCursor(chartCursor);

    // SCROLLBAR
    var chartScrollbar = new AmCharts.ChartScrollbar();
    chart.addChartScrollbar(chartScrollbar);
   
    // WRITE
    chart.write("container");	
}