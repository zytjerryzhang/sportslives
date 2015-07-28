/*
 * 新增职位列表项
 */
function addPos(){
	$('#dlg_addPos').dialog({
	    autoOpen: true,
		modal: true,
        show:'slide',
        hide:'slide',
        title: '新增职位信息列表',
		resizable: false,
		height:420,
		width: 550,
		buttons:{
    	'关闭': function() {
				$(this).dialog('close');
		 },
		'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'Pos/addPos',
					data:$('#posForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('posTips',data.info.toString());
						}else{
							$("#dlg_addPos").dialog('close');
							LC.show($("#lcsubmit"));
						}
					}
				});
			}
		}
	});
}
/*
 * 编辑职位权限函数
 */
function editPosRight(pos_id,name){
	$.ajax({
		type:'POST',
		url:LC._action+'Pos/getPosRight/pos_id/'+pos_id,
		data:'',
		async:false,
		dataType: 'json',
		cache:false,
		success:function(data){
			$("#loadPosRightData").html('');
			var html = '';
			$.each(data.menu_list, function(key, row){
				html+='<div class="myClear" style="margin-top:25px;background:#F2F8FD;cursor:pointer;text-align:center;font-weight:bold;line-height:28px;"><input type="checkbox" ModuleId="'+row.id+'" onclick="checkModul(this)"/>&nbsp;&nbsp;<span onclick="showHide('+row.id+')">'+row.name+'</span></div>';
				html+='<div class="func_'+row.id+'">';
				$.each(data.right_list, function(i, v){
					if( row.id == v.grp ) {
						var check = ( v.isper == 1 ) ? "checked='checked'" : '';
						var color = ( v.isper == 1) ? 'color:red;' : '';
						html+= '<li style="display:inline; '+color+' float:left; width:225px;" ><input style="margin:5px 3px;" type="checkbox" '+check+' name="rid[]" value="'+v.id+'">&nbsp;'+v.name+'&nbsp;&nbsp;</li>';
					}
				});
				html+='</div>';
			});
			$("#loadPosRightData").append(html);
		}
	});
	//角色权限分配
	$('#editPosRight_dialog').dialog({
	    autoOpen: true,
		modal: true,
        show:"slide",
        hide:"slide",
        title: '给角色【'+name+'】分配权限',
		resizable: false,
		height:550,
		width: 950,
		buttons:{
	    	'关闭': function() {
				$(this).dialog('close');
			 },
			'确定': function() {
				$.ajax({
					type:'POST',
					url:LC._action+'Pos/setPosRight/pos_id/'+pos_id,
					data:$('#posRightForm').serializeArray(),
					async:false,
					dataType: 'json',
					cache:false,
					success:function(data){
						if(data.status != 1){
							LCPAGE.msg('editPosRightTips',data.info.toString());
						}else{
							$("#editPosRight_dialog").dialog('close');
							LC.show($("#lcsubmit"));
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
	$("#loadPosRightData li").each(function(k,v){
		if( $(v).css("display")!="none" ){
			$(v).find("input[type='checkbox']").prop('checked',checked);
		}
	});
	$(obj).attr('checked',checked);
}
/*
 * 选中取消  这是针对单点 项
 */
$("#loadPosRightData").delegate("input[type='checkbox']",'click', function() {
	var checked = ($(this).attr('checked') == 'checked') ? false : 'checked' ;
	$(this).attr('checked',checked);
});
/*
 * 检索
 */
$(document).delegate("#selKeywords",'keyup', function () {
	$("#posCheck").attr('checked',false);
    var keywords = $(this).val().toLowerCase();
    $("#loadPosRightData li").hide().filter(function(){
        return $(this).text().toLowerCase().indexOf(keywords)!==-1; //验证输入字符在所有tr中首次出现的位置
    }).show().keyup();
});
