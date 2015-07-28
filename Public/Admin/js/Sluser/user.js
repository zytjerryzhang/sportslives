//订单历史
var o_CurrentPage = 1;
var o_PageSize = 10;
var o_order = '';
//消费记录
var tx_CurrentPage = 1;
var tx_PageSize = 10;
var tx_order = '';
//队长
var team_CurrentPage = 1;
var team_PageSize = 10;
var team_order = '';
$(document).ready(function(){
	//详细页面加载tabs
	$("#tabs").tabs({});
});
//菜单项选择
function listOrderBy(vSelf){
	var by = $(vSelf).attr('by');
	$(vSelf).parent().parent().find('a').each(function(){
		$(this).attr('by','desc');
		$(this).find('img').remove();
	});
	$(vSelf).attr('by',((by == 'asc') ? 'desc' : 'asc'));
	var dir = '<img src="/Public/Admin/images/';
	$(vSelf).append(((by == 'asc') ? dir+'s_asc.png" />' : dir+'s_desc.png" />'));
	var ref = $(vSelf).attr('ref');
	switch(ref){
		case 'earn':
			earn_order = $(vSelf).attr('col')+';'+by;
			myOrder();
			break;
		case 'inv':
			inv_order = $(vSelf).attr('col')+';'+by;
			myTeam();
			break;
		default:
			tx_order = $(vSelf).attr('col')+';'+by;
			mytxlog();
			break;
	}
}