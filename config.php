<?php
	/*
	 * PzPHP Core Constants
	 */
	define('CACHE_MODE_NO_CACHING', 0);
	define('CACHE_MODE_SHARED_MEMORY', 1);
	define('CACHE_MODE_APC', 2);
	define('CACHE_MODE_MEMCACHE', 3);
	define('CACHE_MODE_MEMCACHED', 4);

	/*
	 * PzPHP settings
	 */
	define('CACHING_MODE', CACHE_MODE_NO_CACHING);

	/*
	 * PzSecurity Settings
	 */
	define('PZ_SECURITY_HASH_TABLE', '');
	define('PZ_SECURITY_SALT', '');
	define('PZ_SECURITY_POISON_CONSTRAINTS', '');
	define('PZ_SECURITY_REHASH_DEPTH', '');

	/*
	 * Pz specific settings
	 */
	define('PZ_MYSQL_CONNECTION_RETRIES', 1);
	define('PZ_MYSQL_CONNECTION_RETRY_DELAY', 2);
	define('PZ_MYSQL_AUTO_CONNECT_NEW_SERVER', false);
	define('PZ_MYSQL_AUTO_ASSIGN_NEW_SERVER_AS_ACTIVE', true);
	define('PZ_MYSQL_WRITE_RETRY_FIRST_DELAY_INTERVAL', 3000000);
	define('PZ_MYSQL_WRITE_RETRY_SECOND_DELAY_INTERVAL', 500000);
	define('PZ_MYSQL_WRITE_RETRY_FIRST_DELAY_RETRIES', 3);
	define('PZ_MYSQL_WRITE_RETRY_SECONDT_DELAY_RETRIES', 6);

	define('PZ_MEMCACHE_CONNECTION_RETRIES', 1);
	define('PZ_MEMCACHE_CONNECTION_RETRY_DELAY', 2);
	define('PZ_MEMCACHE_AUTO_CONNECT_NEW_SERVER', false);
	define('PZ_MEMCACHE_AUTO_ASSIGN_NEW_SERVER_AS_ACTIVE', true);

	define('PZ_WHITELIST_IP_CHECK', false);
	define('PZ_WHITELIST_IPS', '');
	define('PZ_WHITELIST_ACTION', 'exit');
	define('PZ_WHITELIST_TARGET', '');
	define('PZ_WHITELIST_MESSAGE', '<h1>Access Denied</h1>');
	define('PZ_WHITELIST_AUTO_ALLOW_HOST_SERVER_IP', true);

	define('PZ_BLACKLIST_IP_CHECK', false);
	define('PZ_BLACKLIST_IPS', '');
	define('PZ_BLACKLIST_ACTION', 'exit');
	define('PZ_BLACKLIST_TARGET', '');
	define('PZ_BLACKLIST_MESSAGE', '<h1>You have been banned from this website</h1>');
	define('PZ_BLACKLIST_AUTO_IGNORE_HOST_SERVER_IP', true);

	define('PZ_REDIRECT_FOR_AJAX_CALLS', false);
	define('PZ_AJAX_REDIRECT_MESSAGE', '');

	define('PZ_COMPRESS_OUTPUT', true);
	define('PZ_OUTPUT_BUFFERING', true);

	define('PZ_DOMAIN_PROTECTION', false);
	define('PZ_ALLOWED_DOMAINS', '');
	define('PZ_TARGET_DOMAIN', '');

	define('PZ_DEBUG_MODE', false);
	define('PZ_DEBUG_BAR_DISPLAY', false);
	define('PZ_DEBUG_DB_USER', '');
	define('PZ_DEBUG_DB_PASSWORD', '');
	define('PZ_DEBUG_DB_NAME', '');
	define('PZ_DEBUG_DB_HOST', 'localhost');
	define('PZ_DEBUG_DB_PORT', 3306);
	define('PZ_DEBUG_DB_LOG', false);
	define('PZ_DEBUG_LOG_FILE_AUTO_ROTATE', true);
	define('PZ_DEBUG_LOG_DELETE_FILE_AFTER_X_DAYS', 7);
	define('PZ_DEBUG_LOG_MYSQL_ERRORS', true);
	define('PZ_DEBUG_LOG_MYSQL_ERROR_LOG_FILE_NAME', 'MYSQL_ERRORS');
	define('PZ_DEBUG_LOG_MEMCACHE_ERRORS', true);
	define('PZ_DEBUG_LOG_MEMCACHE_ERROR_LOG_FILE_NAME', 'MEMCACHE_ERRORS');
	define('PZ_DEBUG_LOG_MEMCACHED_ERRORS', true);
	define('PZ_DEBUG_LOG_MEMCACHED_ERROR_LOG_FILE_NAME', 'MEMCACHED_ERRORS');
	define('PZ_DEBUG_LOG_PHP_ERRORS', true);
	define('PZ_DEBUG_LOG_MEMCACHED_ERROR_LOG_FILE_NAME', 'PHP_ERRORS');
	define('PZ_DEBUG_LOG_DISPLAY_PHP_ERRORS', false);
