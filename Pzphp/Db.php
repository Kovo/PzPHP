<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP_Db
	 */
	class PzPHP_Db extends PzPHP_Wrapper
	{
		/**
		 * @param $username
		 * @param $password
		 * @param $dbname
		 * @param $host
		 * @param $port
		 *
		 * @return mixed
		 */
		public function addServer($username, $password, $dbname, $host, $port)
		{
			return $this->pzphp()->pz()->addMysqlServer($username, $password, $dbname, $host, $port);
		}

		/**
		 * @return bool|mysqli
		 */
		public function dbObject()
		{
			return $this->pzphp()->pz()->mysqlActiveObject();
		}

		/**
		 * @return int
		 */
		public function insertId()
		{
			return $this->pzphp()->pz()->mysqlInsertId();
		}

		/**
		 * @return int
		 */
		public function affectedRows()
		{
			return $this->pzphp()->pz()->mysqlAffectedRows();
		}

		/**
		 * @param $object
		 *
		 * @return int
		 */
		public function returnedRows($object)
		{
			return (is_object($object)?$object->num_rows:0);
		}

		/**
		 * @param $name
		 *
		 * @return bool
		 */
		public function changeDatabase($name)
		{
			return $this->pzphp()->pz()->mysqlSelectDatabase($name);
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
			return $this->pzphp()->pz()->mysqlChangeUser($user, $password, $dbName);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function select($query)
		{
			return $this->pzphp()->pz()->mysqlRead($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function set($query)
		{
			return $this->pzphp()->pz()->mysqlRead($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function optimize($query)
		{
			return $this->pzphp()->pz()->mysqlRead($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function check($query)
		{
			return $this->pzphp()->pz()->mysqlRead($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function insert($query)
		{
			return $this->pzphp()->pz()->mysqlWrite($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function delete($query)
		{
			return $this->pzphp()->pz()->mysqlWrite($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function update($query)
		{
			return $this->pzphp()->pz()->mysqlWrite($query);
		}

		/**
		 * @param     $value
		 * @param int $decimalPlaces
		 *
		 * @return mixed
		 */
		public function sanitizeNumeric($value, $decimalPlaces = 2)
		{
			return $this->pzphp()->pz()->sanitize($value, true, $decimalPlaces);
		}

		/**
		 * @param     $value
		 * @param int $cleanHtmlLevel
		 *
		 * @return mixed
		 */
		public function sanitizeNonNumeric($value, $cleanHtmlLevel = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			return $this->pzphp()->pz()->sanitize($value, false, 2, $cleanHtmlLevel);
		}
	}
