<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP_Wrapper
	 */
	class PzPHP_Wrapper
	{
		/**
		 * @var null|PzPHP_Core
		 */
		private $_pzphpCore = NULL;

		/**
		 * @param PzPHP_Core $PzphpCore
		 */
		public function init(PzPHP_Core $PzphpCore)
		{
			$this->_pzphpCore = $PzphpCore;
		}

		/**
		 * @return null|PzPHP_Core
		 */
		public function pzphp()
		{
			return $this->_pzphpCore;
		}
	}
