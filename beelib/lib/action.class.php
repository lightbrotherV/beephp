<?php
	class Bee_Controller_Action{
		private $is_display_view;
		private $is_display_error;
		private $controller;
		private $action;
		private $is_forward;
		public $view = array();
		public function __construct($controller,$action){
			$this->is_display_view = false ;
			$this->is_display_error = conf::instance()['Display_Error'];
			$this->controller = $controller;
			$this->action = $action;
		}

		//使用twig模版引擎
		public function display($file){
			Twig_Autoloader::register();
			$loader = new Twig_Loader_Filesystem(APP.'/views/'.$this->controller);
			$twig = new Twig_Environment($loader, array(
			    'cache' => APP.'/views/cache',
			));
			$template = $twig->load($file.'.phtml');
			$template->display($this->view? $this->view : NULL);
			$this->is_display_view = true ; 
		}
		
		public function view($key,$val){
			$this->view[$key] = $val;
		}

		public function render($viewName = 'index'){
			if (!$this->is_display_view){
				$this->is_display_view = true;
				$path = APP.'/views/'.$this->controller.'/'.$viewName.'.phtml';
				if (file_exists($path)){
					include_once($path);
				}else if ($this->is_display_error){
					throw new RuntimeException('找不到视图文件！');
				}
			}else if ($this->is_display_error){
				throw new RuntimeException('不能同时引入多次视图！');
			}
		}
		public function redirect($url){
			$match = explode("/",$url);
			if ($match[1]!=$this->controller || $match[2]!==$this->action){
				header('Location:'.$url);
			}
			
		}

		public function getModel($modelName){
			loadClass::Auto_Load('model');
			$className = $modelName.'Model';
			return new $className();
		}
		
	}
?>
	
