<?php
/**
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 *
 * @author Kevork Aghazarian
 * @website http://www.kevorkaghazarian.com
 * @website http://www.pzphp.com
 */
try
{
	###USE WITH HTACCESS PROTECTION###
	if(isset($_GET['action']) && $_GET['action'] === 'htaccessProtection')
	{
		exit();
	}

	###AUTOLOAD###
	spl_autoload_register(function ($className, $fileExtensions = null ){
		$className = str_replace ('_', '/', $className);
		$className = str_replace ('\\', '/', $className);

		$file = stream_resolve_include_path(__DIR__.DIRECTORY_SEPARATOR.$className.'.php');

		if($file === false)
		{
			$file = stream_resolve_include_path(__DIR__.DIRECTORY_SEPARATOR.strtolower($className.'.php'));
		}

		if($file !== false)
		{
			include $file;

			return true;
		}

		return false;
	});

	###BASE CONFIG###
	$PZPHP_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$PZPHP_CONFIG_ARRAY['ENV'] = getenv('PZPHP_ENVIRONMENT');

	###INIT CONFIG###
	PzPHP_Config::loadArray($PZPHP_CONFIG_ARRAY);
	PzPHP_Config::loadConfig('config');

	###INIT PZPHP###
	$_PZPHP = new PzPHP_Core();
}
catch(Exception $e)
{
	error_log('PzPHP start-up error. Msg: '.$e->getMessage().' / Code: '.$e->getCode());
	exit();
}

###CUSTOM BOOTSTRAP###
include PzPHP_Config::get('BASE_DIR').'mybootstrap.php';
