<?php
header("Content-type:text/html;charset=utf-8");
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

#los2 add begin
// 绑定Home模块到当前入口文件
//define('BIND_MODULE','Home');

// 绑定Admin模块到当前入口文件
// define('BIND_MODULE','Admin');
// define('BUILD_CONTROLLER_LIST','Index');
// define('BUILD_MODEL_LIST','Index');

// 绑定Server模块到当前入口文件
// define('BIND_MODULE','Server');
// define('BUILD_CONTROLLER_LIST','Index');
// define('BUILD_MODEL_LIST','Index');


#los2 add end

// 定义应用目录
define('APP_PATH', dirname(__DIR__) . '/Application/');

// 引入ThinkPHP入口文件
require dirname(__DIR__) . '/ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
