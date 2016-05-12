<?php
class PzPHP_Library_Db_Mysqli_Server extends PzPHP_Library_Abstract_Generic
{
	/**
	 * @var int
	 */
	const CONNECTED = 1;

	/**
	 * @var int
	 */
	const DISCONNECTED = 2;

	/**
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
	 * @var null|mysqli
	 */
	private $_dbObject = NULL;

	/**
	 * @param $dbUser
	 * @param $dbPassword
	 * @param $dbName
	 * @param $dbHost
	 * @param $dbPort
	 * @param $connectRetries
	 * @param $connectRetryWait
	 */
	function __construct(PzPHP_Core $pzPHP_Core, $dbUser, $dbPassword, $dbName, $dbHost, $dbPort, $connectRetries, $connectRetryWait)
	{
		parent::__construct($pzPHP_Core);

		$this->_user = $dbUser;
		$this->_password = $dbPassword;
		$this->_dbName = $dbName;
		$this->_host = $dbHost;
		$this->_port = $dbPort;
		$this->_connectRetryAttempts = $connectRetries;
		$this->_connectRetryDelay = $connectRetryWait;
	}

	/**
	 * @return bool
	 */
	public function connect()
	{
		if($this->isConnected() === false)
		{
			$this->_status = self::CONNECTING;

			$this->_dbObject =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

			if($this->_dbObject->connect_error)
			{
				if(strpos($this->_dbObject->connect_error, 'access denied') !== false)
				{
					$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Excpetion during connection attempt: '.$this->_dbObject->connect_error.' | '.$this->_dbObject->connect_errno);

					$this->_status = self::DISCONNECTED;

					return false;
				}

				for($x=0;$x<$this->_connectRetryAttempts;$x++)
				{
					sleep($this->_connectRetryDelay);

					$this->_dbObject =  new mysqli($this->_host, $this->_user, $this->_password, $this->_dbName, $this->_port);

					if($this->_dbObject->connect_error)
					{
						$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MYSQL_ERROR_LOG_FILE_NAME'), 'Excpetion during connection attempt: '.$this->_dbObject->connect_error.' | '.$this->_dbObject->connect_errno);

						if(strpos($this->_dbObject->connect_error, 'access denied') !== false)
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
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->_status===self::CONNECTED&&is_object($this->_dbObject)?true:false);
	}

	/**
	 * @return mysqli|null
	 */
	public function getDBObject()
	{
		return $this->_dbObject;
	}

	/**
	 * @return mixed
	 */
	public function insertId()
	{
		return $this->_dbObject->insert_id;
	}

	/**
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->_dbObject->affected_rows;
	}

	/**
	 * @param $dbName
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
	 * @param $user
	 * @param $password
	 * @param null $dbName
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
