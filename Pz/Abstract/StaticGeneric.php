<?php

	abstract class Pz_Abstract_StaticGeneric
	{
		/**
		 * @var null|Pz_Core
		 */
		protected static $_pzCoreObject = NULL;

		/**
		 * @param Pz_Core $PzCore
		 */
		public static function init(Pz_Core $PzCore)
		{
			static::$_pzCoreObject = $PzCore;
		}

		/**
		 * @return null|Pz_Core
		 */
		public static function pzCore()
		{
			return static::$_pzCoreObject;
		}
	}
