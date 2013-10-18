<?php
/**
 * Contributions by:
 *      Fayez Awad
 *      Yann Madeleine (http://www.yann-madeleine.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 *
 * @author Kevork Aghazarian
 * @website http://www.kevorkaghazarian.com
 * @website http://www.pzphp.com
 */

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
$PZPHP_CONFIG_ARRAY = array();
$PZPHP_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['ENV'] = getenv('PZPHP_ENVIRONMENT');

###INIT CONFIG###
PzPHP_Config::loadArray($PZPHP_CONFIG_ARRAY);
PzPHP_Config::loadConfig('config');

###INIT PZPHP###
$_PZPHP = new PzPHP_Core();

###INIT LOGGING###
$_PZPHP->log()
	->registerLog(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), PzPHP_Config::get('LOGS_DIR').'MYSQL')
	->registerLog(PzPHP_Config::get('SETTING_MEMCACHED_ERROR_LOG_FILE_NAME'), PzPHP_Config::get('LOGS_DIR').'MEMCACHED')
	->warmup();

###INIT LOCALIZATION###
$_PZPHP->locale()->addLanguage('en', 'en_us')->setCurrentLocale('en');

###CLEANUP###
$PZPHP_CONFIG_ARRAY = null;
unset($PZPHP_CONFIG_ARRAY);

###CUSTOM BOOTSTRAP###
include PzPHP_Config::get('BASE_DIR').'mybootstrap.php';
