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

	$PZ_CONFIG_ARRAY = array();

	/*########################################################################################*/
	/**********THE FOLLOWING CAN BE REMOVED IF YOU DO NOT PLAN TO USE THE Pz_Debugger**********/
	/*#*/$PZ_CONFIG_ARRAY['PZD_START_MICROTIME'] = microtime(true);                        /*#*/
	/*#*/$PZ_CONFIG_ARRAY['PZD_START_MEMORY_USE'] = memory_get_usage();                    /*#*/
	/*#*/$PZ_CONFIG_ARRAY['PZD_START_MEMORY_USE_REAL'] = memory_get_usage(true);           /*#*/
	/*#*/$PZ_CONFIG_ARRAY['PZD_START_MEMORY_PEAK_USE'] = memory_get_peak_usage();          /*#*/
	/*#*/$PZ_CONFIG_ARRAY['PZD_START_MEMORY_PEAK_USE_REAL'] = memory_get_peak_usage(true); /*#*/
	/**********THE FOLLOWING CAN BE REMOVED IF YOU DO NOT PLAN TO USE THE Pz_Debugger**********/
	/*########################################################################################*/

	$PZ_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$PZ_CONFIG_ARRAY['BASE_CLASS_DIR'] = $PZ_CONFIG_ARRAY['BASE_DIR'];
	$PZ_CONFIG_ARRAY['PZ_INC_DIR'] = $PZ_CONFIG_ARRAY['BASE_DIR'].'Pz'.DIRECTORY_SEPARATOR;
	$PZ_CONFIG_ARRAY['PZ_LOGS_DIR'] = $PZ_CONFIG_ARRAY['BASE_DIR'].'LOGS'.DIRECTORY_SEPARATOR;
	$PZ_CONFIG_ARRAY['PZPHP_ENVIRONMENT'] = getenv('PZPHP_ENVIRONMENT');

	require_once $PZ_CONFIG_ARRAY['PZ_LOGS_DIR'].'ClassAutoloader.php';

	new Pz_ClassAutoloader($PZ_CONFIG_ARRAY['BASE_CLASS_DIR']);

	PzPHP_Config::loadArray($PZ_CONFIG_ARRAY);

	$envSuffix = ($PZ_CONFIG_ARRAY['PZPHP_ENVIRONMENT'] === 'production'?'':'_'.$PZ_CONFIG_ARRAY['PZPHP_ENVIRONMENT']);

	PzPHP_Config::loadFile($PZ_CONFIG_ARRAY['BASE_DIR'].'config'.$envSuffix.'.php');

	$_PZPHP = new PzPHP_Core();

	PzPHP_Config::loadFile($PZ_CONFIG_ARRAY['BASE_DIR'].'my_config'.$envSuffix.'.php');

	//----------------------BEGIN YOUR MAGIC BELOW----------------------//
