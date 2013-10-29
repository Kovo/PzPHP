<?php
class PzPHP_Helper_Misc
{
	public static function regenerateMtRandSeed()
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * mt_rand(mt_rand(1,1000000000),mt_rand(1000000001,2000000000)));

		mt_srand(
			(int)$seed+mt_rand(mt_rand(1,1000000000),mt_rand(1000000001,2000000000))
		);
	}
}
