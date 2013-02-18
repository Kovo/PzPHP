<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP
	 */
	/**
	 * A class extended by PzPHP modules to give easy access to the PzPHP core, among other things.
	 */
	class PzPHP_Wrapper
	{
		/**
		 * An object representing PzPHP_Core.
		 *
		 * @access private
		 * @var null|PzPHP_Core
		 */
		private $_pzphpCore = null;

		/**
		 * The init method sets the PzPHP_Core object.
		 *
		 * @access public
		 * @param PzPHP_Core $PzphpCore
		 */
		public function init(PzPHP_Core $PzphpCore)
		{
			$this->_pzphpCore = $PzphpCore;
		}

		/**
		 * Returns the PzPHP_Core instance.
		 *
		 * @access public
		 * @return null|PzPHP_Core
		 */
		public function pzphp()
		{
			return $this->_pzphpCore;
		}
	}
