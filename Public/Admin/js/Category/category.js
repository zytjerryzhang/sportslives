/**
 * 新增菜单列表项
 * @param mid
 */
function addCate(mid){
	$('#addcate_dialog').dialog({
	    autoOpen: true,
		modal: true,
		hide:"slide",
        show:"slide",
        title: '新增菜单列表',
		resizable: false,
		height:370,
		width: 500,
		buttons:{
    	'关闭': function() {
				$(this).dialog('close');
		 },
		'确定': function() {
				$.ajax({
					type: 'POST',
					url: LC._action+'Category/addCate/id/'+mid,
					data: $('#categoryForm').serializeArray(),
					async: false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('menuTips',data.info.toString());
						}else{
							$("#addcate_dialog").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}
	});
}


/**
 * 菜单栏移动
 * @param mid
 */
function moveCate(mid){
	$('#moveCate_dialog').dialog({
	    autoOpen: true,
		modal: true,
        show:"slide",
        hide:"slide",
        title: '移动菜单列表',
		resizable: false,
		height:240,
		width: 420,
		buttons:{
    	'关闭': function() {
				$(this).dialog('close');
		 },
		'确定': function() {
				$.ajax({
					type: 'POST',
					url: LC._action+'Category/moveCate/mid/'+mid,
					data: $('#moveForm').serializeArray(),
					async: false,
					dataType: 'json',
					cache: false,
					success: function(data){
						if(data.status != 1){
							LCPAGE.msg('moveTips',data.info.toString());
						}else{
							$("#moveCate_dialog").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}
	});
}