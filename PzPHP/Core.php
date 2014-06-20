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
	const VERSION = '2.0.7/Semper Cursus';

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
		$this->registerModule('PzPHP_Module_Cache');
		$this->registerModule('PzPHP_Module_Db');
		$this->registerModule('PzPHP_Module_Security');
		$this->registerModule('PzPHP_Module_Locale');
		$this->registerModule('PzPHP_Module_Routing');
		$this->registerModule('PzPHP_Module_View');
		$this->registerModule('PzPHP_Module_Log');
		$this->registerModule('PzPHP_Module_Request');
		$this->registerModule('PzPHP_Module_Response');

		if(PzPHP_Config::get('SETTING_DOMAIN_PROTECTION'))
		{
			try
			{
				$this->security()->domainCheck();
			}
			catch(Exception $e)
			{
				$solution = PzPHP_Config::get('SETTING_DOMAIN_SOLUTION');

				if($solution['type'] === 'redirect')
				{
					$this->response()->redirect($solution['value']);
				}
				else
				{
					$this->view()->render($solution['value']);
				}
			}
		}

		if(PzPHP_Config::get('SETTING_OUTPUT_COMPRESSION'))
		{
			ob_start(array($this, 'compressOutput'));
		}
		elseif(PzPHP_Config::get('SETTING_OUTPUT_BUFFERING'))
		{
			ob_start();
		}
	}

	/**
	 * @param $buffer
	 * @return string
	 */
	public function compressOutput($buffer)
	{
		$buffer = explode("<!--compress-html-->", $buffer);
		$count = count($buffer);
		$buffer_out = '';

		for($i =0;$i<=$count;$i++)
		{
			if(isset($buffer[$i]))
			{
				if(stristr($buffer[$i], '<!--compress-html no compression-->'))
				{
					$buffer[$i] = str_replace("<!--compress-html no compression-->", " ", $buffer[$i]);
				}
				else
				{
					$buffer[$i] = str_replace(array("\t","\n\n","\n","\r"), array(" ","\n","",""), $buffer[$i]);

					while(stristr($buffer[$i], '  '))
					{
						$buffer[$i] = str_replace("  ", " ", $buffer[$i]);
					}
				}

				$buffer_out .= $buffer[$i];

				$buffer[$i] = null;
				unset($buffer[$i]);
			}
		}

		$buffer = null;
		unset($buffer);

		return $buffer_out;
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
		}

		return $this;
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
	public function set($variableName, $variableValue)
	{
		if(!$this->exists($variableName))
		{
			$this->_registeredVariables[$variableName] = $variableValue;
		}

		return $this;
	}

	/**
	 * Returns a registered varaible.
	 *
	 * @access public
	 * @param string $variableName
	 * @return null|mixed
	 */
	public function get($variableName)
	{
		if($this->exists($variableName))
		{
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
	public function exists($variableName)
	{
		return isset($this->_registeredVariables[$variableName]);
	}

	/**
	 * Short-form for the unregisterVariable method.
	 *
	 * @return mixed
	 */
	public function delete()
	{
		$variableNames = func_get_args();

		if(!empty($variableNames))
		{
			foreach($variableNames as $variableName)
			{
				if($this->exists($variableName))
				{
					unset($this->_registeredVariables[$variableName]);
				}
			}
		}

		return $this;
	}

	/**
	 * Returns the instance of PzPHP_Module_Cache.
	 *
	 * @access public
	 * @return PzPHP_Module_Cache|null
	 */
	public function cache()
	{
		return $this->module('PzPHP_Module_Cache');
	}

	/**
	 * Returns the instance of PzPHP_Module_Db.
	 *
	 * @access public
	 * @return PzPHP_Module_Db|null
	 */
	public function db()
	{
		return $this->module('PzPHP_Module_Db');
	}

	/**
	 * Returns the instance of PzPHP_Module_Security.
	 *
	 * @access public
	 * @return PzPHP_Module_Security|null
	 */
	public function security()
	{
		return $this->module('PzPHP_Module_Security');
	}

	/**
	 * Returns the instance of PzPHP_Module_Locale.
	 *
	 * @access public
	 * @return PzPHP_Module_Locale|null
	 */
	public function locale()
	{
		return $this->module('PzPHP_Module_Locale');
	}

	/**
	 * Returns the instance of PzPHP_Module_Routing.
	 *
	 * @access public
	 * @return PzPHP_Module_Routing|null
	 */
	public function routing()
	{
		return $this->module('PzPHP_Module_Routing');
	}

	/**
	 * Returns the instance of PzPHP_Module_View.
	 *
	 * @access public
	 * @return PzPHP_Module_View|null
	 */
	public function view()
	{
		return $this->module('PzPHP_Module_View');
	}

	/**
	 * Returns the instance of PzPHP_Module_Log.
	 *
	 * @access public
	 * @return PzPHP_Module_Log|null
	 */
	public function log()
	{
		return $this->module('PzPHP_Module_Log');
	}

	/**
	 * @return PzPHP_Module_Request|null
	 */
	public function request()
	{
		return $this->module('PzPHP_Module_Request');
	}

	/**
	 * @return PzPHP_Module_Response|null
	 */
	public function response()
	{
		return $this->module('PzPHP_Module_Response');
	}
}
