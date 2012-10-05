<?php
	/**
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package ClassAutoloader
	 */
	class ClassAutoloader
	{
		public function __construct()
		{
			spl_autoload_register(array($this, 'loader'));
		}

		/**
		 * @param $className
		 */
		private function loader($className)
		{
			$fileNameParts = preg_split('/(?=[A-Z])/', $className);

			include BASE_CLASS_DIR.implode('/', $fileNameParts).'.php';
		}
	}
