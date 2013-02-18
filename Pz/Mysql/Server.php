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
	 * Class is used for storing mysql connection information using mysql.
	 */
	final class Pz_Mysql_Server
	{
		/**
		 * Status constant when the connection is established.
		 *
		 * @var int
		 */
		const CONNECTED = 1;

		/**
		 * Status constant when the connection is disconnected.
		 *
		 * @var int
		 */
		const DISCONNECTED = 2;

		/**
		 * Status constant when the connection is connecting.
		 *
		 * @var int
		 */
		const CONNECTING = 3;

		/**
		 * The username that will access the mysql server.
		 *
		 * @access private
		 * @var string
		 */
		private $_user = '';

		/**
		 * The password that will access the mysql server.
		 *
		 * @access private
		 * @var string
		 */
		private $_password = '';

		/**
		 * The host that the mysql server is on.
		 *
		 * @access private
		 * @var string
		 */
		private $_host = '';

		/**
		 * The default database to connect to.
		 *
		 * @access private
		 * @var string
		 */
		private $_dbName = '';

		/**
		 * The port that the mysql server is on.
		 *
		 * @access private
		 * @var int
		 */
		private $_port = 0;

		/**
		 * The amount of times Pz should try to reconnect to the mysql server.
		 *
		 * @access private
		 * @var int
		 */
		private $_connectRetryAttempts = 0;

		/**
		 * The amount of seconds to wait between connection retry attempts.
		 *
		 * @access private
		 * @var int
		 */
		private $_connectRetryDelay = 0;

		/**
		 * The current connection status.
		 *
		 * @access private
		 * @var int
		 */
		private $_status = self::DISCONNECTED;

		/**
		 * The final mysql object.
		 *
		 * @access private
		 * @var null|resource
		 */
		private $_mysql_res = null;

		/**
		 * The constructor handles setting the mysql server credentials.
		 *
		 * @access private
		 * @param string $dbUser
		 * @param string $dbPassword
		 * @param string $dbName
		 * @param string $dbHost
		 * @param int $dbPort
		 * @param int $connectRetries
		 * @param int $connectRetryWait
		 */
		function __construct($dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $connectRetries, $connectRetryWait)
		{
			$this->_user = $dbUser;
			$this->_password = $dbPassword;
			$this->_dbName = $dbName;
			$this->_host = $dbHost;
			$this->_port = $dbPort;
			$this->_connectRetryAttempts = $connectRetries;
			$this->_connectRetryDelay = $connectRetryWait;
		}

		/**
		 * Attempts to connect to the mysql server.
		 *
		 * @access public
		 * @return bool
		 */
		public function connect()
		{
			if($this->isConnected() === false)
			{
				$this->_status = self::CONNECTING;

				$this->_mysql_res =  mysql_connect($this->_host.':'.$this->_port, $this->_user, $this->_password);

				if(!$this->_mysql_res)
				{
					for($x=0;$x<$this->_connectRetryAttempts;$x++)
					{
						sleep($this->_connectRetryDelay);

						$this->_mysql_res =  mysql_connect($this->_host.':'.$this->_port, $this->_user, $this->_password);

						if(!$this->_mysql_res)
						{
							continue;
						}
						else
						{
							$this->selectDatabase($this->_dbName);

							$this->_status = self::CONNECTED;

							break;
						}
					}

					if($this->_status === self::CONNECTING)
					{
						$this->_status = self::DISCONNECTED;

						return false;
					}
					else
					{
						return true;
					}
				}
				else
				{
					$this->selectDatabase($this->_dbName);

					$this->_status = self::CONNECTED;

					return true;
				}
			}
			else
			{
				return true;
			}
		}

		/**
		 * Disconnects from the mysql server.
		 *
		 * @access public
		 */
		public function disconnect()
		{
			if($this->isConnected() === true && is_resource($this->_mysql_res))
			{
				$this->_mysql_res->close();

				$this->_mysql_res = null;

				$this->_status = self::DISCONNECTED;
			}
		}

		/**
		 * Returns true or false if the mysql server connection went through and is active.
		 *
		 * @access public
		 * @return bool
		 */
		public function isConnected()
		{
			return ($this->_status===self::CONNECTED&&is_resource($this->_mysql_res)?true:false);
		}

		/**
		 * Returns the active mysql resource.
		 *
		 * @access public
		 * @return resource|null
		 */
		public function returnMysqlRes()
		{
			return $this->_mysql_res;
		}

		/**
		 * Returns the last insert id.
		 *
		 * @access public
		 * @return mixed
		 */
		public function insertId()
		{
			return mysql_insert_id($this->_mysql_res);
		}

		/**
		 * Returns the affected rows from the last query.
		 *
		 * @access public
		 * @return mixed
		 */
		public function affectedRows()
		{
			return mysql_affected_rows($this->_mysql_res);
		}

		/**
		 * Select a new database;
		 *
		 * @access public
		 * @param string $dbName
		 * @return bool
		 */
		public function selectDatabase($dbName)
		{
			if(mysql_select_db($dbName, $this->_mysql_res))
			{
				$this->_dbName = $dbName;

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Change the current user.
		 *
		 * Since the mysql module does not support this directly, Pz will disconnect the current connection, and reconnect using the new username and password.
		 *
		 * @access public
		 * @param string $user
		 * @param string $password
		 * @param null|string $dbName
		 * @return bool
		 */
		public function changeUser($user, $password, $dbName = null)
		{
			$this->disconnect();

			$this->_user = $user;
			$this->_password = $password;

			if($this->connect())
			{
				if($dbName !== null)
				{
					$this->selectDatabase($dbName);
				}
				else
				{
					$this->selectDatabase($this->_dbName);
				}

				return true;
			}
			else
			{
				return false;
			}
		}
	}
