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
	 * @package Pz_Core
	 */
	class Pz_Core
	{
		const VERSION = '3.8.0';

		/**
		 * @var array
		 */
		private $_pzObjects = array(
			'security' => NULL,
			'debugger' => NULL,
			'loggers' => array(
				'mysql' => NULL,
				'mysqli' => NULL,
				'memcache' => NULL,
				'memcached' => NULL
			),
			'http' => array(
				'response' => NULL,
				'request' => NULL
			)
		);

		/**
		 * @var array
		 */
		private $_pzInteractions = array(
			'mysql' => NULL,
			'mysqli' => NULL,
			'memcache' => NULL,
			'memcached' => NULL,
			'apc' => NULL,
			'shm' => NULL,
			'localcache' => NULL
		);

		/**
		 * @var array
		 */
		private $_settings = array(
			#mysql(i)
			'mysql_connect_retry_attempts' => 1,
			'mysql_connect_retry_delay' => 2,
			'mysql_auto_connect_server' => false,
			'mysql_auto_assign_active_server' => true,
			'mysql_write_retry_first_interval_delay' => 3000000,
			'mysql_write_retry_second_interval_delay' => 500000,
			'mysql_write_retry_first_interval_retries' => 3,
			'mysql_write_retry_second_interval_retries' => 6,
			#memcache(d)
			'memcache_connect_retry_attempts' => 1,
			'memcache_connect_retry_delay' => 2,
			'memcache_auto_connect_server' => false,
			'memcache_auto_assign_active_server' => true,
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
			'debug_log_php_errors' => true,
			'debug_php_error_log_file_name' => 'PHP_ERRORS',
			'debug_php_display_errors' => false
		);

		/*
		 *
		 * MySQL(i)
		 *
		 */

		/**
		 * @var array
		 */
		private $_mysqlServers = array();

		/**
		 * @var int
		 */
		private $_activeMysqlServerId = -1;

		/**
		 * @var array
		 */
		private $_mysqliServers = array();

		/**
		 * @var int
		 */
		private $_activeMysqliServerId = -1;

		/*
		 *
		 * Memcache(d)
		 *
		 */

		/**
		 * @var array
		 */
		private $_memcachedServers = array();

		/**
		 * @var array
		 */
		private $_memcacheServers = array();

		/**
		 * @var int
		 */
		private $_activeMemcachedServerId = -1;

		/**
		 * @var int
		 */
		private $_activeMemcacheServerId = -1;

		/*
		 *
		 * General
		 *
		 */

		/**
		 * @param array $settings
		 */
		function __construct(array $settings = array())
		{
			$this->_initSettings($settings);

			$this->_initDebugging();

			$this->_initSecurity();

			$this->_initMisc();

			Pz_Helper_Misc::regenerateMtRandSeed();
		}

		/*
		 * Disconnects any active connection to a mysql or memcache server
		 */
		function __destruct()
		{
			$this->disconnectAllMysqliServers();
			$this->disconnectAllMemcachedServers();
			$this->disconnectAllMemcacheServers();

			$this->debugger('finalize', array($this));
		}

		/*
		 *
		 * Inits
		 *
		 */

		/**
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

		private function _initDebugging()
		{
			if($this->getSetting('debug_mode') === true)
			{
				if($this->getSetting('debug_log_php_errors'))
				{
					ini_set('error_log', PZ_LOGS_DIR.$this->getSetting('debug_php_error_log_file_name').'.log');
					ini_set('error_reporting', E_ALL | E_NOTICE);
					ini_set('display_errors', ($this->getSetting('debug_php_display_errors')?1:0));
				}

				$this->setPzObject(
					'debugger',
					new Pz_Debugger($this->getSetting('debug_db_user'), $this->getSetting('debug_db_password'), $this->getSetting('debug_db_name'), $this->getSetting('debug_db_host'), $this->getSetting('debug_db_port'), $this->getSetting('debug_display_bar'), $this->getSetting('debug_db_log'))
				);

				$this->debugger('registerVersionInfo', array('Pz Library', self::VERSION));

				if($this->getSetting('debug_mysql_log_errors'))
				{
					$this->setPzObject(
						'loggers_mysqli',
						new Pz_Logger('', $this->getSetting('debug_mysql_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'))
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

		private function _initSecurity()
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

		/*
		 *
		 * Core set and get
		 *
		 */

		/**
		 * @param $name
		 * @param $value
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
		}

		/**
		 * @param $name
		 *
		 * @return mixed
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
		 * @param $name
		 *
		 * @return bool
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
		 * @param $name
		 * @param $className
		 */
		protected function _loadPzInteraction($name, $className)
		{
			if($this->_pzInteractions[$name] === NULL)
			{
				$this->_pzInteractions[$name] = new $className($this);
			}
		}

		/**
		 * @param $settingName
		 *
		 * @return mixed
		 */
		public function getSetting($settingName)
		{
			return (isset($this->_settings[$settingName])?$this->_settings[$settingName]:false);
		}

		/*
		 *
		 * Security
		 *
		 */

		/**
		 * @return bool
		 */
		private function _serverSecurityNeeded()
		{
			return ($this->getSetting('domain_protection') || $this->getSetting('blacklist_ip_check') ||$this->getSetting('whitelist_ip_check'));
		}

		/**
		 * @return mixed|void|Pz_Security
		 */
		public function pzSecurity()
		{
			if(($pzSecurity = $this->getPzObject('security')) === NULL)
			{
				$pzSecurity = $this->setPzObject('security', new Pz_Security());
			}

			return $pzSecurity;
		}

		/*
		 *
		 * Http
		 *
		 */

		/**
		 * @return mixed|void|Pz_Http_Request
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
		 * @return mixed|void|Pz_Http_Response
		 */
		public function pzHttpResponse()
		{
			if(($pzHttpResponse = $this->getPzObject('http_response')) === NULL)
			{
				$pzHttpResponse = $this->setPzObject('http_response', new Pz_Http_Response($this));
			}

			return $pzHttpResponse;
		}

		/*
		 *
		 * Logs
		 *
		 */

		/**
		 * @param $which
		 *
		 * @return mixed|_pzLoggerObjectMysqli|_pzLoggerObjectMemcache|_pzLoggerObjectMemcached
		 */
		public function getLoggerObject($which)
		{
			return $this->getPzObject('loggers_'.$which);
		}

		/**
		 * @param Pz_Logger $logObject
		 * @param          $message
		 */
		public function addToLog($logObject, $message)
		{
			if(is_object($logObject))
			{
				$logObject->addToLog($message);
			}
		}

		/**
		 * @param       $methodName
		 * @param array $param
		 */
		public function debugger($methodName, $param = array())
		{
			if($this->getPzObject('debugger') !== NULL)
			{
				call_user_func_array(array($this->getPzObject('debugger'), $methodName), $param);
			}
		}

		/*
		 *
		 * Misc
		 *
		 */

		/**
		 * @param $buffer
		 *
		 * @return string
		 *
		 * Method that compresses buffered output (started with ob_start()). HTML code is compressed to one line, with whitespaces removed.
		 * Sections of the html can be ignored from compression using:
		 *
		 * <!--compress-html--><!--compress-html no compression-->
		 * ...HTML and code that wont be compressed goes here...
		 * <!--compress-html-->
		 *
		 * When compressing output, make sure not to use unclosed comments in inline javascript and css  (i.e. //comment here... )
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

		/*
		 *
		 * MySQL(i)
		 *
		 */

		/**
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
		 * @param int  $id
		 * @param bool $removeAlso
		 */
		public function disconnectMysqliServer($id = -1, $removeAlso = true)
		{
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
		 * @param        $dbUser
		 * @param        $dbPassword
		 * @param string $dbName
		 * @param string $dbHost
		 * @param int    $dbPort
		 * @param bool   $preventAutoAssign
		 *
		 * @return mixed
		 */
		public function addMysqliServer($dbUser, $dbPassword, $dbName = '', $dbHost = 'localhost', $dbPort = 3306, $preventAutoAssign = false)
		{
			$this->_mysqliServers[] = new Pz_Mysqli_Server($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $this->getSetting('mysql_connect_retry_attempts'), $this->getSetting('mysql_connect_retry_delay'));

			$newId = max(array_keys($this->_mysqliServers));

			if($this->getSetting('mysql_auto_assign_active_server') === true && $preventAutoAssign === false)
			{
				$this->_activeMysqliServerId = $newId;
			}

			if($this->getSetting('mysql_auto_connect_server') === true)
			{
				$this->mysqliConnect($newId, $this->getSetting('mysql_auto_assign_active_server'));
			}

			$this->_loadPzInteraction('mysqli', 'Pz_Mysqli_Interactions');

			return $newId;
		}

		/**
		 * @param $id
		 *
		 * @return bool
		 */
		public function removeMysqliServer($id)
		{
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
		 * @param      $servierId
		 * @param bool $setAsActive
		 *
		 * @return bool
		 */
		public function mysqliConnect($servierId = -1, $setAsActive = false)
		{
			if($servierId != -1)
			{
				if(isset($this->_mysqliServers[$servierId]))
				{
					if($this->_mysqliServers[$servierId]->connect())
					{
						$this->debugger('mysqlConnectionsInc');

						if($setAsActive === true)
						{
							$this->_activeMysqliServerId = $servierId;
						}

						return true;
					}
					else
					{
						$this->addToLog($this->getPzObject('loggers_mysqli'), 'Failed to connect to MySQL server with id#'.$servierId.'.');

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
		 * @param      $id
		 * @param bool $autoConnect
		 *
		 * @return bool
		 */
		public function setActiveMysqliServerId($id, $autoConnect = true)
		{
			if(isset($this->_mysqliServers[$id]))
			{
				$this->_activeMysqliServerId = $id;

				if($autoConnect === true)
				{
					$this->_mysqliServers[$id]->connect();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @return int
		 */
		public function getActiveMysqliServerId()
		{
			return $this->_activeMysqliServerId;
		}

		/**
		 * @param $id
		 *
		 * @return bool|Pz_Mysqli_Server
		 */
		public function mysqliActiveObject($id = -1)
		{
			$id = ($id===-1?$this->getActiveMysqliServerId():$id);

			return (isset($this->_mysqliServers[$id])?$this->_mysqliServers[$id]->returnMysqliObj():false);
		}

		/**
		 * @return bool|Pz_Mysqli_Interactions
		 */
		public function mysqliInteract()
		{
			return $this->getPzInteraction('mysqli');
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function decideActiveMySqliId($id)
		{
			return ($id===-1?$this->getActiveMysqliServerId():$id);
		}

		/*
		 *
		 * Memcache(d)
		 *
		 */

		/**
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
		 * @param $mcIp
		 * @param $mcPort
		 *
		 * @return mixed
		 */
		public function addMemcachedServer($mcIp, $mcPort)
		{
			$this->_memcachedServers[] = new Pz_Memcached_Server($mcIp, $mcPort, $this->getSetting('memcache_connect_retry_attempts'), $this->getSetting('memcache_connect_retry_delay'));

			$newId = max(array_keys($this->_memcachedServers));

			if($this->getSetting('memcache_auto_assign_active_server') === true)
			{
				$this->_activeMemcachedServerId = $newId;
			}

			if($this->getSetting('memcache_auto_connect_server') === true)
			{
				$this->memcachedConnect($newId, $this->getSetting('memcache_auto_assign_active_server'));
			}

			$this->_loadPzInteraction('memcached', 'Pz_Memcached_Interactions');

			return $newId;
		}

		/**
		 * @param $mcIp
		 * @param $mcPort
		 *
		 * @return mixed
		 */
		public function addMemcacheServer($mcIp, $mcPort)
		{
			$this->_memcacheServers[] = new Pz_Memcache_Server($mcIp, $mcPort, $this->getSetting('memcache_connect_retry_attempts'), $this->getSetting('memcache_connect_retry_delay'));

			$newId = max(array_keys($this->_memcacheServers));

			if($this->getSetting('memcache_auto_assign_active_server') === true)
			{
				$this->_activeMemcacheServerId = $newId;
			}

			if($this->getSetting('memcache_auto_connect_server') === true)
			{
				$this->memcacheConnect($newId, $this->getSetting('memcache_auto_assign_active_server'));
			}

			$this->_loadPzInteraction('memcache', 'Pz_Memcache_Interactions');

			return $newId;
		}

		/**
		 * @param $id
		 *
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
		 * @param $id
		 *
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
		 * @param      $servierId
		 * @param bool $setAsActive
		 *
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
		 * @param      $servierId
		 * @param bool $setAsActive
		 *
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
		 * @param      $id
		 * @param bool $autoConnect
		 *
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
		 * @param      $id
		 * @param bool $autoConnect
		 *
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
		 * @return int
		 */
		public function getActiveMemcachedServerId()
		{
			return $this->_activeMemcachedServerId;
		}

		/**
		 * @return int
		 */
		public function getActiveMemcacheServerId()
		{
			return $this->_activeMemcacheServerId;
		}

		/**
		 * @return bool|Pz_Memcached_Interactions
		 */
		public function memcachedInteract()
		{
			return $this->getPzInteraction('memcached');
		}

		/**
		 * @return bool|Pz_Memcache_Interactions
		 */
		public function memcacheInteract()
		{
			return $this->getPzInteraction('memcache');
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function decideActiveMemcachedId($id)
		{
			return ($id===-1?$this->getActiveMemcachedServerId():$id);
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function decideActiveMemcacheId($id)
		{
			return ($id===-1?$this->getActiveMemcacheServerId():$id);
		}

		/**
		 * @param $id
		 *
		 * @return bool|Pz_Memcached_Server
		 */
		public function memcachedActiveObject($id = -1)
		{
			$id = ($id===-1?$this->getActiveMemcachedServerId():$id);

			return (isset($this->_memcachedServers[$id])?$this->_memcachedServers[$id]->returnMemcachedObj():false);
		}

		/**
		 * @param $id
		 *
		 * @return bool|Pz_Memcache_Server
		 */
		public function memcacheActiveObject($id = -1)
		{
			$id = ($id===-1?$this->getActiveMemcacheServerId():$id);

			return (isset($this->_memcacheServers[$id])?$this->_memcacheServers[$id]->returnMemcacheObj():false);
		}

		/*
		 *
		 * APC
		 *
		 */

		/**
		 * @return bool|Pz_APC_Interactions
		 */
		public function apcInteract()
		{
			$this->_loadPzInteraction('apc', 'Pz_APC_Interactions');

			return $this->getPzInteraction('apc');
		}

		/*
		 *
		 * SHARED MEMORY
		 *
		 */

		/**
		 * @return bool|Pz_SHM_Interactions
		 */
		public function shmInteract()
		{
			$this->_loadPzInteraction('shm', 'Pz_SHM_Interactions');

			return $this->getPzInteraction('shm');
		}

		/*
		 *
		 * LOCAL CACHE
		 *
		 */

		/**
		 * @return bool|Pz_LocalCache_Interactions
		 */
		public function lcInteract()
		{
			$this->_loadPzInteraction('localcache', 'Pz_LocalCache_Interactions');

			return $this->getPzInteraction('localcache');
		}
	}
