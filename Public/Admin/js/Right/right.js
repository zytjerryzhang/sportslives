/*
 * 新增权限列表项
 */
function addRight(){
	$('#addright_dialog').dialog({	   
	    autoOpen: true,
		modal: true,
        show:"slide",
        hide:"slide",
        title: '新增权限信息列表',
		resizable: false,
		height:320,	
		width: 550,
		buttons:{
	    	'关闭': function() {
				$(this).dialog('close');
			 },
			'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'Right/addRight',
					data:$('#rightForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('addRightTips',data.info.toString());
						}else{
							$("#addright_dialog").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}	 
	});
}	
