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
 * The core class for Pz Library where most other functionality is accessed from.
 */
class Pz_Core
{
	/**
	 * Defines the version of Pz Library.
	 *
	 * @var string
	 */
	const VERSION = '3.9.3/Morbi Mollis';

	/**
	 * A multi-dimensional array that will hold various object instances.
	 *
	 * Pz stores main objects like the logger class, debugger, security, etc... inside this array for easy access.
	 *
	 * @access private
	 * @var array
	 */
	private $_pzObjects = array(
		'security' => NULL,
		'debugger' => NULL,
		'loggers' => array(
			'mysql' => NULL,
			'mysqli' => NULL,
			'pdo' => NULL,
			'memcache' => NULL,
			'memcached' => NULL
		),
		'http' => array(
			'response' => NULL,
			'request' => NULL
		)
	);

	/**
	 * An array that holds interaction objects.
	 *
	 * Pz stores interaction objects for class representations of data storage/caching architectures, such as MySQL, Memcache, APC, etc...
	 *
	 * @access private
	 * @var array
	 */
	private $_pzInteractions = array(
		'mysql' => NULL,
		'mysqli' => NULL,
		'pdo' => NULL,
		'memcache' => NULL,
		'memcached' => NULL,
		'apc' => NULL,
		'shm' => NULL,
		'localcache' => NULL
	);

	/**
	 * Pz's main settings array
	 *
	 * A multi-dimensional array that holds default settings for Pz.
	 *
	 * @access private
	 * @var array
	 */
	private $_settings = array(
		#databases
		'db_connect_retry_attempts' => 1,
		'db_connect_retry_delay' => 2,
		'db_auto_connect_server' => false,
		'db_auto_assign_active_server' => true,
		'db_write_retry_first_interval_delay' => 3000000,
		'db_write_retry_second_interval_delay' => 500000,
		'db_write_retry_first_interval_retries' => 3,
		'db_write_retry_second_interval_retries' => 6,
		#caches
		'cache_connect_retry_attempts' => 1,
		'cache_connect_retry_delay' => 2,
		'cache_auto_connect_server' => false,
		'cache_auto_assign_active_server' => true,
		'cache_lock_expire_time' => 15,
		#whitelisting
		'whitelist_ip_check' => false,
		'whitelist_ips' => array(), //array or string (can be comma separated)
		'whitelist_action' => array(
			'action' => 'exit', //can be exit, url
			'target' => '', //if action = url, provide url here
			'message' => '<h1>Access Denied</h1>' //if action = exit, provide message to be shown
		),
		'whitelist_auto_allow_host_server_ip' => true,
		#blacklisting
		'blacklist_ip_check' => false,
		'blacklist_ips' => array(), //array or string (can be comma separated)
		'blacklist_action' => array(
			'action' => 'exit', //can be exit, url
			'target' => '', //if action = url, provide url here
			'message' => '<h1>You have been banned from this website</h1>' //if action = exit, provide message to be shown
		),
		'blacklist_ignore_host_server_ip' => true,
		#compression
		'output_compression' => true,
		'output_buffering' => true,
		#domain protection
		'domain_protection' => false,
		'domain_allowed_domains' => array(), //array or string (can be comma separated)
		'domain_target_domain' => '',
		#debug/profiling
		'debug_mode' => true,
		'debug_error_logging' => true,
		'debug_display_bar' => true,
		'debug_db_user' => '',
		'debug_db_password' => '',
		'debug_db_name' => '',
		'debug_db_host' => 'localhost',
		'debug_db_port' => 3306,
		'debug_db_log' => false,
		'debug_log_file_auto_rotate' => true,
		'debug_delete_log_files_after_x_days' => 7,
		'debug_mysql_log_errors' => true,
		'debug_mysql_error_log_file_name' => 'MYSQL_ERRORS',
		'debug_memcache_log_errors' => true,
		'debug_memcache_error_log_file_name' => 'MEMCACHE_ERRORS',
		'debug_memcached_log_errors' => true,
		'debug_memcached_error_log_file_name' => 'MEMCACHED_ERRORS',
		'debug_pdo_log_errors' => true,
		'debug_pdo_error_log_file_name' => 'PDO_ERRORS',
		'debug_log_php_errors' => true,
		'debug_php_error_log_file_name' => 'PHP_ERRORS',
		'debug_php_display_errors' => false
	);

	/**
	 * An array where mysql server objects are stored.
	 *
	 * They keys in this array also act as the id for the particular mysql server object.
	 *
	 * @access private
	 * @var array
	 */
	private $_mysqlServers = array();

	/**
	 * The active mysql server id.
	 *
	 * @access private
	 * @var int
	 */
	private $_activeMysqlServerId = -1;

	/**
	 * An array where mysqli server objects are stored.
	 *
	 * They keys in this array also act as the id for the particular mysqli server object.
	 *
	 * @access private
	 * @var array
	 */
	private $_mysqliServers = array();

	/**
	 * The active mysqli server id.
	 *
	 * @access private
	 * @var int
	 */
	private $_activeMysqliServerId = -1;

	/**
	 * An array where pdo server objects are stored.
	 *
	 * They keys in this array also act as the id for the particular pdo server object.
	 *
	 * @access private
	 * @var array
	 */
	private $_pdoServers = array();

	/**
	 * The active pdo server id.
	 *
	 * @access private
	 * @var int
	 */
	private $_activePDOServerId = -1;

	/**
	 * An array where memcached server objects are stored.
	 *
	 * They keys in this array also act as the id for the particular memcached server object.
	 *
	 * @access private
	 * @var array
	 */
	private $_memcachedServers = array();

	/**
	 * An array where memcache server objects are stored.
	 *
	 * They keys in this array also act as the id for the particular memcache server object.
	 *
	 * @access private
	 * @var array
	 */
	private $_memcacheServers = array();

