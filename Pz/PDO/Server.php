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
	 * Class is used for storing database connection information using pdo.
	 */
	final class Pz_PDO_Server
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
		 * The username that will access the database server.
		 *
		 * @access private
		 * @var string
		 */
		private $_user = '';

		/**
		 * The password that will access the database server.
		 *
		 * @access private
		 * @var string
		 */
		private $_password = '';

		/**
		 * The host that the database server is on.
		 *
		 * @access private
		 * @var string
		 */
		private $_host = '';

		/**
		 * The server name for the database (if any).
		 *
		 * @access private
		 * @var string
		 */
		private $_server = '';

		/**
		 * The connection socket for the database (if any).
		 *
		 * @access private
		 * @var string
		 */
		private $_socket = '';

		/**
		 * The connection protocol for the database (if any).
		 *
		 * @access private
		 * @var string
		 */
		private $_protocol = '';

		/**
		 * The default database to connect to.
		 *
		 * @access private
		 * @var string
		 */
		private $_dbName = '';

		/**
		 * The type of database this is.
		 *
		 * @access private
		 * @var string
		 */
		private $_dbType = '';

		/**
		 * The charset to use for the connection.
		 *
		 * @access private
		 * @var string
		 */
		private $_charset = '';

		/**
		 * The chosen database's driver options.
		 *
		 * @access private
		 * @var array
		 */
		private $_dbDriverOptions = array();

		/**
		 * The port that the database server is on.
		 *
		 * @access private
		 * @var int
		 */
		private $_port = 0;

		/**
		 * The amount of times Pz should try to reconnect to the database server.
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
		 * The final pdo object.
		 *
		 * @access private
		 * @var null|pdo
		 */
		private $_pdo_obj = NULL;

		/**
		 * The constructor handles setting the database server credentials.
		 *
		 * @access private
		 * @param string $dbUser
		 * @param string $dbPassword
		 * @param string $dbType
		 * @param string $dbName
		 * @param string $dbHost
		 * @param int $dbPort
		 * @param int $connectRetries
		 * @param int $connectRetryWait
		 * @param array $dbDriverOptions
		 * @param string $server
		 * @param string $protocol
		 * @param string $socket
		 * @param string $charset
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
		 * Attempts to connect to the database server.
		 *
		 * @access public
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
		 * Returns the correct DSN for the PDO connection.
		 *
		 * @access private
		 * @return string
		 */
		private function _getDSN()
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

		/**
		 * Disconnects from the database server.
		 *
		 * @access public
		 */
		public function disconnect()
		{
			if($this->isConnected() === true && is_object($this->_pdo_obj))
			{
				$this->_pdo_obj = NULL;

				$this->_status = self::DISCONNECTED;
			}
		}

		/**
		 * Returns true or false if the database server connection went through and is active.
		 *
		 * @access public
		 * @return bool
		 */
		public function isConnected()
		{
			return ($this->_status===self::CONNECTED&&is_object($this->_pdo_obj)?true:false);
		}

		/**
		 * Returns the active pdo object.
		 *
		 * @access public
		 * @return pdo|null
		 */
		public function returnPDOObj()
		{
			return $this->_pdo_obj;
		}

		/**
		 * Returns the last insert id.
		 *
		 * @access public
		 * @return mixed
		 */
		public function insertId()
		{
			return $this->_pdo_obj->lastInsertId();
		}

		/**
		 * Returns the affected rows from the provided query.
		 *
		 * @access public
		 * @var PDOStatement $queryOnject
		 * @return mixed
		 */
		public function affectedRows(PDOStatement $queryOnject)
		{
			return $queryOnject->rowCount();
		}
	}
