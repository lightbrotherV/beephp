<?php
	
	/*
	框架运行类
	1.加载路由类
	2.加载控制器类
	*/

	class beePhp{
		
		static public function start(){
			//加载路由类
			loadClass::Auto_Load('lib');
			$route = new route();

			//加载composer中的第三方库
			if (file_exists(LIB.'/extlib/composer/vendor/autoload.php')){
				include_once LIB.'/extlib/composer/vendor/autoload.php';
			}

			//加载控制器类并运行
			loadClass::Auto_Load('controller');
			$actionName = $route->action;
			$controllerName = $route->controller.'Controller';
			$controllerClass = new $controllerName($route->controller,$route->action);
			$actionFunction = $route->action.'Action';
			$controllerClass->$actionFunction();
		}
	};