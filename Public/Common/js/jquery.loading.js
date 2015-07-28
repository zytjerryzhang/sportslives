var httpRequest;
function loading() {    //打开遮罩层	
	if ($("#overlay").length>0 && $("#ajaxbusyspinner").length>0) {
		$("#overlay").height($(document).height()).show();
    	$("#ajaxbusyspinner").show();
    } else {	//生成遮罩层	
    	var bheight=$(document).height();    	
        var bwidth=$("body").width();
        var overlay = $('<div id="overlay" style="background-color:black;display:none;z-index:10000;position:absolute;left:0px;top:0px;filter:Alpha(Opacity=30);-moz-opacity:0.4;opacity: 0.4;"></div>');
        overlay.css({width:bwidth, height:bheight, display:"block"}).appendTo($("body"));
        overlay.dblclick(abortRequest);
        $('<div id="ajaxbusyspinner" style="position:absolute; top:50%; left:50%; text-align:center; width:130px; height:50px; margin-left:-65px; margin-top:-25px; z-indent:10090; font-size:14px;"><img style="border-width: 0px;" alt="Loading..." src="/Public/Admin/images/ajax-loader_med.gif" align="absmiddle" />&nbsp;&nbsp;Loading...</div>').appendTo($("body"));
    }  
}
function closeLoading() {   //关闭遮罩层
	$("#overlay").hide();
	$("#ajaxbusyspinner").hide();	
}
function abortRequest() {	//终止Ajax请求
	if (httpRequest!=undefined) {		
		httpRequest.abort();
		httpRequest = undefined;		
	}	
	closeLoading();
}
//块遮罩层
function partLoading(id) {		
	var partOverlay = $('<div id="partOverlay" style="background-color:black;z-index:10000;position:absolute;left:0px;top:0px;filter:Alpha(Opacity=20);-moz-opacity:0.2;opacity: 0.2;"></div>');
	partOverlay.css({'width':$('#'+id).width(), 'height':$('#'+id).height(), display:"block"});
	var imgOverlay = $('<div id="imgOverlay" style="z-index:10002;position:absolute;"><img src="/Public/Admin/images/ajax-loader_med.gif" /></div>');
	imgOverlay.css({'top':Number($('#'+id).height())/2-5, 'left':Number($('#'+id).width())/2-5, display:"block"});	
	$('#'+id).append(partOverlay);
	$('#'+id).append(imgOverlay);
}
function closePartLoading(id) {
	$("#imgOverlay", "#"+id).remove();
	$("#partOverlay", "#"+id).remove();
}