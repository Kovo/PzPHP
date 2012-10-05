<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzphpWrapper
	 */
	class PzphpWrapper
	{
		/**
		 * @var null|PzphpCore
		 */
		private $_pzphpCore = NULL;

		/**
		 * @param PzphpCore $PzphpCore
		 */
		public function init(PzphpCore $PzphpCore)
		{
			$this->_pzphpCore = $PzphpCore;
		}

		/**
		 * @return null|PzphpCore
		 */
		public function pzphp()
		{
			return $this->_pzphpCore;
		}
	}
