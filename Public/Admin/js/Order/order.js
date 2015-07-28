var currentPage = 1;
var pageSize = 15;
var orderby = '';
var c_status = 1;
var browseType = 'fold';	//浏览类型
loadData(0);
/**
 * 排序
 * @param vSelf
 */
function listOrderBy(vSelf){
	var by = $(vSelf).attr('by');
	$(vSelf).parent().parent().find('a').each(function(){
		$(this).attr('by','desc');
		$(this).find('img').remove();
	});
	$(vSelf).attr('by',((by == 'asc') ? 'desc' : 'asc'));
	var dir = '<img src="/Admin/images/';
	$(vSelf).append(((by == 'asc') ? dir+'s_asc.png" />' : dir+'s_desc.png" />'));
	var ref = $(vSelf).attr('ref');
	switch(ref){
		case 'earn':
			earn_order = $(vSelf).attr('col')+';'+by;
			listEarn();
			break;
		case 'inv':
			inv_order = $(vSelf).attr('col')+';'+by;
			listInvest();
			break;
		default:
			orderby = $(vSelf).attr('col')+';'+by;
			loadData(c_status);
			break;
	}
}
//浏览风格
function swichBrowseType(vSelf) {
	if(browseType=='fold'){
		browseType = 'unfold';
		$(vSelf).val('收起');
	} else {
		browseType = 'fold';
		$(vSelf).val('展开');
	}
	loadData(c_status);
}
/**
 * 订单列表
 * @param status
 */
function loadData(status){
	var url=LC._action+"Order/orderList/currentPage/"+currentPage+"/pageSize/"+pageSize+"/status/"+status;
	orderby != '' ? url += "/orderby/"+orderby : '';
	url+='/browsetype/'+browseType;
	$("#load_data"+status).html('');
	var html ='';
	$.ajax({
		type:'POST',
		url:url,
		data:$('#seachForm').serializeArray(),
		async:false,
		dataType: 'json',
		cache:false,
		success:function(data){
			if(data.list==null) return;
			$.each(data.list, function(key, row){
				var rowStyle = (key%2!=0) ? "divGray" : "divWhite";
				html+='<tr title="'+row.title+'" id="tr'+row.id+'" class="'+rowStyle+'">';
				html+='<td style="height:28px;"><input type="checkbox" name="status[]" value="'+row.id+'"></td>';
				html+="<td title='"+row.order_no+"'>"+row.order_no+"&nbsp;</td>";
				html+="<td title='"+row.user_id+"'>"+row.username+"&nbsp;</td>";
				html+="<td title='"+row.mobile+"'>"+row.mobile+"&nbsp;</td>";
				html+="<td title='"+row.order_money+"'>"+(row.order_money/100)+"&nbsp;</td>";
				html+="<td title='"+row.gym_id+"'>"+row.name+"&nbsp;</td>";
				html+="<td title='"+row.pro_name+"'>"+row.pro_name+"&nbsp;</td>";
				html+="<td title='"+row.pay_s_name+"'>"+row.pay_s_name+"&nbsp;</td>";
				html+="<td title='"+row.status_name+"'>"+row.status_name+"&nbsp;</td>";
				html+="<td title='"+row.create_time+"'>"+row.create_time+"&nbsp;</td>";
				html+='<td>--</td>';
				html+='</tr>';
				if(row.item_list != undefined && row.item_list != null){
					$.each(row.item_list, function(k, v){
						html+='<tr title="'+row.title+'" id="tr'+v.id+'" style="height:28px;">';
						html+="<td title='场地名称'>"+v.site_name+"&nbsp;</td>";
						html+="<td title='预定人数'>"+v.order_number+"&nbsp;</td>";
						html+="<td title='日期'>"+v.date+"&nbsp;</td>";
						html+="<td title='开始时间'>"+v.begin_time+"&nbsp;</td>";
						html+="<td title='结束时间'>"+v.end_time+"&nbsp;</td>";
						html+="<td title='消费密码'>"+v.pwd_no+"&nbsp;</td>";
						html+="<td title='券码使用状态'>"+(v.pwd_status==1 ? '已使用' : '未使用')+"&nbsp;</td>";
						html+="<td title='价格'>"+(v.price/100)+"&nbsp;</td>";
						html+="<td title='创建时间'>"+v.create_time+"&nbsp;</td>";
						html+="<td title=''>"+'空缺'+"&nbsp;</td>";
						html+="<td title=''>"+'空缺'+"&nbsp;</td>";
						html+='</tr>';
					});
				}
			});
			$("#load_data"+status).html(html);
			c_status = status;
			pageShow(data.pages.currentPage,data.pages.count);
		}
	});
}
/**
 * 分页处理
 * @param current
 * @param count
 */
function pageShow(current,count){
    $("#Pagination").pagination(count, {
		current_page:current-1,
		items_per_page:pageSize,
        callback: function(page, jq){
	    	currentPage = page+1;
	    	loadData(c_status);
    	}
    });
}
function changePageSize(obj){
	if(isNaN($(obj).val())) return false;
	pageSize = $(obj).val();
	loadData(c_status);
}
/**
 * 搜索
 */
function search(){
	$("#listTabs").tabs({active :3});
	loadData(100);
}