/**
 * 新增用户信息列表项
 */
function addUser(){
	$('#adduser_dialog').dialog({
	    autoOpen: true,
		modal: true,
        show:"slide",
        hide:"slide",
        title: '新增用户信息列表',
		resizable: false,
		height:580,
		width: 570,
		buttons:{
	    	'关闭': function() {
				$(this).dialog('close');
			 },
			'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'User/addUser',
					data:$('#userForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('addUserTips',data.info.toString());
						}else{
							$("#adduser_dialog").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}
	});
}
/*
 * 编辑用户权限
 */
function editUserRight(uid,name){
	$("#loadRightData").html('');
	$.ajax({
		type:'POST',
		url:LC._action+'Right/getUserRight/uid/'+uid,
		data:$('#userRightForm').serializeArray(),
		async:false,
		dataType: 'json',
		cache:false,
		success:function(data){
			$("#loadRightData").html('');
			var html = '';
			$.each(data.menu_list, function(key, row){
				html+='<div class="myClear" style="margin-top:25px;background:#F2F8FD;cursor:pointer;text-align:center;font-weight:bold;line-height:28px;"><input type="checkbox" ModuleId="'+row.id+'" onclick="checkModul(this)"/>&nbsp;&nbsp;<span onclick="showHide('+row.id+')">'+row.name+'</span></div>';
				html+='<div class="func_'+row.id+'">';
				$.each(data.right_list, function(i, v){
					if( row.id == v.grp ) {
						var check = ( v.isper == 1 ) ? "checked='checked'" : '';
						var color = ( v.isper == 1 && v.isroleper != 1 ) ? 'color:red;' : ( v.isroleper == 1 ? 'color:green;' : ''); //用户权限 角色权限
						html+= '<li style="display:inline; '+color+' float:left; width:225px;" ><input style="margin:5px 3px;" type="checkbox" '+check+' name="rid[]" value="'+v.id+'">&nbsp;'+v.name+'&nbsp;&nbsp;</li>';
					}
				});
				html+='</div>';
			});
			$("#loadRightData").append(html);
		}
	});
	$('#editRight_dialog').dialog({
	    autoOpen: true,
		modal: true,
        show:"slide",
        hide:"slide",
        title: '给用户【'+name+'】分配权限',
		resizable: false,
		height:750,
		width: 1050,
		buttons:{
	    	'关闭': function() {
				$(this).dialog('close');
			 },
			'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'Right/setUserRight/uid/'+uid,
					data:$('#userRightForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('editUserRightTips',data.info.toString());
						}else{
							$("#editRight_dialog").dialog('close');
						}
					}
				});
			}
		}
	});
}
/**
 * 隐藏
 * @param ref
 */
function showHide(ref){
	$('div[class=func_'+ref+']').slideToggle();
}
/**
 * 指定模块全选中
 * @param obj
 */
function checkModul(obj) {
	var module = $(obj).attr('ModuleId');
	var checked = ($(obj).attr('checked') == 'checked') ? false : 'checked' ;
	$(".func_"+module+" li").each(function(k,v){
		if( $(v).css("display")!="none" ){
			$(v).find("input[type='checkbox']").prop('checked',checked);
		}
	});
	$(obj).prop('checked',checked);
}
/*
 * 全选和全不选
 */
function checkAll(obj){
	var checked = ($(obj).attr('checked') == 'checked') ? false : 'checked' ;
	$("#loadRightData li").each(function(k,v){
		if( $(v).css("display")!="none" ){
			$(v).find("input[type='checkbox']").prop('checked',checked);
		}
	});
	$(obj).attr('checked',checked);
}
/*
 * 选中取消
 */
$("#loadRightData").delegate("input[type='checkbox']", 'click', function() {
	var checked = ($(this).attr('checked') == 'checked') ? false : 'checked' ;
	$(this).attr('checked',checked);
});
/*
 * 过滤
 */
$(document).delegate("#selKeywords", 'keyup', function () {
	$("#userCheck").attr('checked',false);
    var keywords = $(this).val().toLowerCase();
    $("#loadRightData li").hide().filter(function(){
        return $(this).text().toLowerCase().indexOf(keywords)!==-1; //验证输入字符在所有tr中首次出现的位置
    }).show().keyup();
});
