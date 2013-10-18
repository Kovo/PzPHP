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
	 * @return $this
	 */
	public function warmup()
	{
		ini_set('error_log', PzPHP_Config::get('LOGS_DIR').PzPHP_Config::get('SETTING_PHP_ERROR_LOG_FILE_NAME').'-'.date('Y-m-d').'.log');
		ini_set('error_reporting', E_ALL | E_NOTICE);
		ini_set('display_errors', (PzPHP_Config::get('SETTING_DEBUG_MODE')?1:0));

		if(PzPHP_Config::get('SETTING_DELETE_LOG_FILES_AFTER_DAYS') > 0 && count($this->_logs) > 0)
		{
			foreach($this->_logs as $logName => $location)
			{
				$path = PzPHP_Helper_IO::extractPath($location);

				if(!PzPHP_Helper_IO::isValidDir($path))
				{
					continue;
				}

				$files = file($path);

				if(empty($files))
				{
					continue;
				}

				$rootLogNameLength = strlen($logName);
				foreach($files as $fileName)
				{
					if($fileName === '.' || $fileName === '..' || substr($fileName,0,$rootLogNameLength) !== $logName)
					{
						continue;
					}

					if((strtotime(array_pop(explode('-', $fileName)))/86400) >= PzPHP_Config::get('SETTING_DELETE_LOG_FILES_AFTER_DAYS'))
					{
						PzPHP_Helper_IO::removeFileFolderEnforce($path.$fileName);
					}
				}
			}
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
		$this->_logs[$logName] = ($logLocation===''?PzPHP_Config::get('LOGS_DIR').$logName.'-'.date('Y-m-d').'.log':$logLocation.$logName.'-'.date('Y-m-d').'.log');

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

				if(count($file) > 0)
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
