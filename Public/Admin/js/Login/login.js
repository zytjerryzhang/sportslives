$(document).ready(function(){
	/**
	 * 按回车登录
	 */
	$(document).keydown(function(e) {
	    if(!e) e = window.event;  //火狐中是 window.event
	    if((e.keyCode || e.which) == 13){
	    	userLogin();
	    }
	});
	$('#username').click(function(){
		if($('#username').attr('placeholder') == '用户名')
			$('#username').attr('placeholder','')
	}).blur(function(){
		if($.trim($('#username').attr('placeholder')) == '')
			$('#username').attr('placeholder','用户名');
	});
	$('#passwd').click(function(){
		if($('#passwd').attr('placeholder') == '密码')
			$('#passwd').attr('placeholder','')
	}).blur(function(){
		if($.trim($('#passwd').attr('placeholder')) == '')
			$('#passwd').attr('placeholder','密码');
	});
});


/**
 * 用户登录
 */
function userLogin() {
	//获取参数
	var username = $.trim($('#username').val());
	var passwd = $.trim($('#passwd').val());
	var refurl = $.trim($('#refurl').val());

	//校验参数
	if(!username) {
		$('#checklogin').show().html('<font color="red">请填写用户名！</font>');
		setTimeout(function() {
			$('#checklogin').hide();
		}, 5000);
		return false;
	}
	if(!passwd){
		$('#checklogin').show().html('<font color="red">请填写用户密码！</font>');
		setTimeout(function() {
			$('#checklogin').hide();
		}, 5000);
		return false;
	}

	//登录
	$.ajax({
		type:'POST',
		url:'/Login/doLogin',
		data:{'username':username, 'passwd':passwd,'refurl':refurl},
		async:false,
		dataType: 'json',
		cache:false,
		success:function(data){
			if(data.status == 0){
				$('#checklogin').show().html('<font color="red">'+data.info+'</font>');
				setTimeout(function() {
					$('#checklogin').hide();
				}, 5000);
				return false;
			}else{
				window.location.href = data.refurl;
				return true;
			}
		}
	});
}