	/**
	 * The active memcached server id.
	 *
	 * @access private
	 * @var int
	 */
	private $_activeMemcachedServerId = -1;

	/**
	 * The active memcache server id.
	 *
	 * @access private
	 * @var int
	 */
	private $_activeMemcacheServerId = -1;

	/**
	 * Boot-up process for the Pz Library.
	 *
	 * This is where settings are set (if any custom settings are provided), debugging is started (if enabled), and security checks are executed (if need be).
	 *
	 * @param array $settings
	 */
	function __construct(array $settings = array())
	{
		$this->_initSettings($settings);

		$this->_initDebugging();

		$this->_initServerSecurity();

		$this->_initMisc();

		Pz_Helper_Misc::regenerateMtRandSeed();
	}

	/**
	 * Disconnects any active connection to a mysql or memcache server, as well as tell the debugger to run its finalize step (if enabled).
	 *
	 * The destruct procedure allows you to avoid having to manually disconnect your mysql or memcache servers.
	 *
	 * Of course, you can choose to disconnect them at any time before this destruct procedure.
	 */
	function __destruct()
	{
		$this->disconnectAllMysqliServers();
		$this->disconnectAllMemcachedServers();
		$this->disconnectAllMemcacheServers();

		$this->debugger('finalize', array($this));
	}

	/**
	 * Apply any custom settings for Pz before anything else gets started.
	 *
	 * @access private
	 * @param array $settings
	 */
	private function _initSettings(array $settings = array())
	{
		if(count($settings) > 0)
		{
			foreach($settings as $setting_name => $setting_value)
			{
				if(isset($this->_settings[$setting_name]))
				{
					$this->_settings[$setting_name] = $setting_value;
				}
			}
		}
	}

	/**
	 * Begin debugging class, or warm-up logs.
	 *
	 * @access private
	 */
	private function _initDebugging()
	{
		if($this->getSetting('debug_mode') === true)
		{
			$this->setPzObject(
				'debugger',
				new Pz_Debugger($this->getSetting('debug_db_user'), $this->getSetting('debug_db_password'), $this->getSetting('debug_db_name'), $this->getSetting('debug_db_host'), $this->getSetting('debug_db_port'), $this->getSetting('debug_display_bar'), $this->getSetting('debug_db_log'))
			);

			$this->debugger('registerVersionInfo', array('Pz Library', self::VERSION));
		}

		if($this->getSetting('debug_error_logging'))
		{
			if($this->getSetting('debug_log_php_errors'))
			{
				ini_set('error_log', PZ_LOGS_DIR.$this->getSetting('debug_php_error_log_file_name').'.log');
				ini_set('error_reporting', E_ALL | E_NOTICE);
				ini_set('display_errors', ($this->getSetting('debug_php_display_errors')?1:0));
			}

			if($this->getSetting('debug_mysql_log_errors'))
			{
				$this->setPzObject(
					'loggers_mysql',
					new Pz_Logger('', $this->getSetting('debug_mysql_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'))
				);
			}

			if($this->getSetting('debug_pdo_log_errors'))
			{
				$this->setPzObject(
					'loggers_pdo',
					new Pz_Logger('', $this->getSetting('debug_pdo_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'))
				);
			}

			if($this->getSetting('debug_memcache_log_errors'))
			{
				$this->setPzObject(
					'loggers_memcache',
					new Pz_Logger('', $this->getSetting('debug_memcache_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'))
				);
			}

			if($this->getSetting('debug_memcached_log_errors'))
			{
				$this->setPzObject(
					'loggers_memcached',
					new Pz_Logger('', $this->getSetting('debug_memcached_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'))
				);
			}
		}
	}

	/**
	 * Begin basic server security checks (if enabled).
	 *
	 * This will do a domain check, whitelist check, and blacklist check.
	 *
	 * @access private
	 */
	private function _initServerSecurity()
	{
		if($this->_serverSecurityNeeded())
		{
			Pz_Server_Security::init($this);

			if($this->getSetting('domain_protection'))
			{
				Pz_Server_Security::domainCheck();
			}

			if($this->getSetting('whitelist_ip_check'))
			{
				Pz_Server_Security::whitelistCheck();
			}

			if($this->getSetting('blacklist_ip_check'))
			{
				Pz_Server_Security::blacklistCheck();
			}
		}
	}

	/**
	 * Any other functions that need to be executed at start-up are called here.
	 *
	 * @access private
	 */
	private function _initMisc()
	{
		if($this->getSetting('output_buffering') && $this->getSetting('output_compression'))
		{
			ob_start(array($this, 'compressOutput'));
		}
		elseif($this->getSetting('output_buffering'))
		{
			ob_start();
		}
	}

	/**
	 * Registers an object in the Pz objects array.
	 *
	 * @access public
	 * @param string $name
	 * @param object $value
	 * @return object
	 */
	public function setPzObject($name, $value)
	{
		$explodeName = explode('_', $name);

		if(count($explodeName) === 2)
		{
			$this->_pzObjects[$explodeName[0]][$explodeName[1]] = $value;
		}
		else
		{
			$this->_pzObjects[$explodeName[0]] = $value;
		}

		return $value;
	}

	/**
	 * Gets a registered object from the Pz objects array.
	 *
	 * @access public
	 * @param string $name
	 * @return object
	 */
	public function getPzObject($name)
	{
		$explodeName = explode('_', $name);

		if(count($explodeName) === 2)
		{
			return $this->_pzObjects[$explodeName[0]][$explodeName[1]];
		}
		else
		{
			return $this->_pzObjects[$explodeName[0]];
		}
	}

