<?php

	class indexController extends Bee_Controller_Action{

		public function indexAction()
		{
			$this->view('data','hello bee');
			$this->render('index');
		}
		public function twigAction()
		{
			$this->view('data','hello bee');
			$this->display('twig');
		}
	}
	