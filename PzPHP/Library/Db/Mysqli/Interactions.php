<?php
class PzPHP_Library_Db_Mysqli_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result|PDOStatement
	 */
	public function read($query, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			$query = trim($query);
			$result = $this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->query($query);

			if(!$result)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Query failed: "'.$query.' | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Excpetion during query: "'.$query.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result|PDOStatement
	 */
	public function write($query, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			$firstIntervalDelay = PzPHP_Config::get('SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_DELAY_SECONDS');
			$secondIntervalDelay = PzPHP_Config::get('SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_DELAY_SECONDS');

			$firstIntervalRetries = PzPHP_Config::get('SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_RETRIES');
			$secondIntervalRetries = PzPHP_Config::get('SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_RETRIES');

			$retryCodes = array(
				1213, //Deadlock found when trying to get lock
				1205 //Lock wait timeout exceeded
			);

			//Initialize
			$retryCount = 0;

			$query = trim($query);

			//Main loop
			do
			{
				//Initialize 'flag_retry' indicating whether or not we need to retry this transaction
				$retryFlag = 0;

				// Write query (UPDATE, INSERT)
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->query($query);

				// If failed,
				if(!$result)
				{
					$mysqlErrno = $this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno;
					$mysqlError = $this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error;

					// Determine if we need to retry this transaction -
					// If duplicate PRIMARY key error,
					// or one of the errors in 'arr_need_to_retry_error_codes'
					// then we need to retry
					if($mysqlErrno == 1062 && strpos($mysqlError,"for key 'PRIMARY'") !== false)
					{
						$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Duplicate Primary Key error for query: "'.$query.'". | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
					}

					$retryFlag = (in_array($mysqlErrno, $retryCodes));

					if(!empty($retryFlag))
					{
						$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Deadlock detected for query: "'.$query.'" | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
					}
				}

				// If successful or failed but no need to retry
				if($result || empty($retryFlag))
				{
					// We're done
					break;
				}

				$retryCount++;

				if($retryCount <= $firstIntervalRetries)
				{
					if($retryCount === $firstIntervalRetries)
					{
						$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Reducing retry interval for deadlock detection on query: "'.$query.'". | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
					}

					usleep($firstIntervalDelay*1000000);
				}
				elseif($retryCount > $firstIntervalRetries && $retryCount <= $secondIntervalRetries)
				{
					usleep($secondIntervalDelay*1000000);
				}
				else
				{
					$result = false;
					$retryCount--;

					$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Finally gave up on query: "'.$query.'". | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');

					break;
				}
			}
			while(true);

			// If update query failed, log
			if(!$result)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Query failed: "'.$query.'". | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
			}

			if($retryCount > 0 && $retryCount < $secondIntervalRetries)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Query finally succeeded: "'.$query.'". | Error: "#'.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->errno.' / '.$this->pzphp()->db()->getActiveServer($serverId)->getDBObject()->error.'"');
			}

			// Return result
			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Excpetion during query: "'.$query.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * @param $serverId
	 * @return int|mixed
	 */
	public function affectedRows($serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		$this->_connect($serverId);

		return ($this->pzphp()->db()->getActiveServer($serverId)?$this->pzphp()->db()->getActiveServer($serverId)->affectedRows():0);
	}

	/**
	 * @param $serverId
	 * @return int|mixed
	 */
	public function insertId($serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		$this->_connect($serverId);

		return ($this->pzphp()->db()->getActiveServer($serverId)?$this->pzphp()->db()->getActiveServer($serverId)->insertId():0);
	}

	/**
	 * @param $dbName
	 * @param $serverId
	 * @return bool
	 */
	public function selectDatabase($dbName, $serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		$this->_connect($serverId);

		return ($this->pzphp()->db()->getActiveServer($serverId)?$this->pzphp()->db()->getActiveServer($serverId)->selectDatabase($dbName):false);
	}

	/**
	 * @param $user
	 * @param $password
	 * @param null $dbName
	 * @param $serverId
	 * @return bool
	 */
	public function changeUser($user, $password, $dbName = NULL, $serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		$this->_connect($serverId);

		return ($this->pzphp()->db()->getActiveServer($serverId)?$this->pzphp()->db()->getActiveServer($serverId)->changeUser($user, $password, $dbName):false);
	}

	/**
	 * @param $value
	 * @param bool $mustBeNumeric
	 * @param int $decimalPlaces
	 * @param int $cleanall
	 * @param $serverId
	 * @return array|float|int|mixed|string
	 */
	public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId = -1)
	{
		$this->_connect($serverId);

		return PzPHP_Library_Security_Cleanse::cleanQuery($this->pzphp()->db()->getActiveServer($this->pzphp()->db()->getActiveServerId($serverId))->getDBObject(),$value,$mustBeNumeric, $decimalPlaces, $cleanall);
	}

	/**
	 * @param $serverId
	 * @return bool
	 */
	protected function _connect($serverId)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Excpetion during sanitization | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}
