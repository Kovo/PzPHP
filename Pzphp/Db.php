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
		 * @param        $username
		 * @param        $password
		 * @param        $dbname
		 * @param string $host
		 * @param int    $port
		 *
		 * @return mixed
		 */
		public function addServer($username, $password, $dbname, $host = 'localhost', $port = 3306)
		{
			return $this->pzphp()->pz()->addMysqliServer($username, $password, $dbname, $host, $port);
		}

		/**
		 * @return bool|mysqli
		 */
		public function dbObject()
		{
			return $this->pzphp()->pz()->mysqliActiveObject();
		}

		/**
		 * @return int
		 */
		public function insertId()
		{
			return $this->pzphp()->pz()->mysqliInteract()->mysqliInsertId();
		}

		/**
		 * @return int
		 */
		public function affectedRows()
		{
			return $this->pzphp()->pz()->mysqliInteract()->mysqliAffectedRows();
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
		 * @param $object
		 *
		 * @return bool
		 */
		public function fetchNextRowAssoc($object)
		{
			return (is_object($object)?$object->fetch_assoc():false);
		}

		/**
		 * @param $object
		 *
		 * @return bool
		 */
		public function fetchNextRowEnum($object)
		{
			return (is_object($object)?$object->fetch_row():false);
		}

		/**
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
		 * @param $name
		 *
		 * @return bool
		 */
		public function changeDatabase($name)
		{
			return $this->pzphp()->pz()->mysqliInteract()->mysqliSelectDatabase($name);
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
			return $this->pzphp()->pz()->mysqliInteract()->mysqliChangeUser($user, $password, $dbName);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function select($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function set($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function optimize($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function check($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->read($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function insert($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function delete($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
		}

		/**
		 * @param $query
		 *
		 * @return mixed
		 */
		public function update($query)
		{
			return $this->pzphp()->pz()->mysqliInteract()->write($query);
		}

		/**
		 * @param     $value
		 * @param int $decimalPlaces
		 *
		 * @return mixed
		 */
		public function sanitizeNumeric($value, $decimalPlaces = 2)
		{
			return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, true, $decimalPlaces);
		}

		/**
		 * @param     $value
		 * @param int $cleanHtmlLevel
		 *
		 * @return mixed
		 */
		public function sanitizeNonNumeric($value, $cleanHtmlLevel = Pz_Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			return $this->pzphp()->pz()->mysqliInteract()->sanitize($value, false, 2, $cleanHtmlLevel);
		}
	}
