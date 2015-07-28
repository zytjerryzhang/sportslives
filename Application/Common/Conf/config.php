<?php
define('BK_SITE_URL','http://sl'); //后台站点 $_SERVER['HTTP_HOST']
define('WEB_SITE_URL','http://bk.sl');//前台站点
return array(
    'SHOW_PAGE_TRACE'       => true,
    'LOAD_EXT_CONFIG' 		=> array('database','status','province','redis'),
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式


    'LOAD_EXT_CONFIG' 		=> array('database','status','province','redis','param'),
    'URL_ROUTER_ON'=>true,
    'THINK_EMAIL' => array(
        'SMTP_HOST'   => '', //SMTP服务器
        'SMTP_PORT'   => '', //SMTP服务器端口
        'SMTP_USER'   => '', //SMTP服务器用户名
        'SMTP_PASS'   => '', //SMTP服务器密码
        'FROM_EMAIL'  => '', //发件人EMAIL
        'FROM_NAME'   => '', //发件人名称
        'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
        'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
    ),

    'URL_CASE_INSENSITIVE' =>true,   //不区分大小写
    //其它
    'URL_HTML_SUFFIX'=>'.html',

    //Los2：逗号（,）两边，不能有空格
    'DEFAULT_FILTER'        => 'trim,htmlspecialchars', // 默认参数过滤方法 用于I函数...
    'TMPL_CACHE_ON' 		=> false,      // 默认开启模板缓存
    'ACTION_CACHE_ON'  		=> false,
    'HTML_CACHE_ON'   		=> false,
    'DB_FIELD_CACHE' 		=> false,

    'URL_MODEL'             =>  2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'SESSION_AUTO_START'	=> true, //是否开启session

    'PAGESIZE' 				=> 13,


    'APP_SUB_DOMAIN_DEPLOY' => true, // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES' 	=> array(
        'sl' 						=> 'Home',
        'bk.sl' 					=> 'Admin',

		'www.sportslives.com.cn' 		=> 'Home',
		'www.bk.sportslives.com.cn' 	=> 'Admin',
    ), // 子域名部署规则

    'MULTI_MODULE' 			=> true, 								// 是否允许多模块 如果为false 则必须设置 DEFAULT_MODULE
    'MODULE_DENY_LIST' 		=> array('Common', 'Runtime'), 		// 禁止访问的模块列表
    'MODULE_ALLOW_LIST' 	=> array('Home', 'Admin'), 			// 允许访问的模块列表
    'DEFAULT_MODULE' 		=> 'Home', 							// 默认模块
    'DEFAULT_CONTROLLER' 	=> 'Index', 						// 默认控制器名称

    'EMAILCONFIG' => array(
        'use_email' => 'on',
        'smtp' => '',
        'port' => '',
        'emailname' => '',
        'emailpwd' => '',
        'CharSet' => '',
        'returnemail' => ''
    ),
    //付款配置
    'PAY_ACCOUNT'=>array(
        'wangyin'=>array(
            'v_mid' => '1001',   //测试
            'v_moneytype' => 'CNY',
            'key' => 'test',  //测试密钥   @todo 上线修改
            'payUrl' => 'https://Pay3.chinabank.com.cn/PayGate',
            'v_url' => WEB_SITE_URL.'/pay/rechargeWangyin', //通知商户页面端地址
            'remark2' =>'[url:='.WEB_SITE_URL.'/recharge/backWangyin]', //服务器底层通知地址
        )
    ),

    'TMPL_ACTION_ERROR' => 'Public:jump',
    'TMPL_ACTION_SUCCESS' => 'Public:jump',
);