	/**
	 * Loads a Pz Interaction for a specific submodule.
	 *
	 * New interactions are passed an instance of Pz_Core in their constructor.
	 *
	 * @access protected
	 * @param string $name
	 * @param string $className
	 */
	protected function _loadPzInteraction($name, $className)
	{
		if($this->_pzInteractions[$name] === NULL)
		{
			$this->_pzInteractions[$name] = new $className($this);
		}
	}

	/**
	 * Gets a loaded Pz Interaction.
	 *
	 * @access public
	 * @param string $name
	 * @return bool|mixed
	 */
	public function getPzInteraction($name)
	{
		if($this->_pzInteractions[$name] === NULL)
		{
			return false;
		}
		else
		{
			return $this->_pzInteractions[$name];
		}
	}

	/**
	 * Gets a Pz setting's value.
	 *
	 * @access public
	 * @param string $settingName
	 * @return mixed
	 */
	public function getSetting($settingName)
	{
		return (isset($this->_settings[$settingName])?$this->_settings[$settingName]:false);
	}

	/**
	 * Returns true if server security is needed/enabled.
	 *
	 * @access private
	 * @return bool
	 */
	private function _serverSecurityNeeded()
	{
		return ($this->getSetting('domain_protection') || $this->getSetting('blacklist_ip_check') || $this->getSetting('whitelist_ip_check'));
	}

	/**
	 * Returns the Pz_Security object (also autoloads it if not already loaded).
	 *
	 * @access public
	 * @return null|Pz_Security
	 */
	public function pzSecurity()
	{
		if(($pzSecurity = $this->getPzObject('security')) === NULL)
		{
			$pzSecurity = $this->setPzObject('security', new Pz_Security());
		}

		return $pzSecurity;
	}

	/**
	 * Returns the Pz_Http_Request object (also autoloads it if not already loaded).
	 *
	 * @access public
	 * @return null|Pz_Http_Request
	 */
	public function pzHttpRequest()
	{
		if(($pzHttpRequest = $this->getPzObject('http_request')) === NULL)
		{
			$pzHttpRequest = $this->setPzObject('http_request', new Pz_Http_Request($this));
		}

		return $pzHttpRequest;
	}

	/**
	 * Returns the Pz_Http_Response object (also autoloads it if not already loaded).
	 *
	 * @access public
	 * @return null|Pz_Http_Response
	 */
	public function pzHttpResponse()
	{
		if(($pzHttpResponse = $this->getPzObject('http_response')) === NULL)
		{
			$pzHttpResponse = $this->setPzObject('http_response', new Pz_Http_Response($this));
		}

		return $pzHttpResponse;
	}

	/**
	 * Returns the logger object for a specified subpackage.
	 *
	 * @access public
	 * @param string $which
	 * @return null|_pzLoggerObjectMysql|_pzLoggerObjectMysqli|_pzLoggerObjectMemcache|_pzLoggerObjectMemcached
	 */
	public function getLoggerObject($which)
	{
		return $this->getPzObject('loggers_'.$which);
	}

	/**
	 * Adds a custom log entry to a specified log file (via a logger object).
	 *
	 * @access public
	 * @param Pz_Logger $logObject
	 * @param string    $message
	 */
	public function addToLog($logObject, $message)
	{
		if(is_object($logObject))
		{
			$logObject->addToLog($message);
		}
	}

	/**
	 * Executes a method inside the debugger object (if it exists).
	 *
	 * @access public
	 * @param string    $methodName
	 * @param array     $param
	 */
	public function debugger($methodName, $param = array())
	{
		if($this->getPzObject('debugger') !== NULL)
		{
			call_user_func_array(array($this->getPzObject('debugger'), $methodName), $param);
		}
	}

	/**
	 * Method that compresses buffered output (started with ob_start()). HTML code is compressed to one line, with whitespaces removed.
	 *
	 * Sections of the html can be ignored from compression using:
	 *
	 * <!--compress-html--><!--compress-html no compression-->
	 * ...HTML and code that wont be compressed goes here...
	 * <!--compress-html-->
	 *
	 * When compressing output, make sure not to use unclosed comments in inline javascript and css  (i.e. //comment here... )
	 *
	 * @access public
	 * @param string $buffer
	 * @return string
	 */
	public function compressOutput($buffer)
	{
		$buffer = explode("<!--compress-html-->", $buffer);

		$count = count($buffer);

		$buffer_out = '';

		for($i = 0; $i <= $count; $i++)
		{
			if(isset($buffer[$i]))
			{
				if(stristr($buffer[$i], '<!--compress-html no compression-->'))
				{
					$buffer[$i] = (str_replace("<!--compress-html no compression-->", " ", $buffer[$i]));
				}
				else
				{
					$buffer[$i] = (str_replace("\t", " ", $buffer[$i]));
					$buffer[$i] = (str_replace("\n\n", "\n", $buffer[$i]));
					$buffer[$i] = (str_replace("\n", "", $buffer[$i]));
					$buffer[$i] = (str_replace("\r", "", $buffer[$i]));
					while(stristr($buffer[$i], '  '))
					{
						$buffer[$i] = (str_replace("  ", " ", $buffer[$i]));
					}
				}

				$buffer_out .= $buffer[$i];

				unset($buffer[$i]);
			}
		}

		unset($buffer);

		return $buffer_out;
	}

	/**
	 * Disconnects all registered Mysql servers (using Mysqli).
	 *
	 * You have the option to not have them unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param bool $removeAlso
	 */
	public function disconnectAllMysqliServers($removeAlso = true)
	{
		if(count($this->_mysqliServers) > 0)
		{
			foreach($this->_mysqliServers as $id => $mysqlObj)
			{
				if($removeAlso === true)
				{
					$this->removeMysqliServer($id);
				}
				else
				{
					$this->disconnectMysqliServer($id, false);
				}
			}
		}
	}

