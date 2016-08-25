<?php
class PzPHP_Library_Db_Mysql_Server extends PzPHP_Library_Abstract_Generic
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
	 * @var string
	 */
	protected $_user = '';

	/**
	 * @var string
	 */
	protected $_password = '';

	/**
	 * @var string
	 */
	protected $_host = '';

	/**
	 * @var string
	 */
	protected $_dbName = '';

	/**
	 * @var int
	 */
	protected $_port = 0;

	/**
	 * @var int
	 */
	protected $_connectRetryAttempts = 0;

	/**
	 * @var int
	 */
	protected $_connectRetryDelay = 0;

	/**
	 * @var int
	 */
	protected $_status = self::DISCONNECTED;

	/**
	 * @var null|resource
	 */
	protected $_mysql_res = null;

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
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->_status===self::CONNECTED&&is_resource($this->_mysql_res)?true:false);
	}

	/**
	 * @return null|resource
	 */
	public function getDBObject()
	{
		return $this->_mysql_res;
	}

	/**
	 * @return int
	 */
	public function insertId()
	{
		return mysql_insert_id($this->_mysql_res);
	}

	/**
	 * @return int
	 */
	public function affectedRows()
	{
		return mysql_affected_rows($this->_mysql_res);
	}

	/**
	 * @param $dbName
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
	 * @param $user
	 * @param $password
	 * @param null $dbName
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
