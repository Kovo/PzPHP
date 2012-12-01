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
	/**
	 * The bootstrap is meant for you to include all your important logic.
	 *
	 * It can also be used for routing, among other things.
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

	$_PZPHP->registerVariable('age', 17);
	$_PZPHP->registerVariable('ageLimit', 18);

	function isOfDrinkingAge(PzPHP_Core $pzphpcore)
	{
		if($pzphpcore->getVariable('age') >= $pzphpcore->getVariable('ageLimit'))
		{
			return true;
		}
		else
		{
			//dice roll to see if you will lie
			if(mt_rand(1,2) === 1)
			{
				lieAboutAge($pzphpcore);

				return isOfDrinkingAge($pzphpcore);
			}
			else
			{
				return false;
			}
		}
	}

	function lieAboutAge(PzPHP_Core $pzphpcore)
	{
		$pzphpcore->changeVariable('age', 22);
	}

	//this will return true if the dice roll is 1 or age is 18 or above
	if(isOfDrinkingAge($_PZPHP))
	{
		echo 'Get in!';
	}
	else
	{
		echo 'Get out!';
	}

	//remove our variables
	$_PZPHP->unregisterVariable('age');
