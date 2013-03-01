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
	 * Class is used for storing mysql connection information using mysqli.
	 */
	final class Pz_Mysqli_Server
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
		 * @access protected
		 * @var string
		 */
		protected $_user = '';

		/**
		 * The password that will access the mysql server.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_password = '';

		/**
		 * The host that the mysql server is on.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_host = '';

		/**
		 * The default database to connect to.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_dbName = '';

		/**
		 * The port that the mysql server is on.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_port = 0;

		/**
		 * The amount of times Pz should try to reconnect to the mysql server.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_connectRetryAttempts = 0;

		/**
		 * The amount of seconds to wait between connection retry attempts.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_connectRetryDelay = 0;

		/**
		 * The current connection status.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_status = self::DISCONNECTED;

		/**
		 * The final mysqli object.
		 *
		 * @access protected
		 * @var null|mysqli
		 */
		protected $_mysqli_obj = null;

		/**
		 * The constructor handles setting the mysql server credentials.
		 *
		 * @access protected
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

				$this->_mysqli_obj =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

				if(mysqli_connect_error())
				{
					for($x=0;$x<$this->_connectRetryAttempts;$x++)
					{
						sleep($this->_connectRetryDelay);

						$this->_mysqli_obj =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

						if(mysqli_connect_error())
						{
							continue;
						}
						else
						{
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
			if($this->isConnected() === true && is_object($this->_mysqli_obj))
			{
				$this->_mysqli_obj->close();

				$this->_mysqli_obj = null;

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
			return ($this->_status===self::CONNECTED&&is_object($this->_mysqli_obj)?true:false);
		}

		/**
		 * Returns the active mysqli object.
		 *
		 * @access public
		 * @return mysqli|null
		 */
		public function returnMysqliObj()
		{
			return $this->_mysqli_obj;
		}

		/**
		 * Returns the last insert id.
		 *
		 * @access public
		 * @return mixed
		 */
		public function insertId()
		{
			return $this->_mysqli_obj->insert_id;
		}

		/**
		 * Returns the affected rows from the last query.
		 *
		 * @access public
		 * @return mixed
		 */
		public function affectedRows()
		{
			return $this->_mysqli_obj->affected_rows;
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
			if($this->_mysqli_obj->select_db($dbName))
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
		 * @access public
		 * @param string $user
		 * @param string $password
		 * @param null|string $dbName
		 * @return bool
		 */
		public function changeUser($user, $password, $dbName = null)
		{
			if($this->_mysqli_obj->change_user($user, $password, $dbName))
			{
				$this->_user = $user;
				$this->_password = $password;

				if($dbName !== null)
				{
					$this->_dbName = $dbName;
				}

				return true;
			}
			else
			{
				return false;
			}
		}
	}
