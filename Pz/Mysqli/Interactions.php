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
	 * @package Pz_Mysqli_Interactions
	 */
	class Pz_Mysqli_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * @param $query
		 * @param $id
		 *
		 * @return bool|mysqli_result
		 */
		public function read($query, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			if($this->pzCore()->mysqliActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->mysqliActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->mysqliConnect($id) === false)
					{
						return false;
					}
				}

				$result = $this->pzCore()->mysqliActiveObject($id)->returnMysqliObj()->query($query);

				if(!$result && strtoupper(substr($query,0,6)) === 'SELECT')
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Query failed: "'.$query.'".');
				}

				$this->pzCore()->debugger('mysqlReadsInc');
				$this->pzCore()->debugger('mysqlLogQuery', array($query));

				if(empty($result))
				{
					return false;
				}
				else
				{
					return $result;
				}
			}
		}

		/**
		 * @param $query
		 * @param $id
		 *
		 * @return bool|int|mysqli_result
		 */
		public function write($query, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			if($this->pzCore()->mysqliActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->mysqliActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->mysqliConnect($id) === false)
					{
						return false;
					}
				}

				$firstIntervalDelay = $this->pzCore()->getSetting('mysql_write_retry_first_interval_delay');
				$secondIntervalDelay = $this->pzCore()->getSetting('mysql_write_retry_second_interval_delay');

				$firstIntervalRetries = $this->pzCore()->getSetting('mysql_write_retry_first_interval_retries');
				$secondIntervalRetries = $this->pzCore()->getSetting('mysql_write_retry_second_interval_retries');

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
					$result = $this->pzCore()->mysqliActiveObject($id)->returnMysqliObj()->query($query);
					$mysqlErrno = $this->pzCore()->mysqliActiveObject($id)->returnMysqliObj()->errno;
					$mysqlError = $this->pzCore()->mysqliActiveObject($id)->returnMysqliObj()->error;

					$this->pzCore()->debugger('mysqlWritesInc');
					$this->pzCore()->debugger('mysqlLogQuery', array($query));

					// If failed,
					if(!$result)
					{
						// Determine if we need to retry this transaction -
						// If duplicate PRIMARY key error,
						// or one of the errors in 'arr_need_to_retry_error_codes'
						// then we need to retry
						if($mysqlErrno == 1062 && strpos($mysqlError,"for key 'PRIMARY'") !== false)
						{
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Duplicate Primary Key error for query: "'.$query.'".');
						}

						$retryFlag = (in_array($mysqlErrno, $retryCodes));

						if(!empty($retryFlag))
						{
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Deadlock detected for query: "'.$query.'".');
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
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Reducing retry interval for deadlock detection on query: "'.$query.'".');
						}

						usleep($firstIntervalDelay);
					}
					elseif($retryCount > $firstIntervalRetries && $retryCount <= $secondIntervalRetries)
					{
						usleep($secondIntervalDelay);
					}
					else
					{
						$result = 0;
						$retryCount--;

						$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Finally gave up on query: "'.$query.'".');

						break;
					}
				}
				while(true);

				// If update query failed, log
				if(!$result)
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Query failed: "'.$query.'".');
				}

				if($retryCount > 0 && $retryCount < $secondIntervalRetries)
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysqli'), 'Query finally succeeded: "'.$query.'".');
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
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function mysqliAffectedRows($id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			return ($this->pzCore()->mysqliActiveObject($id)?$this->pzCore()->mysqliActiveObject($id)->affectedRows():0);
		}

		/**
		 * @param $id
		 *
		 * @return int
		 */
		public function mysqliInsertId($id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			return ($this->pzCore()->mysqliActiveObject($id)?$this->pzCore()->mysqliActiveObject($id)->insertId():0);
		}

		/**
		 * @param $dbName
		 * @param $id
		 *
		 * @return bool
		 */
		public function mysqliSelectDatabase($dbName, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			return ($this->pzCore()->mysqliActiveObject($id)?$this->pzCore()->mysqliActiveObject($id)->selectDatabase($dbName):false);
		}

		/**
		 * @param      $user
		 * @param      $password
		 * @param null $dbName
		 * @param      $id
		 *
		 * @return bool
		 */
		public function mysqliChangeUser($user, $password, $dbName = NULL, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqliId($id);

			return ($this->pzCore()->mysqliActiveObject($id)?$this->pzCore()->mysqliActiveObject($id)->changeUser($user, $password, $dbName):false);
		}

		/**
		 * @param      $value
		 * @param bool $mustBeNumeric
		 * @param int  $decimalPlaces
		 * @param int  $cleanall
		 * @param      $id
		 *
		 * @return mixed
		 */
		public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $id = -1)
		{
			return $this->pzSecurity()->cleanQuery(
				$this->pzCore()->mysqliActiveObject($this->pzCore()->decideActiveMySqliId($id)),
				$value,
				$mustBeNumeric,
				$decimalPlaces,
				$cleanall
			);
		}
	}
