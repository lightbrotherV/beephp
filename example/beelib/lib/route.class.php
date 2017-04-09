<?php
	/*
	路由类
	1.获取控制名称
	2.获取控制器下的函数名
	3.获取模拟get参数
	*/

	class route{
		
		public $controller = 'index';
		public $action = 'index';
		
		public function __construct(){
			/*
				1.隐藏index.php/详情看doc
				例如xxx.com/index.php/index(控制器)/index(控制器下的函数)
			*/ 
			if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='/'){

				$path = $_SERVER['REQUEST_URI'];
				$pathArr = explode('/',trim($path,'/')); //根据‘/’切分出控制器和函数
				if (isset($pathArr[0])){
					$this->controller = $pathArr[0];
				}
				if (isset($pathArr[1])){
					$this->action = $pathArr[1];
				}			
				/*
					获取模拟get参数
					xxx.com/index.php/index/index/id/1/name/bee
					id=1,name=bee
				*/
				$count = count($pathArr)-2;
				for ($i = 2;$i <= $count;$i = $i+2){
					if (isset($pathArr[$i+1])){
						$_GET[$pathArr[$i]] = $pathArr[$i+1];
					}
				}
			}
		}



	}