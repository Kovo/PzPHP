<?php
	/**
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package Pz_Debugger
	 */
	class Pz_Debugger
	{
		/**
		 * @var array
		 */
		private $_statistics = array(
			'mysql_queries' => 0,
			'mysql_read_queries' => 0,
			'mysql_write_queries' => 0,
			'mc_writes' => 0,
			'mc_deletes' => 0,
			'mc_reads' => 0,
			'mcd_writes' => 0,
			'mcd_deletes' => 0,
			'mcd_reads' => 0,
			'apc_writes' => 0,
			'apc_deletes' => 0,
			'apc_reads' => 0,
			'shm_writes' => 0,
			'shm_deletes' => 0,
			'shm_reads' => 0,
			'includes' => 0,
			'mysql_connections' => 0,
			'mc_connections' => 0,
			'mcd_connections' => 0,
			'mysql_disconnections' => 0,
			'mc_disconnections' => 0,
			'mcd_disconnections' => 0,
			'mysql_queries_executed' => array(),
			'start_memory_usage' => 0,
			'start_memory_real_usage' => 0,
			'start_peak_memory_usage' => 0,
			'start_peak_memory_real_usage' => 0,
			'end_memory_usage' => 0,
			'end_memory_real_usage' => 0,
			'end_peak_memory_usage' => 0,
			'end_peak_memory_real_usage' => 0,
			'script_memory_usage' => 0,
			'script_peak_memory_usage' => 0,
			'exec_time' => 0,
			'exec_start_time' => 0,
			'exec_end_time' => 0
		);

		/**
		 * @var string
		 */
		private $_dbUser = '';

		/**
		 * @var string
		 */
		private $_dbPassword = '';

		/**
		 * @var string
		 */
		private $_dbName = '';

		/**
		 * @var string
		 */
		private $_dbHost = 'localhost';

		/**
		 * @var int
		 */
		private $_dbPort = 3306;

		/**
		 * @var int
		 */
		private $_dbRetryattempts = 1;

		/**
		 * @var int
		 */
		private $_dbRetryDelay = 2;

		/**
		 * @var bool
		 */
		private $_displayBar = false;

		/**
		 * @var bool
		 */
		private $_logToDb = false;

		function __construct($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $displayBar, $logToDb, $dbRetryAttempts, $dbRetryDelay)
		{
			$this->_dbUser = $dbUser;
			$this->_dbPassword = $dbPassword;
			$this->_dbName = $dbName;
			$this->_dbHost = $dbHost;
			$this->_dbPort = $dbPort;
			$this->_displayBar = $displayBar;
			$this->_logToDb = $logToDb;
			$this->_dbRetryattempts = $dbRetryAttempts;
			$this->_dbRetryDelay = $dbRetryDelay;
		}

		/*
		 * Calculate total included files
		 */
		public function calculateIncludes()
		{
			$this->_statistics['includes'] = count(get_included_files());
		}

		/*
		 * Calculate total queries
		 */
		public function calculateMysqlQueries()
		{
			$this->_statistics['mysql_queries'] = $this->_statistics['mysql_read_queries']+$this->_statistics['mysql_write_queries'];
		}

		/*
		 * Calculate total execution time
		 */
		public function calculateExecTime()
		{
			$microtime = microtime(true);
			$this->_statistics['exec_time'] = bcsub($microtime,PZD_START_MICROTIME,4);
			$this->_statistics['exec_start_time'] = bcmul(PZD_START_MICROTIME,1,4);
			$this->_statistics['exec_end_time'] = bcmul($microtime,1,4);
		}

		/*
		 * Calculate total memory usage
		 */
		public function calculateMemoryUsage()
		{
			$this->_statistics['start_memory_usage'] = PZD_START_MEMORY_USE;
			$this->_statistics['start_memory_real_usage'] = PZD_START_MEMORY_USE_REAL;

			$this->_statistics['start_peak_memory_usage'] = PZD_START_MEMORY_PEAK_USE;
			$this->_statistics['start_peak_memory_real_usage'] = PZD_START_MEMORY_PEAK_USE_REAL;

			$this->_statistics['end_memory_usage'] = memory_get_usage();
			$this->_statistics['end_memory_real_usage'] = memory_get_usage(true);

			$this->_statistics['end_peak_memory_usage'] = memory_get_peak_usage();
			$this->_statistics['end_peak_memory_real_usage'] = memory_get_peak_usage(true);

			$this->_statistics['script_memory_usage'] = bcsub($this->_statistics['end_memory_usage'],$this->_statistics['start_memory_usage']);
			$this->_statistics['script_peak_memory_usage'] = bcsub($this->_statistics['end_peak_memory_usage'],$this->_statistics['start_peak_memory_usage']);
		}

		/*
		 * Increase mysql reads by 1
		 */
		public function mysqlReadsInc()
		{
			$this->_statistics['mysql_read_queries']++;
		}

		/*
		 * Increase mysql writes by 1
		 */
		public function mysqlWritesInc()
		{
			$this->_statistics['mysql_write_queries']++;
		}

		/*
		 * Increase mc writes by 1
		 */
		public function mcWritesInc()
		{
			$this->_statistics['mc_writes']++;
		}

		/*
		 * Increase mc deletes by 1
		 */
		public function mcDeletesInc()
		{
			$this->_statistics['mc_deletes']++;
		}

		/*
		 * Increase mc reads by 1
		 */
		public function mcReadsInc()
		{
			$this->_statistics['mc_reads']++;
		}

		/*
		 * Increase mcd writes by 1
		 */
		public function mcdWritesInc()
		{
			$this->_statistics['mcd_writes']++;
		}

		/*
		 * Increase mcd deletes by 1
		 */
		public function mcdDeletesInc()
		{
			$this->_statistics['mcd_deletes']++;
		}

		/*
		 * Increase mcd reads by 1
		 */
		public function mcdReadsInc()
		{
			$this->_statistics['mcd_reads']++;
		}

		/*
		 * Increase apc writes by 1
		 */
		public function apcWritesInc()
		{
			$this->_statistics['apc_writes']++;
		}

		/*
		 * Increase apc deletes by 1
		 */
		public function apcDeletesInc()
		{
			$this->_statistics['apc_deletes']++;
		}

		/*
		 * Increase apc reads by 1
		 */
		public function apcReadsInc()
		{
			$this->_statistics['apc_reads']++;
		}

		/*
		 * Increase shm writes by 1
		 */
		public function shmWritesInc()
		{
			$this->_statistics['shm_writes']++;
		}

		/*
		 * Increase shm deletes by 1
		 */
		public function shmDeletesInc()
		{
			$this->_statistics['shm_deletes']++;
		}

		/*
		 * Increase shm reads by 1
		 */
		public function shmReadsInc()
		{
			$this->_statistics['shm_reads']++;
		}

		/*
		 * Increase mysql connections by 1
		 */
		public function mysqlConnectionsInc()
		{
			$this->_statistics['mysql_connections']++;
		}

		/*
		 * Increase mysql disconnections by 1
		 */
		public function mysqlDisconnectionsInc()
		{
			$this->_statistics['mysql_disconnections']++;
		}

		/*
		 * Increase mc connections by 1
		 */
		public function mcConnectionsInc()
		{
			$this->_statistics['mc_connections']++;
		}

		/*
		 * Increase mc disconnections by 1
		 */
		public function mcDisconnectionsInc()
		{
			$this->_statistics['mc_disconnections']++;
		}

		/*
		 * Increase mcd connections by 1
		 */
		public function mcdConnectionsInc()
		{
			$this->_statistics['mcd_connections']++;
		}

		/*
		 * Increase mcd disconnections by 1
		 */
		public function mcdDisconnectionsInc()
		{
			$this->_statistics['mcd_disconnections']++;
		}

		/**
		 * @param $query
		 *
		 * logs actual query strings
		 */
		public function mysqlLogQuery($query)
		{
			$this->_statistics['mysql_queries_executed'][] = $query;
		}

		/*
		 * Calcualtes, logs, displays debugger info
		 */
		public function finalize(Pz_Core $_pzcoreObject)
		{
			$this->calculateExecTime();
			$this->calculateMemoryUsage();
			$this->calculateIncludes();
			$this->calculateMysqlQueries();

			if($this->_logToDb === true)
			{
				$pzMysqlObject = new Pz_Mysql_Server($this->_dbUser, $this->_dbPassword, $this->_dbName, $this->_dbHost, $this->_dbPort, $this->_dbRetryattempts, $this->_dbRetryDelay);

				if($pzMysqlObject->connect())
				{
					$mysqlObject = $pzMysqlObject->returnMysqliObj();
					$pzMysqlObject->returnMysqliObj()->query("INSERT INTO pz_debugger (mysql_queries, mysql_read_queries, mysql_write_queries, mc_writes, mc_deletes, mc_reads, mcd_writes, mcd_deletes, mcd_reads, apc_writes, apc_deletes, apc_reads, shm_writes, shm_deletes, shm_reads, includes, mysql_connections, mc_connections, mcd_connections, mysql_disconnections, mc_disconnections, mcd_disconnections, mysql_queries_executed, start_memory_usage, start_memory_real_usage, start_peak_memory_usage, start_peak_memory_real_usage, end_memory_usage, end_memory_real_usage, end_peak_memory_usage, end_peak_memory_real_usage, exec_time, exec_start_time, exec_end_time) VALUES (".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mysql_queries']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mysql_read_queries']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mysql_write_queries']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mc_writes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mc_deletes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mc_reads']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mcd_writes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mcd_deletes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mcd_reads']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['apc_writes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['apc_deletes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['apc_reads']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['shm_writes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['shm_deletes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['shm_reads']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['includes']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mysql_connections']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mc_connections']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mcd_connections']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mysql_disconnections']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mc_disconnections']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['mcd_disconnections']).", '".$_pzcoreObject->sanitizeExternal($mysqlObject, serialize($this->_statistics['mysql_queries_executed']), false)."', ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['start_memory_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['start_memory_real_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['start_peak_memory_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['start_peak_memory_real_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['end_memory_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['end_memory_real_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['end_peak_memory_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['end_peak_memory_real_usage']).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['exec_time'], false).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['exec_start_time'], false).", ".$_pzcoreObject->sanitizeExternal($mysqlObject, $this->_statistics['exec_end_time'], false).")");
				}
			}

			if($this->_displayBar === true)
			{
				echo $this->_buildBar();
			}
		}

		private function _buildBar()
		{
			$html = '<div style="position:fixed;bottom:0;width:90%;margin:0 5%;font-size: 12px;font-family: Arial;background-color: #E9E9E9;border:1px solid #6A5C5A;-webkit-border-radius: 5px 5px 0px 0px;border-radius: 5px 5px 0px 0px;height:40px;">';

			$html .= '<div style="float:left;width:25%;font-size:15px;"><div style="padding:10px 10px 0;"> <strong>Pz_Debugger</strong></div></div>';

			$statisticshtml = '';
			$statisticshtml .= 'Queries: '.$this->_statistics['mysql_queries'].'\n\n';
			$statisticshtml .= 'Read Queries: '.$this->_statistics['mysql_read_queries'].'\n\n';
			$statisticshtml .= 'Write Queries: '.$this->_statistics['mysql_write_queries'].'\n\n\n\n';
			$statisticshtml .= 'MC Writes: '.$this->_statistics['mc_writes'].'\n\n';
			$statisticshtml .= 'MC Deletes: '.$this->_statistics['mc_deletes'].'\n\n';
			$statisticshtml .= 'MC Reads: '.$this->_statistics['mc_reads'].'\n\n\n\n';
			$statisticshtml .= 'MCD Writes: '.$this->_statistics['mcd_writes'].'\n\n';
			$statisticshtml .= 'MCD Deletes: '.$this->_statistics['mcd_deletes'].'\n\n';
			$statisticshtml .= 'MCD Reads: '.$this->_statistics['mcd_reads'].'\n\n\n\n';
			$statisticshtml .= 'APC Writes: '.$this->_statistics['apc_writes'].'\n\n';
			$statisticshtml .= 'APC Deletes: '.$this->_statistics['apc_deletes'].'\n\n';
			$statisticshtml .= 'APC Reads: '.$this->_statistics['apc_reads'].'\n\n\n\n';
			$statisticshtml .= 'SHM Writes: '.$this->_statistics['shm_writes'].'\n\n';
			$statisticshtml .= 'SHM Deletes: '.$this->_statistics['shm_deletes'].'\n\n';
			$statisticshtml .= 'SHM Reads: '.$this->_statistics['shm_reads'].'\n\n\n\n';
			$statisticshtml .= 'MySql Conn: '.$this->_statistics['mysql_connections'].'\n\n';
			$statisticshtml .= 'MySql Disconn: '.$this->_statistics['mysql_disconnections'].'\n\n\n\n';
			$statisticshtml .= 'MC Conn: '.$this->_statistics['mc_connections'].'\n\n';
			$statisticshtml .= 'MC Disconn: '.$this->_statistics['mc_disconnections'].'\n\n';
			$statisticshtml .= 'MCD Conn: '.$this->_statistics['mcd_connections'].'\n\n';
			$statisticshtml .= 'MCD Disconn: '.$this->_statistics['mcd_disconnections'].'\n\n';

			$querieshtml = '';
			if(count($this->_statistics['mysql_queries_executed']) > 0)
			{
				foreach($this->_statistics['mysql_queries_executed'] as $query)
				{
					$querieshtml .= $query.'\n\n';
				}
			}
			else
			{
				$querieshtml = 'No queries logged.';
			}

			$executiondata = '';
			$executiondata .= 'Memory Usage At Start (KB): '.bcdiv($this->_statistics['start_memory_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Usage At Start (Real)(KB): '.bcdiv($this->_statistics['start_memory_real_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Peak Usage At Start (KB): '.bcdiv($this->_statistics['start_peak_memory_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Peak Usage At Start (Real)(KB): '.bcdiv($this->_statistics['start_peak_memory_real_usage'],1024,5).'\n\n';
			$executiondata .= 'Memory Usage At End (KB): '.bcdiv($this->_statistics['end_memory_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Usage At End (Real)(KB): '.bcdiv($this->_statistics['end_memory_real_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Peak Usage At End (KB): '.bcdiv($this->_statistics['end_peak_memory_usage'],1024,2).'\n\n';
			$executiondata .= 'Memory Peak Usage At End (Real)(KB): '.bcdiv($this->_statistics['end_peak_memory_real_usage'],1024,2).'\n\n';
			$executiondata .= 'Script Memory Usage(KB): '.bcdiv($this->_statistics['script_memory_usage'],1024,2).'\n\n';
			$executiondata .= 'Script Peak Memory Usage(KB): '.bcdiv($this->_statistics['script_peak_memory_usage'],1024,2).'\n\n\n\n';
			$executiondata .= 'Script Execution Time: '.$this->_statistics['exec_time'].'\n\n';
			$executiondata .= 'Script Start Execution Time: '.$this->_statistics['exec_start_time'].'\n\n';
			$executiondata .= 'Script End Execution Time: '.$this->_statistics['exec_end_time'].'\n\n';


			$html .= '<div style="float:left;width:25%;"><div style="padding:10px 10px 0;"><a href="javascript:void(0)" onclick="alert(\''.$statisticshtml.'\');">View Statistics</a></div></div>';
			$html .= '<div style="float:left;width:25%;"><div style="padding:10px 10px 0;"><a href="javascript:void(0)" onclick="alert(\''.$querieshtml.'\');">View Queries</a></div></div>';
			$html .= '<div style="float:left;width:25%;"><div style="padding:10px 10px 0;"><a href="javascript:void(0)" onclick="alert(\''.$executiondata.'\');">View Execution Data</a></div></div>';

			return $html.'<div style="clear:both"><!-- --></div></div>';
		}
	}
