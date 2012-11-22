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
	class PzPHP_Db extends PzPHP_Wrapper
	{
		const PDO = 'IS_PDO';

		/**
		 * The established database module to use.
		 *
		 * @access private
		 * @var int
		 */
		private $_databaseMethod = PZPHP_DATABASE_MYSQLI;

		/**
		 * If databaseMethod is PDO, this varaible defines which one.
		 *
		 * @access private
		 * @var int
		 */
		private $_pdoType = -1;

		/**
		 * The contstruct sets the database method (module) to be used.
		 */
		function __construct()
		{
			$this->setDatabaseMethod();
		}

		/**
		 * Sets the chosen database method locally, and then returns its identifier.
		 *
		 * @access public
		 * @param int $method
		 * @return int
		 */
		public function setDatabaseMethod($method = PZPHP_DATABASE_MODE)
		{
			switch($method)
			{
				case PZPHP_DATABASE_MYSQLI:
					$this->_databaseMethod = PZPHP_DATABASE_MYSQLI;
					break;
				case PZPHP_DATABASE_MYSQL:
					$this->_databaseMethod = PZPHP_DATABASE_MYSQL;
					break;
				case (PZPHP_DATABASE_PDO_CUBRID||PZPHP_DATABASE_PDO_MSSQL||PZPHP_DATABASE_PDO_FIREBIRD||PZPHP_DATABASE_PDO_IBM||PZPHP_DATABASE_PDO_INFORMIX||PZPHP_DATABASE_PDO_MYSQL||PZPHP_DATABASE_PDO_MSSQL05PLUS||PZPHP_DATABASE_PDO_ORACLE||PZPHP_DATABASE_PDO_ODBC||PZPHP_DATABASE_PDO_POSTGRESQL||PZPHP_DATABASE_PDO_SQLITE||PZPHP_DATABASE_PDO_4D):
					$this->_databaseMethod = self::PDO;
					$this->_pdoType = $method;
					break;
				default:
					$this->_databaseMethod = PZPHP_DATABASE_MYSQLI;
			}

			return $this->_databaseMethod;
		}

		/**
		 * Add a Mysql server to the pool.
		 *
		 * @access public
		 * @param string $username
		 * @param string $password
		 * @param string $dbname
		 * @param string $host
		 * @param int    $port
		 * @return mixed
		 */
		public function addServer($username, $password, $dbname, $host = 'localhost', $port = 3306)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->addMysqliServer($username, $password, $dbname, $host, $port);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->addMysqlServer($username, $password, $dbname, $host, $port);
				case self::PDO:
					return $this->pzphp()->pz()->addPDOServer($username, $password, $this->_pdoType, $dbname, $host, $port);
				default:
					return false;
			}
		}

		/**
		 * Returns the active mysqli object.
		 *
		 * @access public
		 * @return bool|mysqli|pdo|mysql
		 */
		public function dbObject()
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliActiveObject();
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlActiveObject();
				case self::PDO:
					return $this->pzphp()->pz()->pdoActiveObject();
				default:
					return false;
			}
		}

		/**
		 * Gets the last insert id.
		 *
		 * @access public
		 * @return int
		 */
		public function insertId()
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->insertId();
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->mysqlInsertId();
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->pdoInsertId();
				default:
					return false;
			}
		}

		/**
		 * Gets the affected rows count from the last insert/delete/update/etc query.
		 *
		 * @access public
		 * @return int
		 */
		public function affectedRows()
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->affectedRows();
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqliInteract()->mysqlAffectedRows();
				case self::PDO:
					return $this->pzphp()->pz()->mysqliInteract()->pdoAffectedRows();
				default:
					return false;
			}
		}

		/**
		 * Gets the returned rows count for the mysqli_result object.
		 *
		 * @access public
		 * @param $object
		 * @return int
		 */
		public function returnedRows($object)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return (is_object($object)?$object->num_rows:0);
				case PZPHP_DATABASE_MYSQL:
					return (is_object($object)?$object->num_rows:0);
				case self::PDO:
					return (is_object($object)?$object->num_rows:0);
				default:
					return false;
			}
		}

		/**
		 * Gets the next row from the mysqli_result object in an associative array.
		 *
		 * @access public
		 * @param $object
		 * @return bool
		 */
		public function fetchNextRowAssoc($object)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return (is_object($object)?$object->fetch_assoc():false);
				case PZPHP_DATABASE_MYSQL:
					return (is_object($object)?$object->fetch_assoc():false);
				case self::PDO:
					return (is_object($object)?$object->fetch_assoc():false);
				default:
					return false;
			}
		}

		/**
		 * Gets the next row from the mysqli_result object in a numerated array.
		 *
		 * @access public
		 * @param $object
		 * @return bool
		 */
		public function fetchNextRowEnum($object)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return (is_object($object)?$object->fetch_row():false);
				case PZPHP_DATABASE_MYSQL:
					return (is_object($object)?$object->fetch_row():false);
				case self::PDO:
					return (is_object($object)?$object->fetch_row():false);
				default:
					return false;
			}
		}

		/**
		 * Clears the result set if a valid result object is provided.
		 *
		 * @access public
		 * @param $object
		 */
		public function freeResult($object)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					if(is_object($object) && method_exists($object, 'close'))
					{
						$object->close();
					}
				case PZPHP_DATABASE_MYSQL:
					if(is_object($object) && method_exists($object, 'close'))
					{
						$object->close();
					}
				case self::PDO:
					if(is_object($object) && method_exists($object, 'close'))
					{
						$object->close();
					}
			}

		}

		/**
		 * Changes the current database.
		 *
		 * @access public
		 * @param string $name
		 * @return bool
		 */
		public function changeDatabase($name)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->selectDatabase($name);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->mysqlSelectDatabase($name);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->pdoSelectDatabase($name);
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
		 * @return bool
		 */
		public function changeUser($user, $password, $dbName = NULL)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->changeUser($user, $password, $dbName);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->mysqlChangeUser($user, $password, $dbName);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->pdoChangeUser($user, $password, $dbName);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a select query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function select($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->read($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->read($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a set query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function set($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->read($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->read($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an optimize query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function optimize($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->read($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->read($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a check query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function check($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->read($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->read($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->read($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an insert query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function insert($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->write($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->write($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle a delete query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function delete($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->write($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->write($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query);
				default:
					return false;
			}
		}

		/**
		 * Expects to handle an update query.
		 *
		 * @access public
		 * @param $query
		 * @return bool|mysqli_result
		 */
		public function update($query)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->write($query);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->write($query);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->write($query);
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
		 * @return mixed
		 */
		public function sanitizeNumeric($value, $decimalPlaces = 2)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, true, $decimalPlaces);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->sanitize($value, true, $decimalPlaces);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->sanitize($value, true, $decimalPlaces);
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
		 * @return mixed
		 */
		public function sanitizeNonNumeric($value, $cleanHtmlLevel = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			switch($this->_databaseMethod)
			{
				case PZPHP_DATABASE_MYSQLI:
					return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, false, 2, $cleanHtmlLevel);
				case PZPHP_DATABASE_MYSQL:
					return $this->pzphp()->pz()->mysqlInteract()->sanitize($value, false, 2, $cleanHtmlLevel);
				case self::PDO:
					return $this->pzphp()->pz()->pdoInteract()->sanitize($value, false, 2, $cleanHtmlLevel);
				default:
					return false;
			}
		}
	}
