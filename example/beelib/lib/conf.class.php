<?php
	class conf{
		private $conf;

		private function __construct($path){
			$this->conf = parse_ini_file($path);
		}
		/*单例模式*/
	  	private function __clone(){}

		static public function instance(){

	    	static $singleton = NULL;
		    
		    if (is_null($singleton)) {
		      $singleton = new self(CONF.'/application.ini');
		    }

	    	return $singleton->conf;
	  	}
	}