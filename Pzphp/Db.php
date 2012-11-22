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
			return $this->pzphp()->pz()->addMysqliServer($username, $password, $dbname, $host, $port);
		}

		/**
		 * Returns the active mysqli object.
		 *
		 * @access public
		 * @return bool|mysqli
		 */
		public function dbObject()
		{
			return $this->pzphp()->pz()->mysqliActiveObject();
		}

		/**
		 * Gets the last insert id.
		 *
		 * @access public
		 * @return int
		 */
		public function insertId()
		{
			return $this->pzphp()->pz()->mysqliInteract()->mysqliInsertId();
		}

		/**
		 * Gets the affected rows count from the last insert/delete/update/etc query.
		 *
		 * @access public
		 * @return int
		 */
		public function affectedRows()
		{
			return $this->pzphp()->pz()->mysqliInteract()->mysqliAffectedRows();
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
			return (is_object($object)?$object->num_rows:0);
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
			return (is_object($object)?$object->fetch_assoc():false);
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
			return (is_object($object)?$object->fetch_row():false);
		}

		/**
		 * Clears the result set if a valid result object is provided.
		 *
		 * @access public
		 * @param $object
		 */
		public function freeResult($object)
		{
			if(is_object($object) && method_exists($object, 'close'))
			{
				$object->close();
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
			return $this->pzphp()->pz()->mysqliInteract()->mysqliSelectDatabase($name);
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
			return $this->pzphp()->pz()->mysqliInteract()->mysqliChangeUser($user, $password, $dbName);
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
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
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
			return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, true, $decimalPlaces);
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
			return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, false, 2, $cleanHtmlLevel);
		}
	}
