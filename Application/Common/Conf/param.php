<?php
return array(
	'RE_USERNAME' => array(
		'DataType' => 'string',
		'Pattern' => '/^[A-Za-z][A-Za-z0-9_]{3,15}$/',
		'Desc' => '用户名为4至16个英文字母、数字和下划线的组合，且必须以英文字母开头。',
		'Err' => '',
	),
	'RE_PASSWD' => array(
		'DataType' => 'string',
		'Pattern' => '/^[a-zA-Z][a-zA-Z0-9]{5,17}$/',
		'Desc' => '密码为6至18个英文字母、数字的组合，且必须以英文字母开头。',
		'Err' => '',
	),
	'RE_MOBILE' => array(
		'DataType' => 'string',
		'Pattern' => '/^1[0-9]{10}$/',
		'Desc' => '手机号码为11位数字。',
		'Err' => '',
	),
	'RE_QQ' => array(
		'DataType' => 'string',
		'Pattern'  => '/^\d{5,15}$/',
		'Desc'	   => 'qq数为5至15位数字。',
		'Err'	   => '',
	),
	'RE_EMAIL' => array(
		'DataType' => 'string',
		'Pattern'  => '/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/',
		'Desc'	   => '邮箱格式不正确。',
		'Err'	   => '',
	),
	'RE_POSTCODE' => array(
		'DataType' => 'string',
		'Pattern'  => '/^[1-9]\d{5}$/',
		'Desc'	   => '邮编格式不对。',
		'Err'	   => '',
	),
);