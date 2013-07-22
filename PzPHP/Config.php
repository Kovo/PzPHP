<?php
	class PzPHP_Config extends Pz_Config
	{
		/**
		 * @return array
		 */
		public static function getAll()
		{
			return self::$_CONFIGS;
		}
	}
