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
	 * The interaction class for communicating with mysql using mysql.
	 */
	class Pz_Mysql_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * Expects to handle read related queries.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|resource
		 */
		public function read($query, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqlId($id);

			if($this->pzCore()->mysqlActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->mysqlActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->mysqlConnect($id) === false)
					{
						return false;
					}
				}

				$result = mysql_query($query, $this->pzCore()->mysqlActiveObject($id)->returnMysqlRes());

				if(!$result && strtoupper(substr($query,0,6)) === 'SELECT')
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Query failed: "'.$query.'".');
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
		 * Expects to handle write related query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|resource
		 */
		public function write($query, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqlId($id);

			if($this->pzCore()->mysqlActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->mysqlActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->mysqlConnect($id) === false)
					{
						return false;
					}
				}

				$firstIntervalDelay = $this->pzCore()->getSetting('db_write_retry_first_interval_delay');
				$secondIntervalDelay = $this->pzCore()->getSetting('db_write_retry_second_interval_delay');

				$firstIntervalRetries = $this->pzCore()->getSetting('db_write_retry_first_interval_retries');
				$secondIntervalRetries = $this->pzCore()->getSetting('db_write_retry_second_interval_retries');

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
					$result = mysql_query($query, $this->pzCore()->mysqlActiveObject($id)->returnMysqlRes());
					$mysqlErrno = mysql_errno($this->pzCore()->mysqlActiveObject($id)->returnMysqlRes());
					$mysqlError = mysql_error($this->pzCore()->mysqlActiveObject($id)->returnMysqlRes());

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
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Duplicate Primary Key error for query: "'.$query.'".');
						}

						$retryFlag = (in_array($mysqlErrno, $retryCodes));

						if(!empty($retryFlag))
						{
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Deadlock detected for query: "'.$query.'".');
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
							$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Reducing retry interval for deadlock detection on query: "'.$query.'".');
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

						$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Finally gave up on query: "'.$query.'".');

						break;
					}
				}
				while(true);

				// If update query failed, log
				if(!$result)
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Query failed: "'.$query.'".');
				}

				if($retryCount > 0 && $retryCount < $secondIntervalRetries)
				{
					$this->pzCore()->addToLog($this->pzCore()->getLoggerObject('mysql'), 'Query finally succeeded: "'.$query.'".');
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
		 * Returns the affected rows of the last delete/insert/update/etc... query.
		 *
		 * @access public
		 * @param int $id
		 * @return int
		 */
		public function affectedRows($id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqlId($id);

			return ($this->pzCore()->mysqlActiveObject($id)?$this->pzCore()->mysqlActiveObject($id)->affectedRows():0);
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
			$id = $this->pzCore()->decideActiveMySqlId($id);

			return ($this->pzCore()->mysqlActiveObject($id)?$this->pzCore()->mysqlActiveObject($id)->insertId():0);
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
			$id = $this->pzCore()->decideActiveMySqlId($id);

			return ($this->pzCore()->mysqlActiveObject($id)?$this->pzCore()->mysqlActiveObject($id)->selectDatabase($dbName):false);
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
		public function changeUser($user, $password, $dbName = null, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMySqlId($id);

			return ($this->pzCore()->mysqlActiveObject($id)?$this->pzCore()->mysqlActiveObject($id)->changeUser($user, $password, $dbName):false);
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
		public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $id = -1)
		{
			return $this->pzCore()->pzSecurity()->cleanQuery(
				$this->pzCore()->mysqlActiveObject($id)->returnMysqlRes(),
				$value,
				$mustBeNumeric,
				$decimalPlaces,
				$cleanall
			);
		}
	}
