<?php
###DIRECTORIES###
$PZPHP_CONFIG_ARRAY['LOGS_DIR'] = $PZPHP_CONFIG_ARRAY['BASE_DIR'].'LOGS'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['RESOURCES_DIR'] = $PZPHP_CONFIG_ARRAY['BASE_DIR'].'Resources'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['VIEWS_DIR'] = $PZPHP_CONFIG_ARRAY['RESOURCES_DIR'].'views'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['TRANSLATIONS_DIR'] = $PZPHP_CONFIG_ARRAY['RESOURCES_DIR'].'translations'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['CSS_DIR'] = $PZPHP_CONFIG_ARRAY['RESOURCES_DIR'].'css'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['JS_DIR'] = $PZPHP_CONFIG_ARRAY['RESOURCES_DIR'].'js'.DIRECTORY_SEPARATOR;
$PZPHP_CONFIG_ARRAY['IMAGES_DIR'] = $PZPHP_CONFIG_ARRAY['RESOURCES_DIR'].'images'.DIRECTORY_SEPARATOR;

###CACHING###
$PZPHP_CONFIG_ARRAY['CACHE_MODE_NO_CACHING'] = 0;
$PZPHP_CONFIG_ARRAY['CACHE_MODE_SHARED_MEMORY'] = 1;
$PZPHP_CONFIG_ARRAY['CACHE_MODE_APC'] = 2;
$PZPHP_CONFIG_ARRAY['CACHE_MODE_MEMCACHE'] = 3;
$PZPHP_CONFIG_ARRAY['CACHE_MODE_MEMCACHED'] = 4;
$PZPHP_CONFIG_ARRAY['CACHE_MODE_LOCALCACHE'] = 5;

###DATABASES###
$PZPHP_CONFIG_ARRAY['DATABASE_NONE'] = 0;
$PZPHP_CONFIG_ARRAY['DATABASE_MYSQLI'] = 1;
$PZPHP_CONFIG_ARRAY['DATABASE_MYSQL'] = 2;
$PZPHP_CONFIG_ARRAY['DATABASE_PDO'] = 3;
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_CUBRID'] = 'cubrid';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_MSSQL'] = 'mssql';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_SYBASE'] = 'sybase';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_DBLIB'] = 'dblib';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_FIREBIRD'] = 'firebird';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_IBM'] = 'ibm';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_INFORMIX'] = 'informix';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_MYSQL'] = 'mysql';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_SQLSRV'] = 'sqlsrv';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_ORACLE'] = 'oci';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_ODBC'] = 'odbc';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_ODBC_IBMDB2'] = 'odbcibmdb2';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_ODBC_MSACCSS'] = 'odbcmsaccss';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_POSTGRESQL'] = 'pgsql';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_SQLITE'] = 'sqlite';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_SQLITE2'] = 'sqlite2';
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_4D'] = '4d';

###DB & CACHE MODES###
$PZPHP_CONFIG_ARRAY['CACHING_MODE'] = $PZPHP_CONFIG_ARRAY['CACHE_MODE_NO_CACHING'];
$PZPHP_CONFIG_ARRAY['DATABASE_MODE'] = $PZPHP_CONFIG_ARRAY['DATABASE_NONE'];
$PZPHP_CONFIG_ARRAY['DATABASE_PDO_MODE'] = '';

###SECURITY###
$PZPHP_CONFIG_ARRAY['SETTING_SECURITY_HASH_TABLE'] = array();
$PZPHP_CONFIG_ARRAY['SETTING_SECURITY_SALT'] = '';
$PZPHP_CONFIG_ARRAY['SETTING_SECURITY_POISON_CONSTRAINTS'] = array();
$PZPHP_CONFIG_ARRAY['SETTING_SECURITY_REHASH_DEPTH'] = '';
$PZPHP_CONFIG_ARRAY['SETTING_SECURITY_CHECKSUM'] = '';

###DB SETTINGS###
$PZPHP_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_ATTEMPTS'] = 1;
$PZPHP_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$PZPHP_CONFIG_ARRAY['SETTING_DB_AUTO_CONNECT_SERVER'] = false;
$PZPHP_CONFIG_ARRAY['SETTING_DB_AUTO_ASSIGN_ACTIVE_SERVER'] = true;
$PZPHP_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_DELAY_SECONDS'] = 0.3;
$PZPHP_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_DELAY_SECONDS'] = 0.5;
$PZPHP_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_RETRIES'] = 3;
$PZPHP_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_RETRIES'] = 6;

###CACHE SETTINGS###
$PZPHP_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_ATTEMPTS'] = 1;
$PZPHP_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$PZPHP_CONFIG_ARRAY['SETTING_CACHE_AUTO_CONNECT_SERVER'] = false;
$PZPHP_CONFIG_ARRAY['SETTING_CACHE_AUTO_ASSIGN_ACTIVE_SERVER'] = true;
$PZPHP_CONFIG_ARRAY['SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'] = 15;

###OUTPUT SETTINGS###
$PZPHP_CONFIG_ARRAY['SETTING_OUTPUT_COMPRESSION'] = true;
$PZPHP_CONFIG_ARRAY['SETTING_OUTPUT_BUFFERING'] = true;

###DOMAIN SETTINGS###
$PZPHP_CONFIG_ARRAY['SETTING_DOMAIN_PROTECTION'] = false;
$PZPHP_CONFIG_ARRAY['SETTING_DOMAIN_ALLOWED_DOMAINS'] = '';
$PZPHP_CONFIG_ARRAY['SETTING_DOMAIN_SOLUTION'] = array('type' => '', 'value' => '');

###LOGGING###
$PZPHP_CONFIG_ARRAY['SETTING_LOG_FILE_AUTO_ROTATE'] = true;
$PZPHP_CONFIG_ARRAY['SETTING_DELETE_LOG_FILES_AFTER_DAYS'] = 7;
$PZPHP_CONFIG_ARRAY['SETTING_MYSQL_ERROR_LOG_FILE_NAME'] = 'MYSQL_ERRORS';
$PZPHP_CONFIG_ARRAY['SETTING_MEMCACHE_ERROR_LOG_FILE_NAME'] = 'MEMCACHE_ERRORS';
$PZPHP_CONFIG_ARRAY['SETTING_PDO_ERROR_LOG_FILE_NAME'] = 'PDO_ERRORS';
$PZPHP_CONFIG_ARRAY['SETTING_LOG_PHP_ERRORS'] = true;
$PZPHP_CONFIG_ARRAY['SETTING_PHP_ERROR_LOG_FILE_NAME'] = 'PHP_ERRORS';
$PZPHP_CONFIG_ARRAY['SETTING_PHP_DISPLAY_ERRORS'] = false;
