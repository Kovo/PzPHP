<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzphpCore
	 */
	class PzphpCore
	{
		/**
		 * @var null|PzphpCore
		 */
		private $_pzObject = NULL;

		function __construct()
		{
			$this->_pzObject = new PzCore(array(
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
				'whitelist_auto_allow_host_server_ip' => PZ_WHITELIST_AUTO,
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
				'debug_php_error_log_file_name' => PZ_DEBUG_LOG_MEMCACHED_ERROR_LOG_FILE_NAME,
				'debug_php_display_errors' => PZ_DEBUG_LOG_DISPLAY_PHP_ERRORS
			));
		}

		/**
		 * @return null|PzCore|PzphpCore
		 */
		public function getPz()
		{
			return $this->_pzObject;
		}


	}
