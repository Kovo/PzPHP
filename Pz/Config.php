<?php
	class Pz_Config
	{
		/**
		 * @var array
		 */
		protected static $_CONFIGS = array();

		/**
		 * @param $fileName
		 *
		 * @throws Pz_Exception
		 */
		public static function loadFile($fileName)
		{
			if(file_exists($fileName))
			{
				require $fileName;

				if(isset($PZPHP_CONFIG_ARRAY) && is_array($PZPHP_CONFIG_ARRAY) && count($PZPHP_CONFIG_ARRAY) > 0)
				{
					foreach($PZPHP_CONFIG_ARRAY as $key => $value)
					{
						self::$_CONFIGS[$key] = $value;
					}
				}
			}
			else
			{
				throw new Pz_Exception('Config file "'.$fileName.'" does not exist!');
			}
		}

		/**
		 * @param $array
		 */
		public static function loadArray($array)
		{
			if(is_array($array) && count($array) > 0)
			{
				foreach($array as $key => $value)
				{
					self::$_CONFIGS[$key] = $value;
				}
			}
		}

		/**
		 * @param $configKeyName
		 *
		 * @return mixed
		 * @throws Pz_Exception
		 */
		public static function get($configKeyName)
		{
			if(isset(self::$_CONFIGS[$configKeyName]))
			{
				return self::$_CONFIGS[$configKeyName];
			}
			else
			{
				throw new Pz_Exception('Config "'.$configKeyName.'" does not exist!');
			}
		}
	}
