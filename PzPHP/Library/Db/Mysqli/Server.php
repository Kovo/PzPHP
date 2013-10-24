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
class PzPHP_Library_Db_Mysqli_Server
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
	 * The final mysqli object.
	 *
	 * @access private
	 * @var null|mysqli
	 */
	private $_dbObject = NULL;

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

			$this->_dbObject =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

			if(mysqli_connect_error())
			{
				if(strpos(mysqli_connect_error(), 'access denied') !== false)
				{
					$this->_status = self::DISCONNECTED;

					return false;
				}

				for($x=0;$x<$this->_connectRetryAttempts;$x++)
				{
					sleep($this->_connectRetryDelay);

					$this->_dbObject =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

					if(mysqli_connect_error())
					{
						if(strpos(mysqli_connect_error(), 'access denied') !== false)
						{
							$this->_status = self::DISCONNECTED;

							return false;
						}

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
		if($this->isConnected() === true && is_object($this->_dbObject))
		{
			$this->_dbObject->close();

			$this->_dbObject = NULL;

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
		return ($this->_status===self::CONNECTED&&is_object($this->_dbObject)?true:false);
	}

	/**
	 * Returns the active mysqli object.
	 *
	 * @access public
	 * @return mysqli|null
	 */
	public function getDBObject()
	{
		return $this->_dbObject;
	}

	/**
	 * Returns the last insert id.
	 *
	 * @access public
	 * @return mixed
	 */
	public function insertId()
	{
		return $this->_dbObject->insert_id;
	}

	/**
	 * Returns the affected rows from the last query.
	 *
	 * @access public
	 * @return mixed
	 */
	public function affectedRows()
	{
		return $this->_dbObject->affected_rows;
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
		if($this->_dbObject->select_db($dbName))
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
	public function changeUser($user, $password, $dbName = NULL)
	{
		if($this->_dbObject->change_user($user, $password, $dbName))
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
