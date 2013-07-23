<?php
	class PzPHP_Controller
	{
		/**
		 * @var null|PzPHP_Core
		 */
		protected $_PZPHP = NULL;

		public function before(){}

		/**
		 * @param PzPHP_Core $PzPHPCore
		 */
		function __construct(PzPHP_Core $PzPHPCore)
		{
			$this->_PZPHP = $PzPHPCore;
		}

		public function after(){}
	}
