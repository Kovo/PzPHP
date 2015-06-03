<?php
class Controller_Home extends Controller_Controller
{
	/**
	 * @return string
	 */
	public function indexAction()
	{
		return $this->_PZPHP->view()->render('index');
	}
}
