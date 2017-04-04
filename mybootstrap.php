<?php
	/*
	 *
	 *
	 * EVERYTHING IN THIS SCRIPT IS PROVIDED AS A BASE STARTING POINT
	 *
	 *
	 */

	###INIT LOGGING###
	$_PZPHP->log()
		->registerLog(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), PzPHP_Config::get('LOGS_DIR').'MYSQL')
		->warmup();

	PzPHP_Config::loadFile($PZPHP_CONFIG_ARRAY['BASE_DIR'].'my_config.php');
	PzPHP_Config::loadConfig('my_config');

	$_PZPHP->routing()->setSiteUrl(PzPHP_Config::get('ROOT_URL'));
	$_PZPHP->routing()->setBaseUri(PzPHP_Config::get('ROOT_URI'));

	###ROUTING RULES REGEXP###
	define('ROUTING_LANG_REG_EX', '[a-z]{2,3}+');
	define('ROUTING_ID_REG_EX', '[0-9]+');
	define('ROUTING_NAME_REG_EX', '[a-zA-Z0-9\-]+');

	$_PZPHP->routing()->add('home', '/', 'Controller_Home', 'indexAction', array(), true);

	try
	{
		session_set_cookie_params(0, '/', PzPHP_Config::get('COOKIE_URL'), false, true);
		session_start();

		if(strlen(session_id()) != 64 || session_id() === '')
		{
			session_destroy();
			session_id(PzPHP_Helper_String::createCode(64));
			session_start();
		}

		$_PZPHP->db()->add(PzPHP_Config::get('DB_USER'),PzPHP_Config::get('DB_PASSWORD'),PzPHP_Config::get('DB_NAME'),PzPHP_Config::get('DB_HOST'),PzPHP_Config::get('DB_PORT'));

		echo $_PZPHP->routing()->listen();
	}
	catch(Exception $e)
	{
		if(in_array($e->getCode(), array(PzPHP_Helper_Codes::VIEW_NOT_FOUND,PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE)))
		{
			$_PZPHP->response()->setHeader('Status', '404 Not Found');
			$_PZPHP->response()->setHeader('HTTP/1.0 404 Not Found');
			$_PZPHP->locale()->addLanguage('en', 'en-us');
			echo $_PZPHP->view()->render('404', array('exceptionMsg' => $e->getMessage(), 'exceptionCode' => $e->getCode()));
		}
		else
		{
			$_PZPHP->response()->setHeader('Status', '500 Internal Server Error');
			$_PZPHP->response()->setHeader('HTTP/1.0 500 Internal Server Error');
			$_PZPHP->locale()->addLanguage('en', 'en-us');
			echo $_PZPHP->view()->render('500', array('exceptionMsg' => $e->getMessage(), 'exceptionCode' => $e->getCode()));
		}
	}
