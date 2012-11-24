<?php
	/**
	 * Contributions by:
	 *      Fayez Awad
	 *      Yann Madeleine (http://www.yann-madeleine.com)
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 */

	/*#############################################################################*/
	/*****THE FOLLOWING CAN BE REMOVED IF YOU DO NOT PLAN TO USE THE Pz_Debugger*****/
	/*#*/define('PZD_START_MICROTIME', microtime(true));                        /*#*/
	/*#*/define('PZD_START_MEMORY_USE', memory_get_usage());                    /*#*/
	/*#*/define('PZD_START_MEMORY_USE_REAL', memory_get_usage(true));           /*#*/
	/*#*/define('PZD_START_MEMORY_PEAK_USE', memory_get_peak_usage());          /*#*/
	/*#*/define('PZD_START_MEMORY_PEAK_USE_REAL', memory_get_peak_usage(true)); /*#*/
	/*****THE FOLLOWING CAN BE REMOVED IF YOU DO NOT PLAN TO USE THE Pz_Debugger*****/
	/*#############################################################################*/

	define('BASE_DIR', __DIR__.DIRECTORY_SEPARATOR);
	define('BASE_CLASS_DIR', BASE_DIR);
	define('PZ_INC_DIR', BASE_DIR.'Pz'.DIRECTORY_SEPARATOR);
	define('PZ_LOGS_DIR', BASE_DIR.'LOGS'.DIRECTORY_SEPARATOR);
	define('PZPHP_ENVIRONMENT', getenv('PZPHP_ENVIRONMENT'));

	require_once BASE_DIR.'config'.(PZPHP_ENVIRONMENT === 'production'?'':PZPHP_ENVIRONMENT).'.php';
	require_once BASE_DIR.'my_config'.(PZPHP_ENVIRONMENT === 'production'?'':PZPHP_ENVIRONMENT).'.php';
	
	require_once PZ_INC_DIR.'ClassAutoloader.php';

	new Pz_ClassAutoloader();

	$_PZPHP = new PzPHP_Core();

	//----------------------BEGIN YOUR MAGIC BELOW----------------------//
