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
	 * @package Pz_Helper_Misc
	 */
	class Pz_Helper_Misc
	{
		/*
		 * Regenerates a unique mt_rand seed
		 */
		public static function regenerateMtRandSeed()
		{
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * mt_rand(1,999999));

			mt_srand($seed+mt_rand(1,1000));
		}
	}
