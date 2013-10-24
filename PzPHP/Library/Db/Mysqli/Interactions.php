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
 * @package Pz Library
 */
/**
 * The interaction class for communicating with mysql using mysqli.
 */
class PzPHP_Library_Db_Mysqli_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
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
			return false;
		}
	}

	/**
	 * Expects to handle write related query.
	 *
	 * @access public
	 * @param string $query
	 * @param int $serverId
	 * @return bool|mysqli_result
	 */
	public function write($query, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->mysqliConnect($serverId))
				{
					return false;
				}
			}

			$firstIntervalDelay = $this->pzphp()->db()->getSetting('db_write_retry_first_interval_delay');
			$secondIntervalDelay = $this->pzphp()->db()->getSetting('db_write_retry_second_interval_delay');

			$firstIntervalRetries = $this->pzphp()->db()->getSetting('db_write_retry_first_interval_retries');
			$secondIntervalRetries = $this->pzphp()->db()->getSetting('db_write_retry_second_interval_retries');

			$retryCodes = array(
				1213, //Deadlock found when trying to get lock
				1205 //Lock wait timeout exceeded
			);

			//Initialize
			$retryCount = 0;

			//Main loop
			do
			{
				//Initialize 'flag_retry' indicating whether or not we need to retry this transaction
				$retryFlag = 0;

				// Write query (UPDATE, INSERT)
				$result = $this->pzphp()->db()->mysqliActiveObject($serverId)->returnMysqliObj()->query($query);
				$mysqlErrno = $this->pzphp()->db()->mysqliActiveObject($serverId)->returnMysqliObj()->errno;
				$mysqlError = $this->pzphp()->db()->mysqliActiveObject($serverId)->returnMysqliObj()->error;

				$this->pzphp()->db()->debugger('mysqlWritesInc');
				$this->pzphp()->db()->debugger('mysqlLogQuery', array($query));

				// If failed,
				if(!$result)
				{
					// Determine if we need to retry this transaction -
					// If duplicate PRIMARY key error,
					// or one of the errors in 'arr_need_to_retry_error_codes'
					// then we need to retry
					if($mysqlErrno == 1062 && strpos($mysqlError,"for key 'PRIMARY'") !== false)
					{
						$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Duplicate Primary Key error for query: "'.$query.'".');
					}

					$retryFlag = (in_array($mysqlErrno, $retryCodes));

					if(!empty($retryFlag))
					{
						$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Deadlock detected for query: "'.$query.'".');
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
						$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Reducing retry interval for deadlock detection on query: "'.$query.'".');
					}

					usleep($firstIntervalDelay);
				}
				elseif($retryCount > $firstIntervalRetries && $retryCount <= $secondIntervalRetries)
				{
					usleep($secondIntervalDelay);
				}
				else
				{
					$result = false;
					$retryCount--;

					$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Finally gave up on query: "'.$query.'".');

					break;
				}
			}
			while(true);

			// If update query failed, log
			if(!$result)
			{
				$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Query failed: "'.$query.'".');
			}

			if($retryCount > 0 && $retryCount < $secondIntervalRetries)
			{
				$this->pzphp()->db()->addToLog($this->pzphp()->db()->getLoggerObject('mysql'), 'Query finally succeeded: "'.$query.'".');
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
			return false;
		}
	}

	/**
	 * Returns the affected rows of the last delete/insert/update/etc... query.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function affectedRows($id = -1)
	{
		$id = $this->pzphp()->db()->decideActiveMySqliId($id);

		return ($this->pzphp()->db()->mysqliActiveObject($id)?$this->pzphp()->db()->mysqliActiveObject($id)->affectedRows():0);
	}

	/**
	 * Returns the last insert id of the last insert query.
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function insertId($id = -1)
	{
		$id = $this->pzphp()->db()->decideActiveMySqliId($id);

		return ($this->pzphp()->db()->mysqliActiveObject($id)?$this->pzphp()->db()->mysqliActiveObject($id)->insertId():0);
	}

	/**
	 * Select a new database for the current connection.
	 *
	 * @access public
	 * @param string $dbName
	 * @param int $id
	 * @return bool
	 */
	public function selectDatabase($dbName, $id = -1)
	{
		$id = $this->pzphp()->db()->decideActiveMySqliId($id);

		return ($this->pzphp()->db()->mysqliActiveObject($id)?$this->pzphp()->db()->mysqliActiveObject($id)->selectDatabase($dbName):false);
	}

	/**
	 * Change the user for the current connection.
	 *
	 * @access public
	 * @param string $user
	 * @param string $password
	 * @param null|string $dbName
	 * @param int $id
	 * @return bool
	 */
	public function changeUser($user, $password, $dbName = NULL, $id = -1)
	{
		$id = $this->pzphp()->db()->decideActiveMySqliId($id);

		return ($this->pzphp()->db()->mysqliActiveObject($id)?$this->pzphp()->db()->mysqliActiveObject($id)->changeUser($user, $password, $dbName):false);
	}

	/**
	 * Sanitize a value that will be injected into a query string.
	 *
	 * @access public
	 * @param mixed $value
	 * @param bool $mustBeNumeric
	 * @param int  $decimalPlaces
	 * @param int  $cleanall
	 * @param int $id
	 * @return mixed
	 */
	public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = 0, $id = -1)
	{
		return $this->pzphp()->db()->pzSecurity()->cleanQuery(
			$this->pzphp()->db()->mysqliActiveObject($this->pzphp()->db()->decideActiveMySqliId($id))->returnMysqliObj(),
			$value,
			$mustBeNumeric,
			$decimalPlaces,
			$cleanall
		);
	}
}
