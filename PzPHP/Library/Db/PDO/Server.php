<?php
class PzPHP_Library_Db_PDO_Server
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
	protected $_server = '';

	/**
	 * @var string
	 */
	protected $_socket = '';

	/**
	 * @var string
	 */
	protected $_protocol = '';

	/**
	 * @var string
	 */
	protected $_dbName = '';

	/**
	 * @var string
	 */
	protected $_dbType = '';

	/**
	 * @var string
	 */
	protected $_charset = '';

	/**
	 * @var array
	 */
	protected $_dbDriverOptions = array();

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
	 * @var null|pdo
	 */
	protected $_pdo_obj = null;

	/**
	 * @param $dbUser
	 * @param $dbPassword
	 * @param $dbType
	 * @param $dbName
	 * @param $dbHost
	 * @param $dbPort
	 * @param $connectRetries
	 * @param $connectRetryWait
	 * @param $dbDriverOptions
	 * @param $server
	 * @param $protocol
	 * @param $socket
	 * @param $charset
	 */
	function __construct($dbUser, $dbPassword, $dbType, $dbName, $dbHost, $dbPort, $connectRetries, $connectRetryWait, $dbDriverOptions, $server, $protocol, $socket, $charset)
	{
		$this->_user = $dbUser;
		$this->_password = $dbPassword;
		$this->_dbType = $dbType;
		$this->_dbName = $dbName;
		$this->_host = $dbHost;
		$this->_port = $dbPort;
		$this->_connectRetryAttempts = $connectRetries;
		$this->_connectRetryDelay = $connectRetryWait;
		$this->_dbDriverOptions = $dbDriverOptions;
		$this->_server = $server;
		$this->_protocol = $protocol;
		$this->_socket = $socket;
		$this->_charset = $charset;
	}

	/**
	 * @return bool
	 */
	public function connect()
	{
		if($this->isConnected() === false)
		{
			$this->_status = self::CONNECTING;

			$retries = 0;
			while($retries <= $this->_connectRetryAttempts)
			{
				try
				{
					$this->_pdo_obj =  new PDO($this->_getDSN(), $this->_user, $this->_password, $this->_dbDriverOptions);
					$this->_pdo_obj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
					$this->_pdo_obj->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

					$this->_status = self::CONNECTED;

					return true;

				}
				catch(PDOException $e)
				{
					$retries++;
				}
			}

			$this->_status = self::DISCONNECTED;

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * @return string
	 */
	protected function _getDSN()
	{
		switch($this->_dbType)
		{
			case 'cubrid';
				return 'cubrid:host='.$this->_host.';port='.$this->_port.';dbname='.$this->_dbName;
			case 'mssql';
				return 'mssql:host='.$this->_host.($this->_port>0?','.$this->_port:'').';dbname='.$this->_dbName;
			case 'sybase';
				return 'sybase:host='.$this->_host.';dbname='.$this->_dbName;
			case 'dblib';
				return 'dblib:host='.$this->_host.($this->_port>0?':'.$this->_port:'').';dbname='.$this->_dbName;
			case 'firebird';
				return 'firebird:dbname='.$this->_dbName;
			case 'ibm';
				return 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE='.$this->_dbName.';HOSTNAME='.$this->_host.';PORT='.$this->_port.';PROTOCOL=TCPIP;';
			case 'informix';
				return 'informix:host='.$this->_host.'; '.($this->_port>0?'service='.$this->_port.';':'').' database='.$this->_dbName.'; server='.$this->_server.'; protocol='.$this->_protocol.';EnableScrollableCursors=1';
			case 'mysql';
				return 'mysql:host='.$this->_host.';'.($this->_port>0?'port='.$this->_port.';':'').($this->_socket!=''?'unix_socket='.$this->_socket.';':'').'dbname='.$this->_dbName;
			case 'sqlsrv';
				return 'sqlsrv:Server='.($this->_server!=''?$this->_server:$this->_host).($this->_port>0?','.$this->_port:'').';Database='.$this->_dbName;
			case 'oci';
				return 'oci:dbname='.$this->_host.($this->_port>0?':'.$this->_port:'').($this->_host!=''?'/':'').$this->_dbName.($this->_charset!=''?';charset='.$this->_charset:'');
			case 'odbc';
				return 'odbc:'.$this->_dbName;
			case 'odbcmsaccss';
				return 'odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq='.$this->_dbName;
			case 'pgsql';
				return 'pgsql:host='.$this->_host.';'.($this->_port>0?'port='.$this->_port.';':'').'dbname='.$this->_dbName;
			case 'sqlite';
				return 'sqlite:'.$this->_dbName;
			case 'sqlite2';
				return 'sqlite2:'.$this->_dbName;
			case '4d';
				return '4D:host='.$this->_host.';'.($this->_charset!=''?';charset='.$this->_charset:'');
			default:
				return '';
		}
	}

	public function disconnect()
	{
		if($this->isConnected() === true && is_object($this->_pdo_obj))
		{
			$this->_pdo_obj = null;

			$this->_status = self::DISCONNECTED;
		}
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->_status===self::CONNECTED&&is_object($this->_pdo_obj)?true:false);
	}

	/**
	 * @return null|pdo
	 */
	public function getDBObject()
	{
		return $this->_pdo_obj;
	}

	/**
	 * @return string
	 */
	public function insertId()
	{
		return $this->_pdo_obj->lastInsertId();
	}

	/**
	 * @param PDOStatement $queryOnject
	 * @return int
	 */
	public function affectedRows(PDOStatement $queryOnject)
	{
		return $queryOnject->rowCount();
	}
}
