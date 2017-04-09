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
					die('找不到视图文件！');
				}
			}else if ($this->is_display_error){
				die('<h1>error: 不能同时引入多次视图！</h1>');
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
	
