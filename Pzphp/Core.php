<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP_Core
	 */
	class PzPHP_Core
	{
		const VERSION = '1.0.1';

		/**
		 * @var array
		 */
		private $_registeredModules = array();

		/**
		 * @var array
		 */
		private $_registeredVariables = array();

		function __construct()
		{
			$this->_registeredModules['Pz_Core'] = new Pz_Core($this->_extractPzCoreSettings());

			$this->registerModule('PzPHP_Cache');
			$this->registerModule('PzPHP_Db');
			$this->registerModule('PzPHP_Security');

			$this->pz()->debugger('registerVersionInfo', array('PzPHP', self::VERSION));
		}

		/**
		 * @return array
		 */
		private function _extractPzCoreSettings()
		{
			$settings = array();
			$definedConstants = get_defined_constants(true);

			if(count($definedConstants) > 0)
			{
				foreach($definedConstants as $constantName => $constantValue)
				{
					if(strpos($constantName, 'PZ_SETTING_') !== false)
					{
						$settingArrayKeyName = strtolower(
							str_replace('PZ_SETTING_', '', $constantName)
						);

						$settings[$settingArrayKeyName] = $constantValue;
					}
				}
			}

			return $settings;
		}

		/**
		 * @param $moduleName
		 *
		 * @return bool
		 */
		public function registerModule($moduleName)
		{
			if(!isset($this->_registeredModules[$moduleName]))
			{
				$this->_registeredModules[$moduleName] = false;

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param $moduleName
		 *
		 * @return null|mixed
		 */
		public function module($moduleName)
		{
			if(isset($this->_registeredModules[$moduleName]))
			{
				if($this->_registeredModules[$moduleName] === false)
				{
					$this->_registeredModules[$moduleName] = new $moduleName();

					if(method_exists($this->_registeredModules[$moduleName], 'init'))
					{
						$this->_registeredModules[$moduleName]->init($this);
					}
				}

				return $this->_registeredModules[$moduleName];
			}
			else
			{
				return NULL;
			}
		}

		/**
		 * @param $variableName
		 * @param $variableValue
		 *
		 * @return bool
		 */
		public function registerVariable($variableName, $variableValue)
		{
			if(!isset($this->_registeredVariables[$variableName]))
			{
				$this->_registeredVariables[$variableName] = $variableValue;

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param $variableName
		 *
		 * @return null|mixed
		 */
		public function getVariable($variableName)
		{
			if(isset($this->_registeredVariables[$variableName]))
			{
				return $this->_registeredVariables[$variableName];
			}
			else
			{
				return NULL;
			}
		}

		/**
		 * @param $variableName
		 * @param $variableValue
		 *
		 * @return null|mixed
		 */
		public function changeVariable($variableName, $variableValue)
		{
			if(isset($this->_registeredVariables[$variableName]))
			{
				$this->_registeredVariables[$variableName] = $variableValue;

				return $this->_registeredVariables[$variableName];
			}
			else
			{
				return NULL;
			}
		}

		/**
		 * @return Pz_Core|null
		 */
		public function pz()
		{
			return $this->module('Pz_Core');
		}

		/**
		 * @return PzPHP_Cache|null
		 */
		public function cache()
		{
			return $this->module('PzPHP_Cache');
		}

		/**
		 * @return PzPHP_Db|null
		 */
		public function db()
		{
			return $this->module('PzPHP_Db');
		}

		/**
		 * @return PzPHP_Security|null
		 */
		public function security()
		{
			return $this->module('PzPHP_Security');
		}
	}
