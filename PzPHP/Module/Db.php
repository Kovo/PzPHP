<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP
	 */
	/**
	 * The database class allows you to send queries to a database server, run through results, and a lot more.
	 */
	class PzPHP_Module_Db extends PzPHP_Wrapper
	{
		const PDO = 'IS_PDO';

		/**
		 * The established database module to use.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_databaseMethod = -1;

		/**
		 * If databaseMethod is PDO, this varaible defines which one.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_pdoType = -1;

		/**
		 * The contstruct sets the database method (module) to be used.
		 */
		function __construct()
		{
			$this->setDatabaseMethod(PzPHP_Config::get('PZPHP_DATABASE_MODE'));
		}

		/**
		 * Sets the chosen database method locally, and then returns its identifier.
		 *
		 * @access public
		 * @param int $method
		 * @return int
		 */
		public function setDatabaseMethod($method)
		{
			if($method === PzPHP_Config::get('PZPHP_DATABASE_PDO_CUBRID') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_MSSQL') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_FIREBIRD') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_IBM') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_INFORMIX') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_MYSQL') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_MSSQL05PLUS') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_ORACLE') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_ODBC') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_POSTGRESQL') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_SQLITE') || $method === PzPHP_Config::get('PZPHP_DATABASE_PDO_4D'))
			{
				$this->_databaseMethod = self::PDO;
				$this->_pdoType = $method;
			}
			else
			{
				$this->_databaseMethod = $method;
			}
		}

		/**
		 * Add a Mysql server to the pool.
		 *
		 * @access public
		 * @param string $username
		 * @param string $password
		 * @param string $dbname
		 * @param string $host
		 * @param int $port
		 * @param array $dbDriverOptions
		 * @param string $server
		 * @param string $protocol
		 * @param string $socket
		 * @return mixed
		 */
		public function addServer($username, $password, $dbname, $host = 'localhost', $port = 3306, $dbDriverOptions = array(), $server = '', $protocol = '', $socket = '')
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->addMysqliServer($username, $password, $dbname, $host, $port);
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->addMysqlServer($username, $password, $dbname, $host, $port);
				case self::PDO:
					return $this->pzphp()->pz()->addPDOServer($username, $password, $this->_pdoType, $dbname, $host, $port, $dbDriverOptions, $server, $protocol, $socket);
				default:
					return false;
			}
		}

		/**
		 * Sets the active database id to use.
		 *
		 * @access public
		 * @param int $id
		 * @param bool $autoconnect
		 * @return bool
		 */
		public function setActiveServerId($id, $autoconnect = false)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->setActiveMysqliServerId($id, $autoconnect);
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->setActiveMysqlServerId($id, $autoconnect);
				case self::PDO:
					return $this->pzphp()->pz()->setActivePDOServerId($id, $autoconnect);
				default:
					return false;
			}
		}

		/**
		 * Return the active database object (or using the supplied id).
		 *
		 * @access public
		 * @param $id
		 * @return bool|Pz_Mysql_Server|Pz_Mysqli_Server|Pz_PDO_Server
		 */
		public function returnActiveServerObject($id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliActiveObject($this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlActiveObject($this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoActiveObject($this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Returns the active database object or resource.
		 *
		 * @access public
		 * @param int $id
		 * @return bool|mysqli|pdo|mysql
		 */
		public function dbObject($id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliActiveObject($this->pzphp()->pz()->decideActiveMySqliId($id))->returnMysqliObj();
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlActiveObject($this->pzphp()->pz()->decideActiveMySqlId($id))->returnMysqlRes();
				case self::PDO:
					return $this->pzphp()->pz()->pdoActiveObject($this->pzphp()->pz()->decideActivePDOId($id))->returnPDOObj();
				default:
					return false;
			}
		}

		/**
		 * Gets the last insert id.
		 *
		 * @access public
		 * @param int $id
		 * @return int
		 */
		public function insertId($id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->insertId($this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->insertId($this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->insertId($this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Gets the affected rows count from the last insert/delete/update/etc query.
		 *
		 * @access public
		 * @param PDOStatement|null $queryObject
		 * @param int $id
		 * @return int
		 */
		public function affectedRows(PDOStatement $queryObject = null, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->affectedRows($this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqliInteract()->affectedRows($this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->affectedRows($queryObject, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Gets the returned rows count for the mysqli_result object.
		 *
		 * @access public
		 * @param msqli_result|PDOStatement|resource $object
		 * @return int
		 */
		public function returnedRows($object)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return (is_object($object)?$object->num_rows:0);
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return (is_resource($object)?mysql_num_rows($object):0);
				case self::PDO:
					return (is_object($object)?$object->rowCount():0);
				default:
					return false;
			}
		}

		/**
		 * Gets the next row from the mysqli_result object in an associative array.
		 *
		 * @access public
		 * @param msqli_result|PDOStatement|resource $object
		 * @return bool
		 */
		public function fetchNextRowAssoc($object)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return (is_object($object)?$object->fetch_assoc():false);
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return (is_resource($object)?mysql_fetch_assoc($object):false);
				case self::PDO:
					return (is_object($object)?$object->fetch(PDO::FETCH_ASSOC):false);
				default:
					return false;
			}
		}

		/**
		 * Gets the next row from the mysqli_result object in a numerated array.
		 *
		 * @access public
		 * @param msqli_result|PDOStatement|resource $object
		 * @return bool
		 */
		public function fetchNextRowEnum($object)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return (is_object($object)?$object->fetch_row():false);
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return (is_resource($object)?mysql_fetch_row($object):false);
				case self::PDO:
					return (is_object($object)?$object->fetch(PDO::FETCH_NUM):false);
				default:
					return false;
			}
		}

		/**
		 * Clears the result set if a valid result object is provided.
		 *
		 * @access public
		 * @param msqli_result|PDOStatement|resource $object
		 */
		public function freeResult($object)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					if(is_object($object) && method_exists($object, 'close'))
					{
						$object->close();
					}

					break;
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					if(is_resource($object))
					{
						mysql_free_result($object);
					}

					break;
				case self::PDO:
					if(is_object($object) && method_exists($object, 'closeCursor '))
					{
						$object->closeCursor();
					}
			}

		}

		/**
		 * Changes the current database.
		 *
		 * @access public
		 * @param string $name
		 * @param int $id
		 * @return bool
		 */
		public function changeDatabase($name, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->selectDatabase($name, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->selectDatabase($name, $this->pzphp()->pz()->decideActiveMySqlId($id));
				default:
					return false;
			}
		}

		/**
		 * Changes the current mysql user.
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
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->changeUser($user, $password, $dbName, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->mysqlChangeUser($user, $password, $dbName, $this->pzphp()->pz()->decideActiveMySqlId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a select query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function select($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a set query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function set($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an optimize query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function optimize($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a check query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function check($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an analyze query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function analyze($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->read($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an insert query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function insert($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a delete query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function delete($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an update query.
		 *
		 * @access public
		 * @param string $query
		 * @param int $id
		 * @return bool|mysqli_result|PDOStatement|resource
		 */
		public function update($query, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->write($query, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to be passed a numeric value to make sure it is safe.
		 *
		 * @access public
		 * @param mixed $value
		 * @param int $decimalPlaces
		 * @param int $id
		 * @return mixed
		 */
		public function sanitizeNumeric($value, $decimalPlaces = 2, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, true, $decimalPlaces, Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->sanitize($value, true, $decimalPlaces, Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->sanitize($value, true, $decimalPlaces, Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}

		/**
		 * Expects to be passed a non-numeric value to make sure it is safe.
		 *
		 * @access public
		 * @param mixed $value
		 * @param int $cleanHtmlLevel
		 * @param int $id
		 * @return mixed
		 */
		public function sanitizeNonNumeric($value, $cleanHtmlLevel = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $id = -1)
		{
			switch($this->_databaseMethod)
			{
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQLI'):
					return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $this->pzphp()->pz()->decideActiveMySqliId($id));
				case PzPHP_Config::get('PZPHP_DATABASE_MYSQL'):
					return $this->pzphp()->pz()->mysqlInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $this->pzphp()->pz()->decideActiveMySqlId($id));
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $this->pzphp()->pz()->decideActivePDOId($id));
				default:
					return false;
			}
		}
	}
