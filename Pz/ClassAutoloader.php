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
	 * The Pz Library Auto Loader class
	 */
	class Pz_ClassAutoloader
	{
		/**
		 * Registers the class auto loader using spl_autoload_register method (recommended by PHP)
		 */
		public function __construct()
		{
			spl_autoload_register(array($this, 'loader'));
		}

		/**
		 * Takes in the class name, and constructs the path to the class file using PEAR naming conventions.
		 *
		 * @access private
		 * @param string $className
		 */
		private function loader($className)
		{
			$fileNameParts = explode('_', $className);

			include BASE_CLASS_DIR.implode('/', $fileNameParts).'.php';
		}
	}
