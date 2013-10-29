<?php
class PzPHP_Helper_String
{
	/**
	 * @var int
	 */
	const ALPHANUMERIC = 0;

	/**
	 * @var int
	 */
	const ALPHANUMERIC_PLUS = 1;

	/**
	 * @var int
	 */
	const HEX = 2;

	/**
	 * @param $length
	 * @param int $type
	 * @param bool $regenerateSeed
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
			PzPHP_Helper_Misc::regenerateMtRandSeed();
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
	 * @return bool
	 */
	public static function unserializable($string)
	{
		if(!is_string($string))
		{
			return false;
		}

		$string = trim($string);

		if($string === '')
		{
			return false;
		}

		if($string === 'b:0;')
		{
			return true;
		}

		$length	= strlen($string);
		$end = '';

		switch($string[0])
		{
			case 's':
				if($string[$length - 2] !== '"')
				{
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';

				if($string[1] !== ':')
				{
					return false;
				}

				switch($string[2])
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						break;
					default:
						return false;
				}
			case 'N':
				$end .= ';';
				if($string[$length - 1] !== $end[0])
				{
					return false;
				}

				break;
			default:
				return false;
		}

		if(@unserialize($string) === false)
		{
			return false;
		}

		return true;
	}
}
