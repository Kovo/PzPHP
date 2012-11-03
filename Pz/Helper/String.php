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
	 * @package Pz_Helper_String
	 */
	class Pz_Helper_String
	{
		const ALPHANUMERIC = 0;
		const ALPHANUMERIC_PLUS = 1;
		const HEX = 2;

		/**
		 * @param      $length
		 * @param int  $type
		 * @param bool $regenerateSeed
		 *
		 * @return string
		 */
		public static function createCode($length, $type = self::ALPHANUMERIC, $regenerateSeed = true)
		{
			if($type === self::ALPHANUMERIC)
			{
				$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
			}
			elseif($type === self::ALPHANUMERIC_PLUS)
			{
				$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz`~!@#$%^&*()_+|}{:?><,./;'[]-=";
			}
			else
			{
				$chars = "0123456789abcdef";
			}

			$amountChars = strlen($chars);

			if($regenerateSeed)
			{
				Pz_Helper_Misc::regenerateMtRandSeed();
			}

			$pass = '';

			for($i=0;$i<$length;$i++)
			{
				$num = mt_rand()%$amountChars;
				$tmp = substr($chars, $num, 1);
				$pass = $pass.$tmp;
			}

			return $pass;
		}

		/**
		 * @param $string
		 *
		 * @return bool
		 */
		public static function unserializable($string)
		{
			return (!@unserialize($string)?false:true);
		}
	}
