<?php
	/*
	 * PzPHP Core Constants
	 */
	define('CACHE_MODE_NO_CACHING', 0);
	define('CACHE_MODE_SHARED_MEMORY', 1);
	define('CACHE_MODE_APC', 2);
	define('CACHE_MODE_MEMCACHE', 3);
	define('CACHE_MODE_MEMCACHED', 4);
	define('CACHE_MODE_LOCALCACHE', 5);

	/*
	 * PzPHP settings
	 */
	define('CACHING_MODE', CACHE_MODE_NO_CACHING);

	/*
	 * Pz_Security Settings
	 */
	define('PZ_SECURITY_HASH_TABLE', '');
	define('PZ_SECURITY_SALT', '');
	define('PZ_SECURITY_POISON_CONSTRAINTS', '');
	define('PZ_SECURITY_REHASH_DEPTH', '');

	/*
	 * Pz specific settings
	 */
	define('PZ_SETTING_MYSQL_CONNECT_RETRY_ATTEMPTS', 1);
	define('PZ_SETTING_MYSQL_CONNECT_RETRY_DELAY', 2);
	define('PZ_SETTING_MYSQL_AUTO_CONNECT_SERVER', false);
	define('PZ_SETTING_MYSQL_AUTO_ASSIGN_ACTIVE_SERVER', true);
	define('PZ_SETTING_MYSQL_WRITE_RETRY_FIRST_INTERVAL_DELAY', 3000000);
	define('PZ_SETTING_MYSQL_WRITE_RETRY_SECOND_INTERVAL_DELAY', 500000);
	define('PZ_SETTING_MYSQL_WRITE_RETRY_FIRST_INTERVAL_RETRIES', 3);
	define('PZ_SETTING_MYSQL_WRITE_RETRY_SECOND_INTERVAL_RETRIES', 6);

	define('PZ_SETTING_MEMCACHE_CONNECT_RETRY_ATTEMPTS', 1);
	define('PZ_SETTING_MEMCACHE_CONNECT_RETRY_DELAY', 2);
	define('PZ_SETTING_MEMCACHE_AUTO_CONNECT_SERVER', false);
	define('PZ_SETTING_MEMCACHE_AUTO_ASSIGN_ACTIVE_SERVER', true);

	define('PZ_SETTING_OUTPUT_COMPRESSION', true);
	define('PZ_SETTING_OUTPUT_BUFFERING', true);

	define('PZ_SETTING_DOMAIN_PROTECTION', false);
	define('PZ_SETTING_DOMAIN_ALLOWED_DOMAINS', '');
	define('PZ_SETTING_DOMAIN_TARGET_DOMAIN', '');

	define('PZ_SETTING_DEBUG_MODE', true);
	define('PZ_SETTING_DEBUG_DISPLAY_BAR', false);
	define('PZ_SETTING_DEBUG_DB_USER', '');
	define('PZ_SETTING_DEBUG_DB_PASSWORD', '');
	define('PZ_SETTING_DEBUG_DB_NAME', '');
	define('PZ_SETTING_DEBUG_DB_HOST', 'localhost');
	define('PZ_SETTING_DEBUG_DB_PORT', 3306);
	define('PZ_SETTING_DEBUG_DB_LOG', false);
	define('PZ_SETTING_DEBUG_LOG_FILE_AUTO_ROTATE', true);
	define('PZ_SETTING_DEBUG_DELETE_LOG_FILES_AFTER_X_DAYS', 7);
	define('PZ_SETTING_DEBUG_MYSQL_LOG_ERRORS', true);
	define('PZ_SETTING_DEBUG_MYSQL_ERROR_LOG_FILE_NAME', 'MYSQL_ERRORS');
	define('PZ_SETTING_DEBUG_MEMCACHE_LOG_ERRORS', true);
	define('PZ_SETTING_DEBUG_MEMCACHE_ERROR_LOG_FILE_NAME', 'MEMCACHE_ERRORS');
	define('PZ_SETTING_DEBUG_MEMCACHED_LOG_ERRORS', true);
	define('PZ_SETTING_DEBUG_MEMCACHED_ERROR_LOG_FILE_NAME', 'MEMCACHED_ERRORS');
	define('PZ_SETTING_DEBUG_LOG_PHP_ERRORS', true);
	define('PZ_SETTING_DEBUG_PHP_ERROR_LOG_FILE_NAME', 'PHP_ERRORS');
	define('PZ_SETTING_DEBUG_PHP_DISPLAY_ERRORS', false);

	define('PZ_SETTING_WHITELIST_IP_CHECK', false);
	define('PZ_SETTING_WHITELIST_IPS', '');
	define('PZ_SETTING_WHITELIST_ACTION', 'a:3:{s:6:"action";s:4:"exit";s:6:"target";s:0:"";s:7:"message";s:22:"<h1>Access Denied</h1>";}');
	define('PZ_SETTING_WHITELIST_AUTO_ALLOW_HOST_SERVER_IP', true);

	define('PZ_SETTING_BLACKLIST_IP_CHECK', false);
	define('PZ_SETTING_BLACKLIST_IPS', '');
	define('PZ_SETTING_BLACKLIST_ACTION', 'a:3:{s:6:"action";s:4:"exit";s:6:"target";s:0:"";s:7:"message";s:47:"<h1>You have been banned from this website</h1>";}');
	define('PZ_SETTING_BLACKLIST_IGNORE_HOST_SERVER_IP', true);
