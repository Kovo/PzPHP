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
	 * The PzPHP core class is the central registry for modules, and allows quick access to them.
	 *
	 * The PzPHP core class also allows you to register variables that can later be accessed in modules.
	 */
	class PzPHP_Core
	{
		/**
		 * The version of PzPHP.
		 *
		 * @var string
		 */
		const VERSION = '1.1.4/Ultrices Quam';

		/**
		 * An array of registered modules and their instances.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_registeredModules = array();

		/**
		 * An array of registered variables accessible to all modules.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_registeredVariables = array();

		/**
		 * The construct registers the core modules of PzPHP, including Pz_Core.
		 *
		 * The construct also registers PzPHP's version with Pz_Core.
		 */
		function __construct()
		{
			$this->_warmupDirectories();

			$this->_registeredModules['Pz_Core'] = new Pz_Core($this->_extractPzCoreSettings());

			$this->registerModule('PzPHP_Cache');
			$this->registerModule('PzPHP_Db');
			$this->registerModule('PzPHP_Security');
			$this->registerModule('PzPHP_Locale');
			$this->registerModule('PzPHP_Routing');

			$this->pz()->debugger('registerVersionInfo', array('PzPHP', self::VERSION));
		}

		/**
		 * Checks to make sure key directories exist, and if they don't, create them.
		 *
		 * @access protected
		 */
		protected function _warmupDirectories()
		{
			if(!is_dir(PZPHP_TRANSLATIONS_DIR))
			{
				mkdir(PZPHP_TRANSLATIONS_DIR, 0774, true);
			}

			if(!is_dir(PZPHP_CSS_DIR))
			{
				mkdir(PZPHP_CSS_DIR, 0774, true);
			}

			if(!is_dir(PZPHP_JS_DIR))
			{
				mkdir(PZPHP_JS_DIR, 0774, true);
			}

			if(!is_dir(PZPHP_IMAGES_DIR))
			{
				mkdir(PZPHP_IMAGES_DIR, 0774, true);
			}
		}

		/**
		 * Extracts all pz related constants to pass to Pz_Core.
		 *
		 * @access protected
		 * @return array
		 */
		protected function _extractPzCoreSettings()
		{
			$settings = array();
			$definedConstants = get_defined_constants(true);

			if(isset($definedConstants['user']) && count($definedConstants['user']) > 0)
			{
				foreach($definedConstants['user'] as $constantName => $constantValue)
				{
					if(strpos($constantName, 'PZ_SETTING_') !== false)
					{
						$settingArrayKeyName = strtolower(
							str_replace('PZ_SETTING_', '', $constantName)
						);

						$settings[$settingArrayKeyName] = (
							!Pz_Helper_String::unserializable($constantValue)?
								$constantValue:
								unserialize($constantValue)
						);
					}
				}
			}

			return $settings;
		}

		/**
		 * Registers a module with PzPHP. It does not get instantiated at this step.
		 *
		 * @access public
		 * @param string $moduleName
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
		 * Returns the instance of the specified module.
		 *
		 * The module gets instantiated at this step if it has not already.
		 *
		 * @access public
		 * @param string $moduleName
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
				return null;
			}
		}

		/**
		 * A short-form for the registerVariable method.
		 *
		 * @param $variableName
		 * @param $variableValue
		 *
		 * @return mixed
		 */
		public function setVariable($variableName, $variableValue)
		{
			return call_user_func_array(
				array($this, 'registerVariable'),
				array($variableName, $variableValue)
			);
		}

		/**
		 * Registers a variable that can be accessed via any module.
		 *
		 * @access public
		 * @param string $variableName
		 * @param mixed $variableValue
		 * @return bool
		 */
		public function registerVariable($variableName, $variableValue)
		{
			if(!$this->variableExists($variableName))
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
		 * Returns a registered varaible.
		 *
		 * @access public
		 * @param string $variableName
		 * @return null|mixed
		 */
		public function getVariable($variableName)
		{
			if($this->variableExists($variableName))
			{
				return $this->_registeredVariables[$variableName];
			}
			else
			{
				return null;
			}
		}

		/**
		 * Change a registered variable's value.
		 *
		 * @access public
		 * @param $variableName
		 * @param $variableValue
		 * @return null|mixed
		 */
		public function changeVariable($variableName, $variableValue)
		{
			if($this->variableExists($variableName))
			{
				$this->_registeredVariables[$variableName] = $variableValue;

				return $this->_registeredVariables[$variableName];
			}
			else
			{
				return null;
			}
		}

		/**
		 * Returns true or false depending on if specified variable is registered or not.
		 *
		 * @access public
		 * @param string $variableName
		 * @return bool
		 */
		public function variableExists($variableName)
		{
			return isset($this->_registeredVariables[$variableName]);
		}

		/**
		 * Short-form for the unregisterVariable method.
		 *
		 * @return mixed
		 */
		public function unsetVariable()
		{
			return call_user_func_array(
				array($this, 'unregisterVariable'),
				func_get_args()
			);
		}

		/**
		 * Unregisters one or more registered variables.
		 *
		 * @access public
		 * @return int
		 */
		public function unregisterVariable()
		{
			$variableNames = func_get_args();
			$unregisteredVariables = 0;

			if(count($variableNames) > 0)
			{
				foreach($variableNames as $variableName)
				{
					if($this->variableExists($variableName))
					{
						unset($this->_registeredVariables[$variableName]);

						$unregisteredVariables++;
					}
				}
			}

			return $unregisteredVariables;
		}

		/**
		 * Returns the instance of Pz_Core.
		 *
		 * @access public
		 * @return Pz_Core|null
		 */
		public function pz()
		{
			return $this->module('Pz_Core');
		}

		/**
		 * Returns the instance of PzPHP_Cache.
		 *
		 * @access public
		 * @return PzPHP_Cache|null
		 */
		public function cache()
		{
			return $this->module('PzPHP_Cache');
		}

		/**
		 * Returns the instance of PzPHP_Db.
		 *
		 * @access public
		 * @return PzPHP_Db|null
		 */
		public function db()
		{
			return $this->module('PzPHP_Db');
		}

		/**
		 * Returns the instance of PzPHP_Security.
		 *
		 * @access public
		 * @return PzPHP_Security|null
		 */
		public function security()
		{
			return $this->module('PzPHP_Security');
		}

		/**
		 * Returns the instance of PzPHP_Locale.
		 *
		 * @access public
		 * @return PzPHP_Locale|null
		 */
		public function locale()
		{
			return $this->module('PzPHP_Locale');
		}

		/**
		 * Returns the instance of PzPHP_Routing.
		 *
		 * @access public
		 * @return PzPHP_Routing|null
		 */
		public function routing()
		{
			return $this->module('PzPHP_Routing');
		}
	}
