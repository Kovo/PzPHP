<?php
/**
 * Website: http://www.pzphp.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 *
 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
 * @package PzPHP
 */
/**
 * The security class gives you methods that allow you to create one-way, or two-way encryptions, among other things.
 */
class PzPHP_Module_Log extends PzPHP_Wrapper
{
	/**
	 * @var array
	 */
	protected $_logs = array();

	/**
	 * @var string
	 */
	const REGEX_LOG_PATTERN = "#(?:.*)-([0-9]{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1]))\\.log#";

	/**
	 * @return $this
	 */
	public function warmup()
	{
		if(PzPHP_Config::get('SETTING_LOG_PHP_ERRORS'))
		{
			if(!PzPHP_Helper_IO::isValidDir(PzPHP_Config::get('LOGS_DIR')))
			{
				PzPHP_Helper_IO::createDir(PzPHP_Config::get('LOGS_DIR'), 0750);
			}

			ini_set('error_log', PzPHP_Config::get('LOGS_DIR').PzPHP_Config::get('SETTING_PHP_ERROR_LOG_FILE_NAME').'-'.date('Y-m-d').'.log');
			ini_set('error_reporting', E_ALL | E_NOTICE);
			ini_set('display_errors', (PzPHP_Config::get('SETTING_PHP_DISPLAY_ERRORS')?1:0));
		}

		return $this;
	}

	/**
	 * @param $logName
	 * @param string $logLocation
	 * @return $this
	 */
	public function registerLog($logName, $logLocation = '')
	{
		if(!isset($this->_logs[$logName]))
		{
			$this->_logs[$logName] = ($logLocation===''?PzPHP_Config::get('LOGS_DIR').$logName.'-'.date('Y-m-d').'.log':$logLocation.$logName.'-'.date('Y-m-d').'.log');

			if(PzPHP_Config::get('SETTING_LOG_FILE_AUTO_ROTATE') === true)
			{
				$this->rotateLog($logName);
			}
		}

		return $this;
	}

	/**
	 * @param $logName
	 * @return $this
	 */
	public function rotateLog($logName)
	{
		if(isset($this->_logs[$logName]))
		{
			$path = PzPHP_Helper_IO::extractPath($this->_logs[$logName]);

			if(PzPHP_Helper_IO::isValidDir($path))
			{
				$files = scandir($path);

				if(!empty($files))
				{
					foreach($files as $fileName)
					{
						if(PzPHP_Helper_IO::isValidDir($path.$fileName))
						{
							continue;
						}

						if(!preg_match(self::REGEX_LOG_PATTERN, $fileName, $matches))
						{
							continue;
						}

						if(isset($matches[1]))
						{
							$fileDate		= new DateTime($matches[1]);
							$currentDate	= new DateTime(date('Y-m-d'));

							$interval		= $fileDate->diff($currentDate);

							if($interval->days >= PzPHP_Config::get('SETTING_DELETE_LOG_FILES_AFTER_DAYS'))
							{
								PzPHP_Helper_IO::removeFileFolderEnforce($path.$fileName);
							}
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * @param $logName
	 * @param $value
	 * @return $this
	 */
	public function add($logName, $value)
	{
		if(isset($this->_logs[$logName]))
		{
			file_put_contents($this->_logs[$logName], date('Y-m-d H:i:s').' | '.$value."\r\n", FILE_APPEND);
		}

		return $this;
	}

	/**
	 * @param $logName
	 * @param string $beforeDate
	 * @return $this
	 */
	public function clear($logName, $beforeDate = '')
	{
		if(isset($this->_logs[$logName]))
		{
			if($beforeDate === '')
			{
				file_put_contents($this->_logs[$logName], '');
			}
			else
			{
				$file = file($this->_logs[$logName]);

				if(!empty($file))
				{
					file_put_contents($this->_logs[$logName], '');

					foreach($file as $lineValue)
					{
						$explode = explode(' | ', $lineValue);

						if(strtotime($explode[0]) >= strtotime($beforeDate))
						{
							file_put_contents($this->_logs[$logName], $lineValue."\r\n", FILE_APPEND);
						}
					}
				}
			}
		}

		return $this;
	}
}