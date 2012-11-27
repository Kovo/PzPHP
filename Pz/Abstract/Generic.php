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
	 * This abstract class allows classes to share commonalities between each other.
	 */
	abstract class Pz_Abstract_Generic
	{
		/**
		 * Stores an instance of Pz_Core.
		 *
		 * @access protected
		 * @var null|Pz_Core
		 */
		protected $_pzCoreObject = NULL;

		/**
		 * Calls the init() method.
		 *
		 * @param Pz_Core $PzCore
		 */
		function __construct(Pz_Core $PzCore)
		{
			$this->init($PzCore);
		}

		/**
		 * The init method sets the Pz_Core object.
		 *
		 * @access public
		 * @param Pz_Core $PzCore
		 */
		public function init(Pz_Core $PzCore)
		{
			$this->_pzCoreObject = $PzCore;
		}

		/**
		 * Returns the Pz_Core instance.
		 *
		 * @access public
		 * @return null|Pz_Core
		 */
		public function pzCore()
		{
			return $this->_pzCoreObject;
		}
	}
