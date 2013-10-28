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
 * @package Pz Library
 */
/**
 * A helper class that contains various, unrelated methods that help with various, unrelated tasks.
 */
class PzPHP_Helper_Misc
{
	/**
	 * Regenerates the mt_rand using a unique seed.
	 */
	public static function regenerateMtRandSeed()
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * mt_rand(mt_rand(1,1000000000),mt_rand(1000000001,2000000000)));

		mt_srand(
			(int)$seed+mt_rand(mt_rand(1,1000000000),mt_rand(1000000001,2000000000))
		);
	}
}