	/**
	 * Disconnects a particular mysql server (using Mysqli).
	 *
	 * If a specific id is not given, the current active mysqli server object will be disconnected.
	 *
	 * You have the option to not have it unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $removeAlso
	 */
	public function disconnectMysqliServer($id = -1, $removeAlso = true)
	{
		$id = ($id===-1?$this->getActiveMysqliServerId():$id);

		if(isset($this->_mysqliServers[$id]))
		{
			if($removeAlso === true)
			{
				$this->removeMysqliServer($id);
			}
			else
			{
				$this->_mysqliServers[$id]->disconnect();

				$this->debugger('mysqlDisconnectionsInc');

				if($this->_activeMysqliServerId === $id)
				{
					$this->_activeMysqliServerId = -1;
				}
			}
		}
	}

	/**
	 * Registers a mysql server with Pz (using Mysqli).
	 *
	 * By default, the mysql server is not connected to until you send a specific command to it (like a query).
	 *
	 * This method handles everything that is needed to register a mysql server object with Pz (using Mysqli).
	 *
	 * @access public
	 * @param string $dbUser
	 * @param string $dbPassword
	 * @param string $dbName
	 * @param string $dbHost
	 * @param int    $dbPort
	 * @param bool   $preventAutoAssign
	 * @return mixed
	 */
	public function addMysqliServer($dbUser, $dbPassword, $dbName = '', $dbHost = 'localhost', $dbPort = 3306, $preventAutoAssign = false)
	{
		$this->_mysqliServers[] = new Pz_Mysqli_Server($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $this->getSetting('db_connect_retry_attempts'), $this->getSetting('db_connect_retry_delay'));

		$newId = max(array_keys($this->_mysqliServers));

		if($this->getSetting('db_auto_assign_active_server') === true && $preventAutoAssign === false)
		{
			$this->_activeMysqliServerId = $newId;
		}

		if($this->getSetting('db_auto_connect_server') === true)
		{
			$this->mysqliConnect($newId, $this->getSetting('db_auto_assign_active_server'));
		}

		$this->_loadPzInteraction('mysqli', 'Pz_Mysqli_Interactions');

		return $newId;
	}

	/**
	 * Unregisters a mysql server object with Pz (using Mysqli).
	 *
	 * If no specific server is specified, the active server object will be used.
	 *
	 * This method will disconnect from the server automatically.
	 *
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public function removeMysqliServer($id = -1)
	{
		$id = ($id===-1?$this->getActiveMysqliServerId():$id);
		$return = false;

		if(isset($this->_mysqliServers[$id]) && is_object($this->_mysqliServers[$id]))
		{
			$this->_mysqliServers[$id]->disconnect();

			$this->debugger('mysqlDisconnectionsInc');

			unset($this->_mysqliServers[$id]);

			if($this->_activeMysqliServerId === $id)
			{
				$this->_activeMysqliServerId = -1;
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Connects to the mysql server (using Mysqli).
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $setAsActive
	 * @return bool
	 */
	public function mysqliConnect($id = -1, $setAsActive = false)
	{
		$id = ($id===-1?$this->getActiveMysqliServerId():$id);
		$return = false;

		if(isset($this->_mysqliServers[$id]))
		{
			if($this->_mysqliServers[$id]->connect())
			{
				$this->debugger('mysqlConnectionsInc');

				if($setAsActive === true)
				{
					$this->_activeMysqliServerId = $id;
				}

				$return = true;
			}
			else
			{
				$this->addToLog($this->getPzObject('loggers_mysql'), 'Failed to connect to MySQL server with id#'.$id.'.');
			}
		}

		return $return;
	}

