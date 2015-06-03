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
	###AUTOLOAD###
	spl_autoload_register(function($className)
	{
		$fileNameParts = explode('_', $className);
		$fileName = __DIR__.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $fileNameParts).'.php';

		if(file_exists($fileName))
		{
			include $fileName;
		}
		else
		{
			throw new Exception('Failed to load "'.$className.'"! File "'.$fileName.'" does not exist!');
		}
	});

	###BASE CONFIG###
	$PZPHP_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$PZPHP_CONFIG_ARRAY['ENV'] = 'local';

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
