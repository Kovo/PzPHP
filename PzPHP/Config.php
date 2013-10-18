<?php
class PzPHP_Config
{
	/**
	 * @var array
	 */
	protected static $_CONFIGS = array();

	/**
	 * @param $fileName
	 * @throws Exception
	 */
	public static function loadFile($fileName)
	{
		if(file_exists($fileName))
		{
			require $fileName;

			if(isset($PZPHP_CONFIG_ARRAY) && is_array($PZPHP_CONFIG_ARRAY) && !empty($PZPHP_CONFIG_ARRAY))
			{
				foreach($PZPHP_CONFIG_ARRAY as $key => $value)
				{
					self::$_CONFIGS[$key] = $value;
				}
			}
		}
		else
		{
			throw new Exception('Config file "'.$fileName.'" does not exist!');
		}
	}

	/**
	 * @param $name
	 */
	public static function loadConfig($name)
	{
		$fileName = self::get('BASE_DIR').$name.'_'.self::get('ENV').'.php';

		self::loadFile($fileName);
	}

	/**
	 * @param $array
	 */
	public static function loadArray($array)
	{
		if(is_array($array) && !empty($array))
		{
			foreach($array as $key => $value)
			{
				self::$_CONFIGS[$key] = $value;
			}
		}
	}

	/**
	 * @param $configKeyName
	 * @return mixed
	 * @throws Exception
	 */
	public static function get($configKeyName)
	{
		if(isset(self::$_CONFIGS[$configKeyName]))
		{
			return self::$_CONFIGS[$configKeyName];
		}
		else
		{
			throw new Exception('Config "'.$configKeyName.'" does not exist!');
		}
	}

	/**
	 * @return array
	 */
	public static function getAll()
	{
		return self::$_CONFIGS;
	}
}
