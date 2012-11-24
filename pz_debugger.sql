DROP TABLE IF EXISTS `pz_debugger`;
CREATE TABLE IF NOT EXISTS `pz_debugger` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mysql_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mysql_read_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mysql_write_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pdo_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pdo_read_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pdo_write_queries` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mc_writes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mc_deletes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mc_reads` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mcd_writes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mcd_deletes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mcd_reads` smallint(5) unsigned NOT NULL DEFAULT '0',
  `apc_writes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `apc_deletes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `apc_reads` smallint(5) unsigned NOT NULL DEFAULT '0',
  `shm_writes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `shm_deletes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `shm_reads` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lc_writes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lc_deletes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lc_reads` smallint(5) unsigned NOT NULL DEFAULT '0',
  `includes` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mysql_connections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pdo_connections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mc_connections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mcd_connections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mysql_disconnections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pdo_disconnections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mc_disconnections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mcd_disconnections` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mysql_queries_executed` blob NOT NULL,
  `start_memory_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `start_memory_real_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `start_peak_memory_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `start_peak_memory_real_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `end_memory_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `end_memory_real_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `end_peak_memory_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `end_peak_memory_real_usage` int(10) unsigned NOT NULL DEFAULT '0',
  `exec_time` float(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `exec_start_time` float(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `exec_end_time` float(10,4) unsigned NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
