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
	 * @package Pz_Logger
	 */
	class Pz_Logger
	{
		/**
		 * @var string
		 */
		private $_logsDir = '';

		/**
		 * @var string
		 */
		private $_logFileName = '';

		/**
		 * @var string
		 */
		private $_fullLogFileName = '';

		/**
		 * @var bool
		 */
		private $_autoRotate = true;

		/**
		 * @var int
		 */
		private $_deleteLogsAfterXDays = 7;

		/**
		 * @param string $logsDir
		 * @param string $logFileName
		 * @param bool   $autoRotate
		 * @param int    $deleteLogsAfterXDays
		 */
		function __construct($logsDir = '', $logFileName = '', $autoRotate = true, $deleteLogsAfterXDays = 7)
		{
			$this->_logsDir = ($logsDir === ''?PZ_LOGS_DIR:$logsDir);
			$this->_logFileName = ($logFileName === ''?'logger':$logFileName);
			$this->_autoRotate = $autoRotate;
			$this->_deleteLogsAfterXDays = $deleteLogsAfterXDays;

			$this->_warmupLogs();
		}

		/*
		 * Makes sure the log dir exists, and rotates if necessary
		 */
		private function _warmupLogs()
		{
			if(!file_exists($this->_logsDir) || !is_dir($this->_logsDir))
			{
				mkdir($this->_logsDir, 0775, true);
			}

			if($this->_autoRotate === true)
			{
				$this->_fullLogFileName = $this->_logsDir.$this->_logFileName.'-'.date('Ymd').'.log';
			}
			else
			{
				$this->_fullLogFileName = $this->_logsDir.$this->_logFileName.'.log';
			}

			if(!file_exists($this->_fullLogFileName))
			{
				file_put_contents($this->_fullLogFileName, '');
			}

			if($this->_deleteLogsAfterXDays > 0)
			{
				if($directory = opendir($this->_logsDir))
				{
					while (($fileName = readdir($directory)) !== false)
					{
						if($fileName !== '.' && $fileName !== '..')
						{
							$explodeName = explode('-', $fileName);
							$extractDate = substr(array_pop($explodeName), 0, -4);

							$year = substr($extractDate, 0, 4);
							$month = substr($extractDate, 4, 2);
							$day = substr($extractDate, 6, 2);

							$secondsDifference = time() - strtotime($year.'-'.$month.'-'.$day.' 00:00:00');

							if($secondsDifference >= ($this->_deleteLogsAfterXDays*86400))
							{
								unlink($this->_logsDir.$fileName);
							}
						}
					}

					closedir($directory);
				}
			}
		}

		/**
		 * @param $lineStr
		 *
		 * @return int
		 */
		public function addToLog($lineStr)
		{
			return file_put_contents($this->_fullLogFileName, date("Y-m-d H:i:s")." | ".$lineStr."\r\n", FILE_APPEND);
		}

		/**
		 * @return bool
		 */
		public function clearLog()
		{
			$unlinkResult = unlink($this->_fullLogFileName);
			$createResult = file_put_contents($this->_fullLogFileName, '');

			return ($unlinkResult&&$createResult);
		}
	}
