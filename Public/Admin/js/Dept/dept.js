/**
 * 新增部门列表项
 */
function addDept(){
	$('#dlg_addDept').dialog({
	    autoOpen: true,
		modal: true,
        show:'slide',
        hide:'slide',
        title: '新增部门信息列表',
		resizable: false,
		height:380,
		width: 500,
		buttons:{
    	'关闭': function() {
				$(this).dialog('close');
		 },
		'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'Dept/addDept',
					data:$('#deparForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('deptTips',data.info);
						}else{
							$("#dlg_addDept").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}
	});
}
