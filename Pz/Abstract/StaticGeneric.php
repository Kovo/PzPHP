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
	 * This abstract class allows static classes to share commonalities between each other.
	 */
	abstract class Pz_Abstract_StaticGeneric
	{
		/**
		 * Stores an instance of Pz_Core.
		 *
		 * @static
		 * @access protected
		 * @var null|Pz_Core
		 */
		protected static $_pzCoreObject = null;

		/**
		 * The init method sets the Pz_Core object.
		 *
		 * @static
		 * @access public
		 * @param Pz_Core $PzCore
		 */
		public static function init(Pz_Core $PzCore)
		{
			static::$_pzCoreObject = $PzCore;
		}

		/**
		 * Returns the Pz_Core instance.
		 *
		 * @static
		 * @access public
		 * @return null|Pz_Core
		 */
		public static function pzCore()
		{
			return static::$_pzCoreObject;
		}
	}