	/**
	 * Sets the active mysql server id (using Mysqli).
	 *
	 * By default, this method will not auto connect to the mysql server.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $autoConnect
	 * @return bool
	 */
	public function setActiveMysqliServerId($id = -1, $autoConnect = false)
	{
		$id = ($id===-1?$this->getActiveMysqliServerId():$id);
		$return = false;

		if(isset($this->_mysqliServers[$id]))
		{
			$this->_activeMysqliServerId = $id;

			if($autoConnect === true)
			{
				$this->_mysqliServers[$id]->connect();
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Returns the current active mysql server id (using Mysqli).
	 *
	 * @access public
	 * @return int
	 */
	public function getActiveMysqliServerId()
	{
		return $this->_activeMysqliServerId;
	}

	/**
	 * Returns the current active mysql server object (using Mysqli).
	 *
	 * If an id is specified, then the specified mysler server object will be returned instead.
	 *
	 * @access public
	 * @param int $id
	 * @return bool|Pz_Mysqli_Server
	 */
	public function mysqliActiveObject($id = -1)
	{
		$id = ($id===-1?$this->getActiveMysqliServerId():$id);

		return (isset($this->_mysqliServers[$id])?$this->_mysqliServers[$id]:false);
	}

	/**
	 * Returns the interaction object for mysqli.
	 *
	 * @access public
	 * @return bool|Pz_Mysqli_Interactions
	 */
	public function mysqliInteract()
	{
		return $this->getPzInteraction('mysqli');
	}

	/**
	 * Resolves the proper mysql server object id based on input.
	 *
	 * If the input id is -1, then the active id is returned, else, the id is returned.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function decideActiveMySqliId($id)
	{
		return ($id===-1?$this->getActiveMysqliServerId():$id);
	}

	/**
	 * Disconnects all registered Mysql servers (using Mysql).
	 *
	 * You have the option to not have them unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param bool $removeAlso
	 */
	public function disconnectAllMysqlServers($removeAlso = true)
	{
		if(count($this->_mysqlServers) > 0)
		{
			foreach($this->_mysqlServers as $id => $mysqlObj)
			{
				if($removeAlso === true)
				{
					$this->removeMysqlServer($id);
				}
				else
				{
					$this->disconnectMysqlServer($id, false);
				}
			}
		}
	}

	/**
	 * Disconnects a particular mysql server (using Mysql).
	 *
	 * If a specific id is not given, the current active mysqli server object will be disconnected.
	 *
	 * You have the option to not have it unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $removeAlso
	 */
	public function disconnectMysqlServer($id = -1, $removeAlso = true)
	{
		$id = ($id===-1?$this->getActiveMysqlServerId():$id);

		if(isset($this->_mysqlServers[$id]))
		{
			if($removeAlso === true)
			{
				$this->removeMysqlServer($id);
			}
			else
			{
				$this->_mysqlServers[$id]->disconnect();

				$this->debugger('mysqlDisconnectionsInc');

				if($this->_activeMysqlServerId === $id)
				{
					$this->_activeMysqlServerId = -1;
				}
			}
		}
	}

	/**
	 * Registers a mysql server with Pz (using Mysql).
	 *
	 * By default, the mysql server is not connected to until you send a specific command to it (like a query).
	 *
	 * This method handles everything that is needed to register a mysql server object with Pz (using Mysql).
	 *
	 * @access public
	 * @param string $dbUser
	 * @param string $dbPassword
	 * @param string $dbName
	 * @param string $dbHost
	 * @param int    $dbPort
	 * @param bool   $preventAutoAssign
	 * @return mixed
	 */
	public function addMysqlServer($dbUser, $dbPassword, $dbName = '', $dbHost = 'localhost', $dbPort = 3306, $preventAutoAssign = false)
	{
		$this->_mysqlServers[] = new Pz_Mysql_Server($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $this->getSetting('db_connect_retry_attempts'), $this->getSetting('db_connect_retry_delay'));

		$newId = max(array_keys($this->_mysqlServers));

		if($this->getSetting('db_auto_assign_active_server') === true && $preventAutoAssign === false)
		{
			$this->_activeMysqlServerId = $newId;
		}

		if($this->getSetting('db_auto_connect_server') === true)
		{
			$this->mysqlConnect($newId, $this->getSetting('db_auto_assign_active_server'));
		}

		$this->_loadPzInteraction('mysql', 'Pz_Mysql_Interactions');

		return $newId;
	}

	/**
	 * Unregisters a mysql server object with Pz (using Mysql).
	 *
	 * If no specific server is specified, the active server object will be used.
	 *
	 * This method will disconnect from the server automatically.
	 *
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public function removeMysqlServer($id = -1)
	{
		$id = ($id===-1?$this->getActiveMysqlServerId():$id);
		$return = false;

		if(isset($this->_mysqlServers[$id]) && is_object($this->_mysqlServers[$id]))
		{
			$this->_mysqlServers[$id]->disconnect();

			$this->debugger('mysqlDisconnectionsInc');

			unset($this->_mysqlServers[$id]);

			if($this->_activeMysqlServerId === $id)
			{
				$this->_activeMysqlServerId = -1;
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Connects to the mysql server (using Mysql).
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $setAsActive
	 * @return bool
	 */
	public function mysqlConnect($id = -1, $setAsActive = false)
	{
		$id = ($id===-1?$this->getActiveMysqlServerId():$id);
		$return = false;

		if(isset($this->_mysqlServers[$id]))
		{
			if($this->_mysqlServers[$id]->connect())
			{
				$this->debugger('mysqlConnectionsInc');

				if($setAsActive === true)
				{
					$this->_activeMysqlServerId = $id;
				}

				$return = true;
			}
			else
			{
				$this->addToLog($this->getPzObject('loggers_mysql'), 'Failed to connect to MySQL server with id#'.$id.'.');
			}
		}

		return $return;
	}

	/**
	 * Sets the active mysql server id (using Mysql).
	 *
	 * By default, this method will not auto connect to the mysql server.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $autoConnect
	 * @return bool
	 */
	public function setActiveMysqlServerId($id = -1, $autoConnect = false)
	{
		$id = ($id===-1?$this->getActiveMysqlServerId():$id);
		$return = false;

		if(isset($this->_mysqlServers[$id]))
		{
			$this->_activeMysqlServerId = $id;

			if($autoConnect === true)
			{
				$this->_mysqlServers[$id]->connect();
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Returns the current active mysql server id (using Mysql).
	 *
	 * @access public
	 * @return int
	 */
	public function getActiveMysqlServerId()
	{
		return $this->_activeMysqlServerId;
	}

	/**
	 * Returns the current active mysql server object (using Mysql).
	 *
	 * If an id is specified, then the specified mysql server object will be returned instead.
	 *
	 * @access public
	 * @param int $id
	 * @return bool|Pz_Mysql_Server
	 */
	public function mysqlActiveObject($id = -1)
	{
		$id = ($id===-1?$this->getActiveMysqlServerId():$id);

		return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]:false);
	}

	/**
	 * Returns the interaction object for mysql.
	 *
	 * @access public
	 * @return bool|Pz_Mysql_Interactions
	 */
	public function mysqlInteract()
	{
		return $this->getPzInteraction('mysql');
	}

	/**
	 * Resolves the proper mysql server object id based on input.
	 *
	 * If the input id is -1, then the active id is returned, else, the id is returned.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function decideActiveMySqlId($id)
	{
		return ($id===-1?$this->getActiveMysqlServerId():$id);
	}

	/**
	 * Disconnects all registered database servers (using PDO).
	 *
	 * You have the option to not have them unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param bool $removeAlso
	 */
	public function disconnectAllPDOServers($removeAlso = true)
	{
		if(count($this->_pdoServers) > 0)
		{
			foreach($this->_pdoServers as $id => $pdoObj)
			{
				if($removeAlso === true)
				{
					$this->removePDOServer($id);
				}
				else
				{
					$this->disconnectPDOServer($id, false);
				}
			}
		}
	}

	/**
	 * Disconnects a particular database server (using PDO).
	 *
	 * If a specific id is not given, the current active PDO server object will be disconnected.
	 *
	 * You have the option to not have it unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $removeAlso
	 */
	public function disconnectPDOServer($id = -1, $removeAlso = true)
	{
		$id = ($id===-1?$this->getActivePDOServerId():$id);

		if(isset($this->_pdoServers[$id]))
		{
			if($removeAlso === true)
			{
				$this->removePDOServer($id);
			}
			else
			{
				$this->_pdoServers[$id]->disconnect();

				$this->debugger('pdoDisconnectionsInc');

				if($this->_activePDOServerId === $id)
				{
					$this->_activePDOServerId = -1;
				}
			}
		}
	}

	/**
	 * Registers a database server with Pz (using PDO).
	 *
	 * By default, the database server is not connected to until you send a specific command to it (like a query).
	 *
	 * This method handles everything that is needed to register a database server object with Pz (using PDO).
	 *
	 * @access public
	 * @param string $dbUser
	 * @param string $dbPassword
	 * @param string $dbType
	 * @param string $dbName
	 * @param string $dbHost
	 * @param int    $dbPort
	 * @param array    $dbDriverOptions
	 * @param string    $server
	 * @param string    $protocol
	 * @param string    $socket
	 * @param bool   $preventAutoAssign
	 * @return mixed
	 */
	public function addPDOServer($dbUser, $dbPassword, $dbType, $dbName = '', $dbHost = 'localhost', $dbPort = 0, $dbDriverOptions = array(), $server, $protocol, $socket, $preventAutoAssign = false)
	{
		$this->_pdoServers[] = new Pz_PDO_Server($dbUser, $dbPassword, $dbType, $dbName, $dbHost, $dbPort, $this->getSetting('db_connect_retry_attempts'), $this->getSetting('db_connect_retry_delay'), $dbDriverOptions, $server, $protocol, $socket);

		$newId = max(array_keys($this->_pdoServers));

		if($this->getSetting('db_auto_assign_active_server') === true && $preventAutoAssign === false)
		{
			$this->_activePDOServerId = $newId;
		}

		if($this->getSetting('db_auto_connect_server') === true)
		{
			$this->pdoConnect($newId, $this->getSetting('db_auto_assign_active_server'));
		}

		$this->_loadPzInteraction('pdo', 'Pz_PDO_Interactions');

		return $newId;
	}

	/**
	 * Unregisters a database server object with Pz (using PDO).
	 *
	 * If no specific server is specified, the active server object will be used.
	 *
	 * This method will disconnect from the server automatically.
	 *
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public function removePDOServer($id = -1)
	{
		$id = ($id===-1?$this->getActivePDOServerId():$id);
		$return = false;

		if(isset($this->_pdoServers[$id]) && is_object($this->_pdoServers[$id]))
		{
			$this->_pdoServers[$id]->disconnect();

			$this->debugger('pdoDisconnectionsInc');

			unset($this->_pdoServers[$id]);

			if($this->_activePDOServerId === $id)
			{
				$this->_activePDOServerId = -1;
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Connects to the database server (using PDO).
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $setAsActive
	 * @return bool
	 */
	public function pdoConnect($id = -1, $setAsActive = false)
	{
		$id = ($id===-1?$this->getActivePDOServerId():$id);
		$return = false;

		if(isset($this->_pdoServers[$id]))
		{
			if($this->_pdoServers[$id]->connect())
			{
				$this->debugger('pdoConnectionsInc');

				if($setAsActive === true)
				{
					$this->_activePDOServerId = $id;
				}

				$return = true;
			}
			else
			{
				$this->addToLog($this->getPzObject('loggers_pdo'), 'Failed to connect to database server with id#'.$id.'.');
			}
		}

		return $return;
	}

	/**
	 * Sets the active database server id (using PDO).
	 *
	 * By default, this method will not auto connect to the database server.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $autoConnect
	 * @return bool
	 */
	public function setActivePDOServerId($id = -1, $autoConnect = false)
	{
		$id = ($id===-1?$this->getActivePDOServerId():$id);
		$return = false;

		if(isset($this->_pdoServers[$id]))
		{
			$this->_activePDOServerId = $id;

			if($autoConnect === true)
			{
				$this->_pdoServers[$id]->connect();
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Returns the current active database server id (using PDO).
	 *
	 * @access public
	 * @return int
	 */
	public function getActivePDOServerId()
	{
		return $this->_activePDOServerId;
	}

	/**
	 * Returns the current active database server object (using PDO).
	 *
	 * If an id is specified, then the specified database server object will be returned instead.
	 *
	 * @access public
	 * @param int $id
	 * @return bool|Pz_PDO_Server
	 */
	public function pdoActiveObject($id = -1)
	{
		$id = ($id===-1?$this->getActivePDOServerId():$id);

		return (isset($this->_pdoServers[$id])?$this->_pdoServers[$id]:false);
	}

	/**
	 * Returns the interaction object for the PDO.
	 *
	 * @access public
	 * @return bool|Pz_PDO_Interactions
	 */
	public function pdoInteract()
	{
		return $this->getPzInteraction('pdo');
	}

	/**
	 * Resolves the proper database server object id based on input.
	 *
	 * If the input id is -1, then the active id is returned, else, the id is returned.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function decideActivePDOId($id)
	{
		return ($id===-1?$this->getActivePDOServerId():$id);
	}

	/**
	 * Disconnects all registered Memcache servers (using Memcached).
	 *
	 * You have the option to not have them unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param bool $removeAlso
	 */
	public function disconnectAllMemcachedServers($removeAlso = true)
	{
		if(count($this->_memcachedServers) > 0)
		{
			foreach($this->_memcachedServers as $id => $memcachedObj)
			{
				if($removeAlso === true)
				{
					$this->removeMemcachedServer($id);
				}
				else
				{
					$this->disconnectMemcachedServer($id, false);
				}
			}
		}
	}

	/**
	 * Disconnects all registered Memcache servers (using Memcache).
	 *
	 * You have the option to not have them unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param bool $removeAlso
	 */
	public function disconnectAllMemcacheServers($removeAlso = true)
	{
		if(count($this->_memcacheServers) > 0)
		{
			foreach($this->_memcacheServers as $id => $memcacheObj)
			{
				if($removeAlso === true)
				{
					$this->removeMemcacheServer($id);
				}
				else
				{
					$this->disconnectMemcacheServer($id, false);
				}
			}
		}
	}

	/**
	 * Disconnects a particular memcache server (using Memcached).
	 *
	 * If a specific id is not given, the current active memcache server object will be disconnected.
	 *
	 * You have the option to not have it unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $removeAlso
	 */
	public function disconnectMemcachedServer($id = -1, $removeAlso = true)
	{
		if(isset($this->_memcachedServers[$id]))
		{
			if($removeAlso === true)
			{
				$this->removeMemcachedServer($id);
			}
			else
			{
				$this->_memcachedServers[$id]->disconnect();

				$this->debugger('mcdDisconnectionsInc');

				if($this->_activeMemcachedServerId === $id)
				{
					$this->_activeMemcachedServerId = -1;
				}
			}
		}
	}

	/**
	 * Disconnects a particular memcache server (using Memcache).
	 *
	 * If a specific id is not given, the current active memcache server object will be disconnected.
	 *
	 * You have the option to not have it unregistered with Pz after being disconnected.
	 *
	 * @access public
	 * @param int  $id
	 * @param bool $removeAlso
	 */
	public function disconnectMemcacheServer($id = -1, $removeAlso = true)
	{
		if(isset($this->_memcacheServers[$id]))
		{
			if($removeAlso === true)
			{
				$this->removeMemcacheServer($id);
			}
			else
			{
				$this->_memcacheServers[$id]->disconnect();

				$this->debugger('mcDisconnectionsInc');

				if($this->_activeMemcacheServerId === $id)
				{
					$this->_activeMemcacheServerId = -1;
				}
			}
		}
	}

	/**
	 * Registers a memcache server with Pz (using Memcached).
	 *
	 * By default, the memcache server is not connected to until you send a specific command to it (like a query).
	 *
	 * This method handles everything that is needed to register a memcache server with Pz (using Memcached).
	 *
	 * @access public
	 * @param string $mcIp
	 * @param string|int $mcPort
	 * @return mixed
	 */
	public function addMemcachedServer($mcIp, $mcPort)
	{
		$this->_memcachedServers[] = new Pz_Memcached_Server($mcIp, $mcPort, $this->getSetting('cache_connect_retry_attempts'), $this->getSetting('cache_connect_retry_delay'));

		$newId = max(array_keys($this->_memcachedServers));

		if($this->getSetting('cache_auto_assign_active_server') === true)
		{
			$this->_activeMemcachedServerId = $newId;
		}

		if($this->getSetting('cache_auto_connect_server') === true)
		{
			$this->memcachedConnect($newId, $this->getSetting('cache_auto_assign_active_server'));
		}

		$this->_loadPzInteraction('memcached', 'Pz_Memcached_Interactions');

		return $newId;
	}

	/**
	 * Registers a memcache server with Pz (using Memcache).
	 *
	 * By default, the memcache server is not connected to until you send a specific command to it (like a query).
	 *
	 * This method handles everything that is needed to register a memcache server with Pz (using Memcache).
	 *
	 * @access public
	 * @param string $mcIp
	 * @param string|int $mcPort
	 * @return mixed
	 */
	public function addMemcacheServer($mcIp, $mcPort)
	{
		$this->_memcacheServers[] = new Pz_Memcache_Server($mcIp, $mcPort, $this->getSetting('cache_connect_retry_attempts'), $this->getSetting('cache_connect_retry_delay'));

		$newId = max(array_keys($this->_memcacheServers));

		if($this->getSetting('cache_auto_assign_active_server') === true)
		{
			$this->_activeMemcacheServerId = $newId;
		}

		if($this->getSetting('cache_auto_connect_server') === true)
		{
			$this->memcacheConnect($newId, $this->getSetting('cache_auto_assign_active_server'));
		}

		$this->_loadPzInteraction('memcache', 'Pz_Memcache_Interactions');

		return $newId;
	}

	/**
	 * Unregisters a memcache server with Pz (using Memcached).
	 *
	 * If no specific server is specified, the active server object will be used.
	 *
	 * This method will disconnect from the server automatically.
	 *
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public function removeMemcachedServer($id)
	{
		$return = false;

		if(isset($this->_memcachedServers[$id]) && is_object($this->_memcachedServers[$id]))
		{
			$this->_memcachedServers[$id]->disconnect();

			$this->debugger('mcdDisconnectionsInc');

			unset($this->_memcachedServers[$id]);

			if($this->_activeMemcachedServerId === $id)
			{
				$this->_activeMemcachedServerId = -1;
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Unregisters a memcache server with Pz (using Memcache).
	 *
	 * If no specific server is specified, the active server object will be used.
	 *
	 * This method will disconnect from the server automatically.
	 *
	 * @access public
	 * @param int $id
	 * @return bool
	 */
	public function removeMemcacheServer($id)
	{
		$return = false;

		if(isset($this->_memcacheServers[$id]) && is_object($this->_memcacheServers[$id]))
		{
			$this->_memcacheServers[$id]->disconnect();

			$this->debugger('mcDisconnectionsInc');

			unset($this->_memcacheServers[$id]);

			if($this->_activeMemcacheServerId === $id)
			{
				$this->_activeMemcacheServerId = -1;
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * Connects to the memcache server (using Memcached).
	 *
	 * @access public
	 * @param int $servierId
	 * @param bool $setAsActive
	 * @return bool
	 */
	public function memcachedConnect($servierId = -1, $setAsActive = false)
	{
		if($servierId != -1)
		{
			if(isset($this->_memcachedServers[$servierId]))
			{
				if($this->_memcachedServers[$servierId]->connect())
				{
					$this->debugger('mcdConnectionsInc');

					if($setAsActive === true)
					{
						$this->_activeMemcachedServerId = $servierId;
					}

					return true;
				}
				else
				{
					$this->addToLog($this->getPzObject('loggers_memcached'), 'Failed to connect to Memcached server with id#'.$servierId.'.');

					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Connects to the memcache server (using Memcache).
	 *
	 * @access public
	 * @param int $servierId
	 * @param bool $setAsActive
	 * @return bool
	 */
	public function memcacheConnect($servierId = -1, $setAsActive = false)
	{
		if($servierId != -1)
		{
			if(isset($this->_memcacheServers[$servierId]))
			{
				if($this->_memcacheServers[$servierId]->connect())
				{
					$this->debugger('mcConnectionsInc');

					if($setAsActive === true)
					{
						$this->_activeMemcacheServerId = $servierId;
					}

					return true;
				}
				else
				{
					$this->addToLog($this->getPzObject('loggers_memcache'), 'Failed to connect to Memcache server with id#'.$servierId.'.');

					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets the active memcache server id (using Memcached).
	 *
	 * By default, this method will not auto connect to the memcache server.
	 *
	 * @access public
	 * @param int $id
	 * @param bool $autoConnect
	 * @return bool
	 */
	public function setActiveMemcachedServerId($id, $autoConnect = true)
	{
		if(isset($this->_memcachedServers[$id]))
		{
			$this->_activeMemcachedServerId = $id;

			if($autoConnect === true)
			{
				$this->_memcachedServers[$id]->connect();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets the active memcache server id (using Memcache).
	 *
	 * By default, this method will not auto connect to the memcache server.
	 *
	 * @access public
	 * @param int $id
	 * @param bool $autoConnect
	 * @return bool
	 */
	public function setActiveMemcacheServerId($id, $autoConnect = true)
	{
		if(isset($this->_memcacheServers[$id]))
		{
			$this->_activeMemcacheServerId = $id;

			if($autoConnect === true)
			{
				$this->_memcacheServers[$id]->connect();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the current active memcache server id (using Memcached).
	 *
	 * @access public
	 * @return int
	 */
	public function getActiveMemcachedServerId()
	{
		return $this->_activeMemcachedServerId;
	}

	/**
	 * Returns the current active memcache server id (using Memcache).
	 *
	 * @access public
	 * @return int
	 */
	public function getActiveMemcacheServerId()
	{
		return $this->_activeMemcacheServerId;
	}

	/**
	 * Returns the interaction object for memcached.
	 *
	 * @access public
	 * @return bool|Pz_Memcached_Interactions
	 */
	public function memcachedInteract()
	{
		return $this->getPzInteraction('memcached');
	}

	/**
	 * Returns the interaction object for memcache.
	 *
	 * @access public
	 * @return bool|Pz_Memcache_Interactions
	 */
	public function memcacheInteract()
	{
		return $this->getPzInteraction('memcache');
	}

	/**
	 * Resolves the proper memcached server object id based on input.
	 *
	 * If the input id is -1, then the active id is returned, else, the id is returned.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function decideActiveMemcachedId($id)
	{
		return ($id===-1?$this->getActiveMemcachedServerId():$id);
	}

	/**
	 * Resolves the proper memcache server object id based on input.
	 *
	 * If the input id is -1, then the active id is returned, else, the id is returned.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function decideActiveMemcacheId($id)
	{
		return ($id===-1?$this->getActiveMemcacheServerId():$id);
	}

	/**
	 * Returns the current active memcache server object (using Memcached).
	 *
	 * If an id is specified, then the specified mysler server object will be returned instead.
	 *
	 * @access public
	 * @param int $id
	 * @return bool|Pz_Memcached_Server
	 */
	public function memcachedActiveObject($id = -1)
	{
		$id = ($id===-1?$this->getActiveMemcachedServerId():$id);

		return (isset($this->_memcachedServers[$id])?$this->_memcachedServers[$id]->returnMemcachedObj():false);
	}

	/**
	 * Returns the current active memcache server object (using Memcache).
	 *
	 * If an id is specified, then the specified mysler server object will be returned instead.
	 *
	 * @access public
	 * @param int $id
	 * @return bool|Pz_Memcache_Server
	 */
	public function memcacheActiveObject($id = -1)
	{
		$id = ($id===-1?$this->getActiveMemcacheServerId():$id);

		return (isset($this->_memcacheServers[$id])?$this->_memcacheServers[$id]->returnMemcacheObj():false);
	}

	/**
	 * Returns the interaction object for APC.
	 *
	 * @access public
	 * @return bool|Pz_APC_Interactions
	 */
	public function apcInteract()
	{
		$this->_loadPzInteraction('apc', 'Pz_APC_Interactions');

		return $this->getPzInteraction('apc');
	}

	/**
	 * Returns the interaction object for Shared Memory.
	 *
	 * @access public
	 * @return bool|Pz_SHM_Interactions
	 */
	public function shmInteract()
	{
		$this->_loadPzInteraction('shm', 'Pz_SHM_Interactions');

		return $this->getPzInteraction('shm');
	}

	/**
	 * Returns the interaction object for Local Cache.
	 *
	 * @access public
	 * @return bool|Pz_LocalCache_Interactions
	 */
	public function lcInteract()
	{
		$this->_loadPzInteraction('localcache', 'Pz_LocalCache_Interactions');

		return $this->getPzInteraction('localcache');
	}
}
