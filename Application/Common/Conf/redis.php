<?php
return array(
	//数据缓存设置
	'DATA_CACHE_TIME'       => '0',      // 数据缓存有效期 0表示永久缓存
	'DATA_CACHE_COMPRESS'   => false,   // 数据缓存是否压缩缓存
	'DATA_CACHE_CHECK'      => false,   // 数据缓存是否校验缓存
	'DATA_CACHE_PREFIX'     => '',     // 缓存前缀
	'DATA_CACHE_TYPE'       => 'Redis',  // 数据缓存类型,

	//Redis设置
	'REDIS_HOST'            => '127.0.0.1', //主机
	'REDIS_PORT'            => '6379', //端口
	'DATA_CACHE_TIMEOUT'	=> 0,	//连接超时时间(S) 0:永不超时

);

