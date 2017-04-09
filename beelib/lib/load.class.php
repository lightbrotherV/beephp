<?php
//加载类
	/*
		进行文件的加载
		封装底层加载函数
	*/
	class loadClass{

		//将已经加载的类文件保存，防止重复加载
		static public $loadClass = array();
		
		//按照传入路径加载文件函数
		/*
		def 在入口文件定义的宏，
		type文件类型
		class类名
		suffix文件后缀
		*/
		static public function loadfile($def,$type,$class,$suffix){
			$path = $def.'/'.$type.'/'.$class.$suffix;
			if (isset($loadClass[$class])){
				return true;
			}else{
				if (is_file($path)){
					include_once $path;
					
					self::$loadClass[$class] = $path;
				}else {
					return false;
				}
			}
		}

		static public function loadLib($class){
			//自动未定义加载lib类库
			loadClass::loadfile(LIB,'lib',$class,'.class.php');
		}

		static public function loadController($class){
			//自动未定义加载Controller类库
			loadClass::loadfile(APP,'controllers',$class,'.class.php');
			//自动加载完之后要注销
			spl_autoload_unregister('loadClass::loadController');
		}

		
		static public function loadModel($class){  
			//自动未定义加载Model类库
			loadClass::loadfile(APP,'models',$class,'.class.php');
			//自动加载完之后要注销
			spl_autoload_unregister('loadClass::loadModel');
		}


		//自动加载所需类的文件和基类文件
		static public function Auto_Load($type){
			switch ($type) {
				case 'lib':
						spl_autoload_register('loadClass::loadLib');
						break;
				case 'controller':
						self::loadfile(LIB,'lib','action','.class.php');
						spl_autoload_register('loadClass::loadController');
						break;
				case 'model':
						self::loadfile(LIB,'lib','model','.class.php');
						spl_autoload_register('loadClass::loadModel');
						break;
			}
		}
	}
