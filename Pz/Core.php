<?php
	/**
	 * Website: http://www.pzphp.com
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzCore
	 */
	class PzCore
	{
		const VERSION = '3.5.11';

		/**
		 * @var bool
		 */
		public $isAjax = false;

		/**
		 * @var null|PzSecuirty
		 */
		private $_pzsecurityObject = NULL;

		/**
		 * @var null|PzDebugger
		 */
		private $_pzdebuggerObject = NULL;

		/**
		 * @var null|PzLogger
		 */
		private $_pzLoggerObjectMysql = NULL;

		/**
		 * @var null|PzLogger
		 */
		private $_pzLoggerObjectMemcache = NULL;

		/**
		 * @var null|PzLogger
		 */
		private $_pzLoggerObjectMemcached = NULL;

		/**
		 * @var array
		 */
		private $_settings = array(
			#mysql
			'mysql_connect_retry_attempts' => 1,
			'mysql_connect_retry_delay' => 2,
			'auto_connect_mysql_servers' => false,
			'auto_assign_active_mysql_server' => true,
			'mysql_write_retry_first_interval_delay' => 3000000,
			'mysql_write_retry_second_interval_delay' => 500000,
			'mysql_write_retry_first_interval_retries' => 3,
			'mysql_write_retry_second_interval_retries' => 6,
			#memcache(d)
			'memcache_connect_retry_attempts' => 1,
			'memcache_connect_retry_delay' => 2,
			'auto_connect_memcache_servers' => false,
			'auto_assign_active_memcache_server' => true,
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
			#redirect handling for ajax requests
			'redirect_for_ajax_calls' => false,
			'ajax_redirect_message' => '',
			#compression
			'compress_output' => true,
			'output_buffering' => true,
			#domain protection
			'domain_protection' => false,
			'allowed_domains' => array(), //array or string (can be comma separated)
			'target_domain' => '',
			#debug/profiling
			'debug_mode' => false,
			'display_debug_bar' => false,
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
		 * MySQL
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
			$this->init($settings);

			$this->isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

			if($this->getSetting('debug_mode') === true)
			{
				if($this->getSetting('debug_log_php_errors'))
				{
					ini_set('error_log', PZ_LOGS_DIR.$this->getSetting('debug_php_error_log_file_name').'.log');
					ini_set('error_reporting', E_ALL | E_NOTICE);
					ini_set('display_errors', ($this->getSetting('debug_php_display_errors')?1:0));
				}

				$this->_pzdebuggerObject = new PzDebugger($this->getSetting('debug_db_user'), $this->getSetting('debug_db_password'), $this->getSetting('debug_db_name'), $this->getSetting('debug_db_host'), $this->getSetting('debug_db_port'), $this->getSetting('display_debug_bar'), $this->getSetting('debug_db_log'), $this->getSetting('mysql_connect_retry_attempts'), $this->getSetting('mysql_connect_retry_delay'));

				if($this->getSetting('debug_mysql_log_errors'))
				{
					$this->_pzLoggerObjectMysql = new PzLogger('', $this->getSetting('debug_mysql_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'));
				}

				if($this->getSetting('debug_memcache_log_errors'))
				{
					$this->_pzLoggerObjectMemcache = new PzLogger('', $this->getSetting('debug_memcache_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'));
				}

				if($this->getSetting('debug_memcached_log_errors'))
				{
					$this->_pzLoggerObjectMemcached = new PzLogger('', $this->getSetting('debug_memcached_error_log_file_name'), $this->getSetting('debug_log_file_auto_rotate'), $this->getSetting('debug_delete_log_files_after_x_days'));
				}
			}

			if($this->getSetting('domain_protection') === true)
			{
				$this->_domainCheck();
			}

			if($this->getSetting('output_buffering') === true && $this->getSetting('compress_output') === true)
			{
				ob_start(array($this, '_compressOutput'));
			}
			elseif($this->getSetting('output_buffering') === true)
			{
				ob_start();
			}

			$this->_whitelistCheck();
			$this->_blacklistCheck();

			$this->regenerateMtRandSeed();
		}

		/*
		 * Disconnects any active connection to a mysql or memcache server
		 */
		function __destruct()
		{
			$this->disconnectAllMysqlServers();
			$this->disconnectAllMemcachedServers();
			$this->disconnectAllMemcacheServers();

			$this->debuggerLog('finalize', $this);
		}

		/**
		 * @param array $settings
		 */
		public function init(array $settings = array())
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
		 * @param $classname
		 */
		private function _lazyLoad($classname)
		{
			$properName = '_'.strtolower($classname).'Object';

			if($this->$properName === NULL)
			{
				$this->$properName = new $classname();
			}
		}

		/**
		 * @param      $methodName
		 * @param null $param
		 */
		public function debuggerLog($methodName, $param = NULL)
		{
			if($this->_pzdebuggerObject !== NULL)
			{
				$this->_pzdebuggerObject->$methodName($param);
			}
		}

		/*
		 * Checks if domain is allowed
		 */
		private function _domainCheck()
		{
			//domain protection prevents certainr rare exploits, where attackers may play with the HEADER information
			//this also helps redirect users when they type example.com instead of www.example.com
			if(isset($_SERVER['SERVER_NAME']))
			{
				$allowedDomains = $this->getSetting('allowed_domains');

				if(is_array($allowedDomains) || strpos($allowedDomains, ',') !== false)
				{
					if(!is_array($allowedDomains))
					{
						$allowedDomains = array_map('trim', explode(',', $allowedDomains));
					}

					if(count($allowedDomains) > 0)
					{
						$exists = false;

						foreach($allowedDomains as $domain)
						{
							if(strrpos(trim($_SERVER['SERVER_NAME']), $domain) === true)
							{
								$exists = true;
								break;
							}
						}

						if($exists === false)
						{
							header("HTTP/1.1 301 Moved Permanently");
							header("Location: ".(isset($_SERVER["HTTPS"])&&strtolower($_SERVER["HTTPS"])==='on'?'https://':'http://').$this->getSetting('target_domain').$_SERVER['REQUEST_URI']);
							header("Connection: close");

							exit();
						}
					}
				}
				else
				{
					if(strrpos(trim($_SERVER['SERVER_NAME']), $allowedDomains) === false)
					{
						header("HTTP/1.1 301 Moved Permanently");
						header("Location: ".(isset($_SERVER["HTTPS"])&&strtolower($_SERVER["HTTPS"])==='on'?'https://':'http://').$this->getSetting('target_domain').$_SERVER['REQUEST_URI']);
						header("Connection: close");

						exit();
					}
				}
			}
		}

		/*
		 * Check if the visitors IP is whitelisted (if enabled)
		 */
		private function _whitelistCheck()
		{
			if($this->getSetting('whitelist_ip_check') === true)
			{
				$whitelistedips = $this->getSetting('whitelist_ips');

				if(!is_array($whitelistedips))
				{
					$whitelistedips = array_map('trim', explode(',', $whitelistedips));
				}

				if($this->getSetting('whitelist_auto_allow_host_server_ip') === true)
				{
					$whitelistedips[] = (isset($_SERVER['LOCAL_ADDR'])&&$_SERVER['LOCAL_ADDR']!==''?$_SERVER['LOCAL_ADDR']:(isset($_SERVER['SERVER_ADDR'])&&$_SERVER['SERVER_ADDR']!==''?$_SERVER['SERVER_ADDR']:'127.0.0.1'));
				}

				if(count($whitelistedips) > 0)
				{
					$this->_lazyLoad('PzSecurity');

					$ip = $this->_pzsecurityObject->getIpAddress();

					$ipFound = false;

					foreach($whitelistedips as $allowedIp)
					{
						if($allowedIp !== '' && $allowedIp === $ip)
						{
							$ipFound = true;
						}
					}

					if($ipFound === false)
					{
						$whatToDo = $this->getSetting('whitelist_action');

						if($whatToDo['action'] === 'exit')
						{
							echo $whatToDo['message'];
							exit();
						}
						elseif($whatToDo['action'] === 'url')
						{
							$this->redirect($whatToDo['target']);
						}
					}
				}
			}
		}

		/*
		 * Check if the visitors IP is blacklisted (if enabled)
		 */
		private function _blacklistCheck()
		{
			if($this->getSetting('blacklist_ip_check') === true)
			{
				$blacklistedips = $this->getSetting('blacklist_ips');

				if(!is_array($blacklistedips))
				{
					$blacklistedips = array_map('trim', explode(',', $blacklistedips));
				}

				if(count($blacklistedips) > 0)
				{
					$this->_lazyLoad('PzSecurity');

					$ip = $this->_pzsecurityObject->getIpAddress();

					$serverIp = (isset($_SERVER['LOCAL_ADDR'])&&$_SERVER['LOCAL_ADDR']!==''?$_SERVER['LOCAL_ADDR']:(isset($_SERVER['SERVER_ADDR'])&&$_SERVER['SERVER_ADDR']!==''?$_SERVER['SERVER_ADDR']:'127.0.0.1'));

					$ignoreServerIp = $this->getSetting('blacklist_ignore_host_server_ip');

					$ipFound = false;

					foreach($blacklistedips as $notallowedIp)
					{
						if($notallowedIp !== '' && ($ignoreServerIp === false || $serverIp !== $ip) && $notallowedIp === $ip)
						{
							$ipFound = true;
						}
					}

					if($ipFound === true)
					{
						$whatToDo = $this->getSetting('blacklist_action');

						if($whatToDo['action'] === 'exit')
						{
							echo $whatToDo['message'];
							exit();
						}
						elseif($whatToDo['action'] === 'url')
						{
							$this->redirect($whatToDo['target']);
						}
					}
				}
			}
		}

		/*
		 * Regenerates a unique mt_rand seed
		 */
		public function regenerateMtRandSeed()
		{
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * mt_rand(1,999999));

			mt_srand($seed+mt_rand(1,1000));
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

		/**
		 * @param      $url
		 * @param bool $exit
		 */
		public function redirect($url, $exit = true)
		{
			if(!$this->isAjax || $this->getSetting('redirect_for_ajax_calls') === true)
			{
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".$url);
				header("Connection: close");
			}
			else
			{
				echo $this->getSetting('ajax_redirect_message');
			}

			if($exit === true)
			{
				exit();
			}
		}

		/**
		 * @return null|PzSecurity
		 */
		public function getSecurityObject()
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject;
		}

		/**
		 * @param     $length
		 * @param int $type
		 *
		 * @return mixed
		 */
		public function createCode($length, $type = PzCrypt::ALPHANUMERIC)
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject->createCode($length, $type);
		}

		/**
		 * @param       $input
		 * @param array $flags
		 * @param array $customRules
		 *
		 * @return mixed
		 */
		public function encrypt($input, $flags = array(PzCrypt::TWO_WAY), $customRules = array())
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject->encrypt($input, $flags, $customRules);
		}

		/**
		 * @param       $input
		 * @param array $flags
		 * @param array $customRules
		 *
		 * @return mixed
		 */
		public function decrypt($input, $flags = array(), $customRules = array())
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject->decrypt($input, $flags, $customRules);
		}

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
		private function _compressOutput($buffer)
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
		 * @param      $value
		 * @param bool $mustBeNumeric
		 * @param int  $decimalPlaces
		 * @param int  $cleanall
		 * @param      $mysqlServerId
		 *
		 * @return mixed
		 */
		public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = PzSecurity::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $mysqlServerId = -1)
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject->cleanQuery(
				$this->_mysqlServers[($mysqlServerId===-1?$this->_activeMysqlServerId:$mysqlServerId)]->returnMysqliObj(),
				$value,
				$mustBeNumeric,
				$decimalPlaces,
				$cleanall
			);
		}

		/**
		 * @param      $mysqlObj
		 * @param      $value
		 * @param bool $mustBeNumeric
		 * @param int  $decimalPlaces
		 * @param int  $cleanall
		 *
		 * @return mixed
		 */
		public function sanitizeExternal($mysqlObj, $value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = PzSecurity::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			$this->_lazyLoad('PzSecurity');

			return $this->_pzsecurityObject->cleanQuery(
				$mysqlObj,
				$value,
				$mustBeNumeric,
				$decimalPlaces,
				$cleanall
			);
		}

		/**
		 * @param PzLogger $logObject
		 * @param          $message
		 */
		public function addToLog($logObject, $message)
		{
			if(is_object($logObject))
			{
				$logObject->addToLog($message);
			}
		}

		/*
		 *
		 * MySQL
		 *
		 */

		/**
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
		 * @param int  $id
		 * @param bool $removeAlso
		 */
		public function disconnectMysqlServer($id = -1, $removeAlso = true)
		{
			if(isset($this->_mysqlServers[$id]))
			{
				if($removeAlso === true)
				{
					$this->removeMysqlServer($id);
				}
				else
				{
					$this->_mysqlServers[$id]->disconnect();

					$this->debuggerLog('mysqlDisconnectionsInc');

					if($this->_activeMysqlServerId === $id)
					{
						$this->_activeMysqlServerId = -1;
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
		 *
		 * @return mixed
		 */
		public function addMysqlServer($dbUser, $dbPassword, $dbName = '', $dbHost = 'localhost', $dbPort = 3306)
		{
			$this->_mysqlServers[] = new PzMysqlServer($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $this->getSetting('mysql_connect_retry_attempts'), $this->getSetting('mysql_connect_retry_delay'));

			$newId = max(array_keys($this->_mysqlServers));

			if($this->getSetting('auto_assign_active_mysql_server') === true)
			{
				$this->_activeMysqlServerId = $newId;
			}

			if($this->getSetting('auto_connect_mysql_servers') === true)
			{
				$this->mysqlConnect($newId, $this->getSetting('auto_assign_active_mysql_server'));
			}

			return $newId;
		}

		/**
		 * @param $id
		 *
		 * @return bool
		 */
		public function removeMysqlServer($id)
		{
			$return = false;

			if(isset($this->_mysqlServers[$id]) && is_object($this->_mysqlServers[$id]))
			{
				$this->_mysqlServers[$id]->disconnect();

				$this->debuggerLog('mysqlDisconnectionsInc');

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
		 * @param      $servierId
		 * @param bool $setAsActive
		 *
		 * @return bool
		 */
		public function mysqlConnect($servierId = -1, $setAsActive = false)
		{
			if($servierId != -1)
			{
				if(isset($this->_mysqlServers[$servierId]))
				{
					if($this->_mysqlServers[$servierId]->connect())
					{
						$this->debuggerLog('mysqlConnectionsInc');

						if($setAsActive === true)
						{
							$this->_activeMysqlServerId = $servierId;
						}

						return true;
					}
					else
					{
						$this->addToLog($this->_pzLoggerObjectMysql, 'Failed to connect to MySQL server with id#'.$servierId.'.');

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
		 * @param $query
		 *
		 * @return bool
		 */
		public function mysqlRead($query)
		{
			if($this->_activeMysqlServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_mysqlServers[$this->_activeMysqlServerId]->isConnected() === false)
				{
					if($this->mysqlConnect($this->_activeMysqlServerId) === false)
					{
						return false;
					}
				}

				$result = $this->_mysqlServers[$this->_activeMysqlServerId]->returnMysqliObj()->query($query);

				if(!$result && strtoupper(substr($query,0,6)) === 'SELECT')
				{
					$this->addToLog($this->_pzLoggerObjectMysql, 'Query failed: "'.$query.'".');
				}

				$this->debuggerLog('mysqlReadsInc');
				$this->debuggerLog('mysqlLogQuery', $query);

				if(empty($result))
				{
					return false;
				}
				else
				{
					return $result;
				}
			}
		}

		/**
		 * @param $query
		 *
		 * @return bool|int
		 */
		public function mysqlWrite($query)
		{
			if($this->_activeMysqlServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_mysqlServers[$this->_activeMysqlServerId]->isConnected() === false)
				{
					if($this->mysqlConnect($this->_activeMysqlServerId) === false)
					{
						return false;
					}
				}

				$firstIntervalDelay = $this->getSetting('mysql_write_retry_first_interval_delay');
				$secondIntervalDelay = $this->getSetting('mysql_write_retry_second_interval_delay');

				$firstIntervalRetries = $this->getSetting('mysql_write_retry_first_interval_retries');
				$secondIntervalRetries = $this->getSetting('mysql_write_retry_second_interval_retries');

				$retryCodes = array(
					1213, //Deadlock found when trying to get lock
					1205 //Lock wait timeout exceeded
				);

				//Initialize
				$cnt_retry = 0;

				//Main loop
				do
				{
					//Initialize 'flag_retry' indicating whether or not we need to retry this transaction
					$flag_retry = 0;

					// Write query (UPDATE, INSERT)
					$result = $this->_mysqlServers[$this->_activeMysqlServerId]->returnMysqliObj()->query($query);
					$mysql_errno = $this->_mysqlServers[$this->_activeMysqlServerId]->returnMysqliObj()->errno;
					$mysql_error = $this->_mysqlServers[$this->_activeMysqlServerId]->returnMysqliObj()->error;

					$this->debuggerLog('mysqlWritesInc');
					$this->debuggerLog('mysqlLogQuery', $query);

					// If failed,
					if(!$result)
					{
						// Determine if we need to retry this transaction -
						// If duplicate PRIMARY key error,
						// or one of the errors in 'arr_need_to_retry_error_codes'
						// then we need to retry
						if($mysql_errno == 1062 && strpos($mysql_error,"for key 'PRIMARY'") !== false)
						{
							$this->addToLog($this->_pzLoggerObjectMysql, 'Duplicate Primary Key error for query: "'.$query.'".');
						}

						$flag_retry = (in_array($mysql_errno, $retryCodes));

						if(!empty($flag_retry))
						{
							$this->addToLog($this->_pzLoggerObjectMysql, 'Deadlock detected for query: "'.$query.'".');
						}
					}

					// If successful or failed but no need to retry
					if($result || empty($flag_retry))
					{
						// We're done
						break;
					}

					$cnt_retry++;

					if($cnt_retry <= $firstIntervalRetries)
					{
						if($cnt_retry === $firstIntervalRetries)
						{
							$this->addToLog($this->_pzLoggerObjectMysql, 'Reducing retry interval for deadlock detection on query: "'.$query.'".');
						}

						usleep($firstIntervalDelay);
					}
					elseif($cnt_retry > $firstIntervalRetries && $cnt_retry <= $secondIntervalRetries)
					{
						usleep($secondIntervalDelay);
					}
					else
					{
						$result = 0;
						$cnt_retry--;

						$this->addToLog($this->_pzLoggerObjectMysql, 'Finally gave up on query: "'.$query.'".');

						break;
					}
				}
				while(1);

				// If update query failed, log
				if(!$result)
				{
					$this->addToLog($this->_pzLoggerObjectMysql, 'Query failed: "'.$query.'".');
				}

				if($cnt_retry > 0 && $cnt_retry < $secondIntervalRetries)
				{
					$this->addToLog($this->_pzLoggerObjectMysql, 'Query finally succeeded: "'.$query.'".');
				}

				// Return result
				if(empty($result))
				{
					return false;
				}
				else
				{
					return $result;
				}
			}
		}

		/**
		 * @param      $id
		 * @param bool $autoConnect
		 *
		 * @return bool
		 */
		public function setActiveMysqlServerId($id, $autoConnect = true)
		{
			if(isset($this->_mysqlServers[$id]))
			{
				$this->_activeMysqlServerId = $id;

				if($autoConnect === true)
				{
					$this->_mysqlServers[$id]->connect();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function mysqlAffectedRows($id = -1)
		{
			$id = ($id===-1?$this->_activeMysqlServerId:$id);

			return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]->affectedRows():0);
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function mysqlInsertId($id = -1)
		{
			$id = ($id===-1?$this->_activeMysqlServerId:$id);

			return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]->insertId():0);
		}

		/**
		 * @param $dbName
		 * @param $id
		 *
		 * @return bool
		 */
		public function mysqlSelectDatabase($dbName, $id = -1)
		{
			$id = ($id===-1?$this->_activeMysqlServerId:$id);

			return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]->selectDatabase($dbName):false);
		}

		/**
		 * @param      $user
		 * @param      $password
		 * @param null $dbName
		 * @param      $id
		 *
		 * @return bool
		 */
		public function mysqlChangeUser($user, $password, $dbName = NULL, $id = -1)
		{
			$id = ($id===-1?$this->_activeMysqlServerId:$id);

			return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]->changeUser($user, $password, $dbName):false);
		}

		/**
		 * @param $id
		 *
		 * @return bool
		 */
		public function mysqlActiveObject($id = -1)
		{
			$id = ($id===-1?$this->_activeMysqlServerId:$id);

			return (isset($this->_mysqlServers[$id])?$this->_mysqlServers[$id]->returnMysqliObj():false);
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

					$this->debuggerLog('mcdDisconnectionsInc');

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

					$this->debuggerLog('mcDisconnectionsInc');

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
			$this->_memcachedServers[] = new PzMemcachedServer($mcIp, $mcPort, $this->getSetting('memcache_connect_retry_attempts'), $this->getSetting('memcache_connect_retry_delay'));

			$newId = max(array_keys($this->_memcachedServers));

			if($this->getSetting('auto_assign_active_memcache_server') === true)
			{
				$this->_activeMemcachedServerId = $newId;
			}

			if($this->getSetting('auto_connect_memcache_servers') === true)
			{
				$this->memcachedConnect($newId, $this->getSetting('auto_assign_active_memcache_server'));
			}

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
			$this->_memcacheServers[] = new PzMemcacheServer($mcIp, $mcPort, $this->getSetting('memcache_connect_retry_attempts'), $this->getSetting('memcache_connect_retry_delay'));

			$newId = max(array_keys($this->_memcacheServers));

			if($this->getSetting('auto_assign_active_memcache_server') === true)
			{
				$this->_activeMemcacheServerId = $newId;
			}

			if($this->getSetting('auto_connect_memcache_servers') === true)
			{
				$this->memcacheConnect($newId, $this->getSetting('auto_assign_active_memcache_server'));
			}

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

				$this->debuggerLog('mcdDisconnectionsInc');

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

				$this->debuggerLog('mcDisconnectionsInc');

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
						$this->debuggerLog('mcdConnectionsInc');

						if($setAsActive === true)
						{
							$this->_activeMemcachedServerId = $servierId;
						}

						return true;
					}
					else
					{
						$this->addToLog($this->_pzLoggerObjectMemcached, 'Failed to connect to Memcached server with id#'.$servierId.'.');

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
						$this->debuggerLog('mcConnectionsInc');

						if($setAsActive === true)
						{
							$this->_activeMemcacheServerId = $servierId;
						}

						return true;
					}
					else
					{
						$this->addToLog($this->_pzLoggerObjectMemcache, 'Failed to connect to Memcache server with id#'.$servierId.'.');

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
		 * @param      $key
		 * @param      $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $checkFirst
		 *
		 * @return bool
		 */
		public function mcdWrite($key, $value, $expires = 0, $deleteLock = false, $checkFirst = true)
		{
			if($this->_activeMemcachedServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcachedServers[$this->_activeMemcachedServerId]->isConnected() === false)
				{
					if($this->memcachedConnect($this->_activeMemcachedServerId) === false)
					{
						return false;
					}
				}

				if($checkFirst === true)
				{
					$replace = $this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->replace($key, (is_scalar($value)?(string)$value:$value), $expires);

					$this->debuggerLog('mcdWritesInc');

					if($replace === false)
					{
						$return = $this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->add($key, (is_scalar($value)?(string)$value:$value), $expires);

						$this->debuggerLog('mcdWritesInc');
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					if($this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->add($key, (is_scalar($value)?(string)$value:$value), $expires) === true)
					{
						$this->debuggerLog('mcdWritesInc');

						if((is_scalar($value)?(string)$value:$value) == $this->mcdRead($key))
						{
							$return = true;
						}
						else
						{
							$return = false;
						}
					}
					else
					{
						$return = false;
					}
				}

				if($deleteLock === true)
				{
					$this->mcdDelete($key.'_pzLock');
				}

				return $return;
			}
		}

		/**
		 * @param $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function mcdRead($key, $checkLock = false)
		{
			if($this->_activeMemcachedServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcachedServers[$this->_activeMemcachedServerId]->isConnected() === false)
				{
					if($this->memcachedConnect($this->_activeMemcachedServerId) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->debuggerLog('mcdReadsInc');

					return $this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->get($key);
				}
				else
				{
					while($this->mcdWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->mcdRead($key);
				}
			}
		}

		/**
		 * @param      $key
		 * @param bool $checkLock
		 *
		 * @return mixed
		 */
		public function mcdDelete($key, $checkLock = false)
		{
			if($this->_activeMemcachedServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcachedServers[$this->_activeMemcachedServerId]->isConnected() === false)
				{
					if($this->memcachedConnect($this->_activeMemcachedServerId) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->delete($key);

					$this->debuggerLog('mcdDeletesInc');

					if(substr($key, -7) !== '_pzLock')
					{
						$this->_memcachedServers[$this->_activeMemcachedServerId]->returnMemcachedObj()->delete($key.'_pzLock');

						$this->debuggerLog('mcdDeletesInc');
					}
				}
				else
				{
					while($this->mcdWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->mcdDelete($key);
				}
			}
		}

		/**
		 * @param      $key
		 * @param      $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $checkFirst
		 *
		 * @return bool
		 */
		public function mcWrite($key, $value, $expires = 0, $deleteLock = false, $checkFirst = true)
		{
			if($this->_activeMemcacheServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcacheServers[$this->_activeMemcacheServerId]->isConnected() === false)
				{
					if($this->memcacheConnect($this->_activeMemcacheServerId) === false)
					{
						return false;
					}
				}

				if($checkFirst === true)
				{
					$replace = $this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->replace($key, (is_scalar($value)?(string)$value:$value), MEMCACHE_COMPRESSED, $expires);

					$this->debuggerLog('mcWritesInc');

					if($replace === false)
					{
						$return = $this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->add($key, (is_scalar($value)?(string)$value:$value), MEMCACHE_COMPRESSED, $expires);

						$this->debuggerLog('mcWritesInc');
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					if($this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->add($key, (is_scalar($value)?(string)$value:$value), MEMCACHE_COMPRESSED, $expires) === true)
					{
						$this->debuggerLog('mcWritesInc');

						if((is_scalar($value)?(string)$value:$value) == $this->mcRead($key))
						{
							$return = true;
						}
						else
						{
							$return = false;
						}
					}
					else
					{
						$return = false;
					}
				}

				if($deleteLock === true)
				{
					$this->mcDelete($key.'_pzLock');
				}

				return $return;
			}
		}

		/**
		 * @param $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function mcRead($key, $checkLock = false)
		{
			if($this->_activeMemcacheServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcacheServers[$this->_activeMemcacheServerId]->isConnected() === false)
				{
					if($this->memcacheConnect($this->_activeMemcacheServerId) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->debuggerLog('mcReadsInc');

					return $this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->get($key);
				}
				else
				{
					while($this->mcWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->mcRead($key);
				}
			}
		}

		/**
		 * @param      $key
		 * @param bool $checkLock
		 *
		 * @return mixed
		 */
		public function mcDelete($key, $checkLock = false)
		{
			if($this->_activeMemcacheServerId == -1)
			{
				return false;
			}
			else
			{
				if($this->_memcacheServers[$this->_activeMemcacheServerId]->isConnected() === false)
				{
					if($this->memcacheConnect($this->_activeMemcacheServerId) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->delete($key);

					$this->debuggerLog('mcDeletesInc');

					if(substr($key, -7) !== '_pzLock')
					{
						$this->_memcacheServers[$this->_activeMemcacheServerId]->returnMemcacheObj()->delete($key.'_pzLock');

						$this->debuggerLog('mcDeletesInc');
					}
				}
				else
				{
					while($this->mcWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->mcDelete($key);
				}
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

		/*
		 *
		 * APC
		 *
		 */

		/**
		 * @param      $key
		 * @param      $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $deleteOnExist
		 *
		 * @return bool
		 */
		public function apcWrite($key, $value, $expires = 0, $deleteLock = false, $deleteOnExist = true)
		{
			if(apc_add($key, (is_scalar($value)?(string)$value:$value), $expires) === true)
			{
				$this->debuggerLog('apcWritesInc');

				if((is_scalar($value)?(string)$value:$value) == $this->apcRead($key))
				{
					$return = true;
				}
				else
				{
					$return = false;
				}
			}
			else
			{
				if($deleteOnExist === true)
				{
					$this->apcDelete($key, true);

					$return = $this->apcWrite($key, $value, $expires, $deleteLock);
				}
				else
				{
					$return = false;
				}
			}

			if($deleteLock === true)
			{
				$this->apcDelete($key.'_pzLock');
			}

			return $return;
		}

		/**
		 * @param $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function apcRead($key, $checkLock = false)
		{
			if($checkLock === false)
			{
				$this->debuggerLog('apcReadsInc');

				return apc_fetch($key);
			}
			else
			{
				while($this->apcWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->apcRead($key);
			}
		}

		/**
		 * @param      $key
		 * @param bool $checkLock
		 *
		 * @return mixed
		 */
		public function apcDelete($key, $checkLock = false)
		{
			if($checkLock === false)
			{
				apc_delete($key);

				$this->debuggerLog('apcDeletesInc');

				if(substr($key, -7) !== '_pzLock')
				{
					apc_delete($key.'_pzLock');

					$this->debuggerLog('apcDeletesInc');
				}
			}
			else
			{
				while($this->apcWrite($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->apcDelete($key);
			}
		}

		/*
		 *
		 * SHARED MEMORY
		 *
		 */

		/**
		 * @param $keyname
		 *
		 * @return string
		 */
		private function _shmKeyToHex($keyname)
		{
			return bin2hex($keyname);
		}

		/**
		 * @param $value
		 *
		 * @return string
		 */
		private function _shmValueToString($value)
		{
			if(is_scalar($value))
			{
				return (string)$value;
			}
			else
			{
				return serialize($value);
			}
		}

		/**
		 * @param $value
		 *
		 * @return string
		 */
		private function _shmStringToValue($value)
		{
			$validValue = @unserialize($value);

			if($validValue !== false)
			{
				return $validValue;
			}
			else
			{
				return $value;
			}
		}

		/**
		 * @param      $key
		 * @param      $value
		 * @param bool $deleteLock
		 * @param bool $deleteOnExist
		 *
		 * @return bool
		 */
		public function shmWrite($key, $value, $deleteLock = false, $deleteOnExist = true)
		{
			$validKey = $this->_shmKeyToHex($key);
			$validValue = $this->_shmValueToString($value);

			$shm_id = @shmop_open($validKey, 'a', 0644, 0);

			if(!empty($shm_id))
			{
				if($deleteOnExist === true)
				{
					$this->debuggerLog('shmDeletesInc');

					shmop_delete($shm_id);

					shmop_close($shm_id);

					$return = $this->shmWrite($key, $value, $deleteLock, $deleteOnExist);
				}
				else
				{
					$return = false;
				}
			}
			else
			{
				$shm_id = shmop_open($validKey, "c", 0644, strlen($validValue));
				shmop_write($shm_id, $validValue, 0);

				shmop_close($shm_id);

				if($deleteLock === true)
				{
					$validKey = $this->_shmKeyToHex($key.'_pzLock');

					$shm_id = @shmop_open($validKey, 'a', 0644, 0);

					if(!empty($shm_id))
					{
						$this->debuggerLog('shmDeletesInc');

						shmop_delete($shm_id);
					}

					shmop_close($shm_id);
				}

				$return = true;
			}

			return $return;
		}

		/**
		 * @param $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function shmRead($key, $checkLock = false)
		{
			$validKey = $this->_shmKeyToHex($key);

			if($checkLock === false)
			{
				$shm_id = @shmop_open($validKey, 'a', 0644, 0);

				if(!empty($shm_id))
				{
					$this->debuggerLog('shmReadsInc');

					$data = shmop_read($shm_id, 0, shmop_size($shm_id));

					shmop_close($shm_id);

					return $this->_shmStringToValue($data);
				}
				else
				{
					return false;
				}
			}
			else
			{
				while($this->shmWrite($key.'_pzLock', mt_rand(1,2000000000), false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->shmRead($key);
			}
		}

		/**
		 * @param      $key
		 * @param bool $checkLock
		 *
		 * @return mixed
		 */
		public function shmDelete($key, $checkLock = false)
		{
			$validKey = $this->_shmKeyToHex($key);

			if($checkLock === false)
			{
				$shm_id = @shmop_open($validKey, 'a', 0644, 0);

				if(!empty($shm_id))
				{
					$this->debuggerLog('shmDeletesInc');

					shmop_delete($shm_id);

					shmop_close($shm_id);

					if(substr($key, -7) !== '_pzLock')
					{
						$validKey = $this->_shmKeyToHex($key.'_pzLock');

						$shm_id = @shmop_open($validKey, 'a', 0644, 0);

						if(!empty($shm_id))
						{
							$this->debuggerLog('shmDeletesInc');

							shmop_delete($shm_id);

							shmop_close($shm_id);
						}
					}

					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				while($this->shmWrite($key.'_pzLock', mt_rand(1,2000000000), false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->shmDelete($key);
			}
		}
	}
