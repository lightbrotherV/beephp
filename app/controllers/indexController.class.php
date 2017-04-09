<?php 
class indexController extends Bee_Controller_Action{

		public function indexAction()
		{
			$this->view('data','Bee PHP');
			$this->render('index');
		}
}
