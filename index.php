<?php

/**
*1.入口文件
*2.定义常量
*3.加载函数库和类库
*4.启动框架
*/



	define ('BEE',str_replace('\\','/',realpath(dirname(__FILE__)))); 	  //框架根目录
	define ('LIB',BEE.'/beelib');//库文件目录
	define('APP', BEE.'/app');    //视图控制器模型目录
	define('CONF',APP.'/config'); //配置文件的目录
	define('SELFLIB',LIB.'/extlib/self'); //自己写的库
	define('PUBLICRES',BEE.'/public');  //CSS  JS  图片等资源

	include_once LIB.'/lib/load.class.php';//加载“加载类”

	loadClass::loadfile(LIB,'lib','beePhp','.class.php'); //加载框架类

	beePhp::start();   //打开框架