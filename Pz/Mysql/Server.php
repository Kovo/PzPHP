<?php
	/**
	 * Website: http://www.pzphp.com
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzMysqlServer
	 */
	final class PzMysqlServer
	{
		/*
		 * Status constants
		 */
		const CONNECTED = 1;
		const DISCONNECTED = 2;
		const CONNECTING = 3;

		/**
		 * @var string
		 */
		private $_user = '';

		/**
		 * @var string
		 */
		private $_password = '';

		/**
		 * @var string
		 */
		private $_host = '';

		/**
		 * @var string
		 */
		private $_dbName = '';

		/**
		 * @var int
		 */
		private $_port = 0;

		/**
		 * @var int
		 */
		private $_connectRetryAttempts = 0;

		/**
		 * @var int
		 */
		private $_connectRetryDelay = 0;

		/**
		 * @var int
		 */
		private $_status = self::DISCONNECTED;

		/**
		 * @var null
		 */
		private $_mysqli_obj = NULL;

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

		/*
		 * Attempts to connect to the mysql server
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

		/*
		 * Disconnect the mysql server
		 */
		public function disconnect()
		{
			if($this->isConnected() === true && is_object($this->_mysqli_obj))
			{
				$this->_mysqli_obj->close();

				$this->_mysqli_obj = NULL;

				$this->_status = self::DISCONNECTED;
			}
		}

		/**
		 * @return bool
		 */
		public function isConnected()
		{
			return ($this->_status===self::CONNECTED&&is_object($this->_mysqli_obj)?true:false);
		}

		/**
		 * @return null
		 */
		public function returnMysqliObj()
		{
			return $this->_mysqli_obj;
		}

		/**
		 * @return mixed
		 */
		public function insertId()
		{
			return $this->_mysqli_obj->insert_id;
		}

		/**
		 * @return mixed
		 */
		public function affectedRows()
		{
			return $this->_mysqli_obj->affected_rows;
		}

		/**
		 * @param $dbName
		 *
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
		 * @param      $user
		 * @param      $password
		 * @param null $dbName
		 *
		 * @return bool
		 */
		public function changeUser($user, $password, $dbName = NULL)
		{
			if($this->_mysqli_obj->change_user($user, $password, $dbName))
			{
				$this->_user = $user;
				$this->_password = $password;

				if($dbName !== NULL)
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
