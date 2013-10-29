<?php
###INIT LOGGING###
$_PZPHP->log()
	->registerLog(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), PzPHP_Config::get('LOGS_DIR').'MYSQL')
	->registerLog(PzPHP_Config::get('SETTING_MEMCACHE_ERROR_LOG_FILE_NAME'), PzPHP_Config::get('LOGS_DIR').'MEMCACHED')
	->warmup();
