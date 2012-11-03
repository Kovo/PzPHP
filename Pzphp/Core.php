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
			$this->_registeredModules['Pz_Core'] = new Pz_Core(array(
				'mysql_connect_retry_attempts' => PZ_MYSQL_CONNECTION_RETRIES,
				'mysql_connect_retry_delay' => PZ_MYSQL_CONNECTION_RETRY_DELAY,
				'auto_connect_mysql_servers' => PZ_MYSQL_AUTO_CONNECT_NEW_SERVER,
				'auto_assign_active_mysql_server' => PZ_MYSQL_AUTO_ASSIGN_NEW_SERVER_AS_ACTIVE,
				'mysql_write_retry_first_interval_delay' => PZ_MYSQL_WRITE_RETRY_FIRST_DELAY_INTERVAL,
				'mysql_write_retry_second_interval_delay' => PZ_MYSQL_WRITE_RETRY_SECOND_DELAY_INTERVAL,
				'mysql_write_retry_first_interval_retries' => PZ_MYSQL_WRITE_RETRY_FIRST_DELAY_RETRIES,
				'mysql_write_retry_second_interval_retries' => PZ_MYSQL_WRITE_RETRY_SECONDT_DELAY_RETRIES,
				'memcache_connect_retry_attempts' => PZ_MEMCACHE_CONNECTION_RETRIES,
				'memcache_connect_retry_delay' => PZ_MEMCACHE_CONNECTION_RETRY_DELAY,
				'auto_connect_memcache_servers' => PZ_MEMCACHE_AUTO_CONNECT_NEW_SERVER,
				'auto_assign_active_memcache_server' => PZ_MEMCACHE_AUTO_ASSIGN_NEW_SERVER_AS_ACTIVE,
				'whitelist_ip_check' => PZ_WHITELIST_IP_CHECK,
				'whitelist_ips' => PZ_WHITELIST_IPS,
				'whitelist_action' => array(
					'action' => PZ_WHITELIST_ACTION,
					'target' => PZ_WHITELIST_TARGET,
					'message' => PZ_WHITELIST_MESSAGE
				),
				'whitelist_auto_allow_host_server_ip' => PZ_WHITELIST_AUTO_ALLOW_HOST_SERVER_IP,
				'blacklist_ip_check' => PZ_BLACKLIST_IP_CHECK,
				'blacklist_ips' => PZ_BLACKLIST_IPS,
				'blacklist_action' => array(
					'action' => PZ_BLACKLIST_ACTION,
					'target' => PZ_BLACKLIST_TARGET,
					'message' => PZ_BLACKLIST_MESSAGE
				),
				'blacklist_ignore_host_server_ip' => PZ_BLACKLIST_AUTO_IGNORE_HOST_SERVER_IP,
				'redirect_for_ajax_calls' => PZ_REDIRECT_FOR_AJAX_CALLS,
				'ajax_redirect_message' => PZ_AJAX_REDIRECT_MESSAGE,
				'compress_output' => PZ_COMPRESS_OUTPUT,
				'output_buffering' => PZ_OUTPUT_BUFFERING,
				'domain_protection' => PZ_DOMAIN_PROTECTION,
				'allowed_domains' => PZ_ALLOWED_DOMAINS,
				'target_domain' => PZ_TARGET_DOMAIN,
				'debug_mode' => PZ_DEBUG_MODE,
				'display_debug_bar' => PZ_DEBUG_BAR_DISPLAY,
				'debug_db_user' => PZ_DEBUG_DB_USER,
				'debug_db_password' => PZ_DEBUG_DB_PASSWORD,
				'debug_db_name' => PZ_DEBUG_DB_NAME,
				'debug_db_host' => PZ_DEBUG_DB_HOST,
				'debug_db_port' => PZ_DEBUG_DB_PORT,
				'debug_db_log' => PZ_DEBUG_DB_LOG,
				'debug_log_file_auto_rotate' => PZ_DEBUG_LOG_FILE_AUTO_ROTATE,
				'debug_delete_log_files_after_x_days' => PZ_DEBUG_LOG_DELETE_FILE_AFTER_X_DAYS,
				'debug_mysql_log_errors' => PZ_DEBUG_LOG_MYSQL_ERRORS,
				'debug_mysql_error_log_file_name' => PZ_DEBUG_LOG_MYSQL_ERROR_LOG_FILE_NAME,
				'debug_memcache_log_errors' => PZ_DEBUG_LOG_MEMCACHE_ERRORS,
				'debug_memcache_error_log_file_name' => PZ_DEBUG_LOG_MEMCACHE_ERROR_LOG_FILE_NAME,
				'debug_memcached_log_errors' => PZ_DEBUG_LOG_MEMCACHED_ERRORS,
				'debug_memcached_error_log_file_name' => PZ_DEBUG_LOG_MEMCACHED_ERROR_LOG_FILE_NAME,
				'debug_log_php_errors' => PZ_DEBUG_LOG_PHP_ERRORS,
				'debug_php_error_log_file_name' => PZ_DEBUG_LOG_PHP_ERROR_LOG_FILE_NAME,
				'debug_php_display_errors' => PZ_DEBUG_LOG_DISPLAY_PHP_ERRORS
			));

			$this->registerModule('PzPHP_Cache');
			$this->registerModule('PzPHP_Db');
			$this->registerModule('PzPHP_Security');

			$this->pz()->debugger('registerVersionInfo', array('PzPHP', self::VERSION));
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
