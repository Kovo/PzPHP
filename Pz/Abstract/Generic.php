<?php

	abstract class Pz_Abstract_Generic
	{
		/**
		 * @var null|Pz_Core
		 */
		protected $_pzCoreObject = NULL;

		/**
		 * @param Pz_Core $PzCore
		 */
		function __construct(Pz_Core $PzCore)
		{
			$this->init($PzCore);
		}

		/**
		 * @param Pz_Core $PzCore
		 */
		public function init(Pz_Core $PzCore)
		{
			$this->_pzCoreObject = $PzCore;
		}

		/**
		 * @return null|Pz_Core
		 */
		public function pzCore()
		{
			return $this->_pzCoreObject;
		}
	}
