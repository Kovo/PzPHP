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
class PzPHP_Core
{
	/**
	 * The version of PzPHP.
	 *
	 * @var string
	 */
	const VERSION = '2.0.11/Semper Cursus';

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
		$this->registerModule('PzPHP_Module_Notification');

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
	 * @param $variableName
	 * @param $variableValue
	 * @return $this
	 */
	public function set($variableValue, $variableName = null)
	{
		if(!$this->exists($variableName))
		{
			if($variableName !== null)
			{
				$this->_registeredVariables[$variableName] = $variableValue;
			}
			else
			{
				$this->_registeredVariables[] = $variableValue;
			}
		}

		return $this;
	}

	/**
	 * @param $variableName
	 * @return null
	 */
	public function get($variableName = null)
	{
		if($variableName === null)
		{
			return $this->_registeredVariables;
		}
		elseif($this->exists($variableName))
		{
			return $this->_registeredVariables[$variableName];
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param $variableName
	 * @return bool
	 */
	public function exists($variableName)
	{
		return isset($this->_registeredVariables[$variableName]);
	}

	/**
	 * @return $this
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
					$this->_registeredVariables[$variableName] = null;
					unset($this->_registeredVariables[$variableName]);
				}
			}
		}
		else
		{
			$this->_registeredVariables = null;
			$this->_registeredVariables = array();
		}

		return $this;
	}

	/**
	 * @return mixed|PzPHP_Module_Cache
	 */
	public function cache()
	{
		return $this->module('PzPHP_Module_Cache');
	}

	/**
	 * @return mixed|PzPHP_Module_Db
	 */
	public function db()
	{
		return $this->module('PzPHP_Module_Db');
	}

	/**
	 * @return mixed|PzPHP_Module_Security
	 */
	public function security()
	{
		return $this->module('PzPHP_Module_Security');
	}

	/**
	 * @return mixed|PzPHP_Module_Locale
	 */
	public function locale()
	{
		return $this->module('PzPHP_Module_Locale');
	}

	/**
	 * @return mixed|PzPHP_Module_Routing
	 */
	public function routing()
	{
		return $this->module('PzPHP_Module_Routing');
	}

	/**
	 * @return mixed|PzPHP_Module_View
	 */
	public function view()
	{
		return $this->module('PzPHP_Module_View');
	}

	/**
	 * @return mixed|PzPHP_Module_Log
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

	/**
	 * @return PzPHP_Module_Notification|null
	 */
	public function notification()
	{
		return $this->module('PzPHP_Module_Notification');
	}
}
