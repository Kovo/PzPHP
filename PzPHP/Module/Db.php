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
	/**
	 * @var array
	 */
	protected $_servers = array();

	/**
	 * @var null|PzPHP_Library_Db_Couchbase_Interactions
	 */
	protected $_couchbaseInteractions = null;

	/**
	 * @var null|PzPHP_Library_Db_Mysqli_Interactions
	 */
	protected $_mysqliInteractions = null;

	/**
	 * @var null|PzPHP_Library_Db_PDO_Interactions
	 */
	protected $_pdoInteractions = null;

	/**
	 * @var int
	 */
	protected $_activeServerId = -1;

	/**
	 * @return null|PzPHP_Library_Db_Couchbase_Interactions
	 */
	public function couchbaseInteract()
	{
		if($this->_couchbaseInteractions === null)
		{
			$this->_couchbaseInteractions = new PzPHP_Library_Db_Couchbase_Interactions($this->_PzPHP);
		}

		return $this->_couchbaseInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Db_Mysqli_Interactions
	 */
	public function mysqliInteract()
	{
		if($this->_mysqliInteractions === null)
		{
			$this->_mysqliInteractions = new PzPHP_Library_Db_Mysqli_Interactions($this->_PzPHP);
		}

		return $this->_mysqliInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Db_PDO_Interactions
	 */
	public function pdoInteract()
	{
		if($this->_pdoInteractions === null)
		{
			$this->_pdoInteractions = new PzPHP_Library_Db_PDO_Interactions($this->_PzPHP);
		}

		return $this->_pdoInteractions;
	}

	/**
	 * @param $user
	 * @param $password
	 * @param string $name
	 * @param string $host
	 * @param int $port
	 * @param bool $preventAutoAssign
	 * @param array $pdoDriverOptions
	 * @param string $pdoServer
	 * @param string $pdoProtocol
	 * @param string $pdoSocket
	 * @param string $pdoCharset
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	public function add($user, $password, $name = '', $host = 'localhost', $port = 3306, $preventAutoAssign = false, $pdoDriverOptions = array(), $pdoServer = '', $pdoProtocol = '', $pdoSocket = '', $pdoCharset = '')
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				$this->_servers[] = new PzPHP_Library_Db_Mysqli_Server($this->pzphp(), $user, $password, $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				$this->_servers[] = new PzPHP_Library_Db_Couchbase_Server($this->pzphp(), $user, $password, $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			case PzPHP_Config::get('DATABASE_PDO'):
				$this->_servers[] = new PzPHP_Library_Db_PDO_Server($this->pzphp(), $user, $password, PzPHP_Config::get('DATABASE_PDO_MODE'), $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'), $pdoDriverOptions, $pdoServer, $pdoProtocol, $pdoSocket, $pdoCharset);
				break;
			default:
				throw new PzPHP_Exception('Invalid database mode provided.', PzPHP_Helper_Codes::DATABASE_INVALID_MODE);
		}

		$serverId = max(array_keys($this->_servers));

		$autoAssign = (PzPHP_Config::get('SETTING_DB_AUTO_ASSIGN_ACTIVE_SERVER') && !$preventAutoAssign);
		if($autoAssign)
		{
			$this->setActiveServerId($serverId);
		}

		if(PzPHP_Config::get('SETTING_DB_AUTO_CONNECT_SERVER'))
		{
			$this->connect($serverId, $autoAssign);
		}

		return $serverId;
	}

	/**
	 * @param $serverId
	 * @param bool $preventAutoAssign
	 * @return bool
	 * @throws PzPHP_Exception
	 */
	public function connect($serverId, $preventAutoAssign = false)
	{
		if($this->_servers[$serverId]->connect())
		{
			if(PzPHP_Config::get('SETTING_DB_AUTO_ASSIGN_ACTIVE_SERVER') && !$preventAutoAssign)
			{
				$this->setActiveServerId($serverId);
			}

			return true;
		}
		else
		{
			throw new PzPHP_Exception('Could not connect to server with id: '.$serverId, PzPHP_Helper_Codes::DATABASE_CONNECT_FAILURE);
		}
	}

	/**
	 * @param $serverId
	 * @return int
	 */
	public function getActiveServerId($serverId = -1)
	{
		return ($serverId===-1?$this->_activeServerId:$serverId);
	}

	/**
	 * @param $serverId
	 * @return $this
	 */
	public function setActiveServerId($serverId)
	{
		$this->_activeServerId = $serverId;

		return $this;
	}

	/**
	 * @param $serverId
	 * @return PzPHP_Library_Db_Mysqli_Server|PzPHP_Library_Db_Couchbase_Server|PzPHP_Library_Db_PDO_Server
	 * @throws PzPHP_Exception
	 */
	public function getActiveServer($serverId = -1)
	{
		if(isset($this->_servers[($serverId===-1?$this->_activeServerId:$serverId)]))
		{
			return $this->_servers[($serverId===-1?$this->_activeServerId:$serverId)];
		}
		else
		{
			throw new PzPHP_Exception('Active server not found!', PzPHP_Helper_Codes::DATABASE_NO_ACTIVE_SERVER_ID);
		}
	}

	/**
	 * @param $value
	 * @param int $decimalPlaces
	 * @param $serverId
	 * @return bool|mixed
	 */
	public function sanitizeNumeric($value, $decimalPlaces = 2, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->sanitize($value, true, $decimalPlaces, PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->sanitize($value, true, $decimalPlaces, PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->sanitize($value, true, $decimalPlaces, PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $value
	 * @param int $cleanHtmlLevel
	 * @param $serverId
	 * @return bool|mixed
	 */
	public function sanitizeNonNumeric($value, $cleanHtmlLevel = PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->sanitize($value, false, 2, $cleanHtmlLevel, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $serverId
	 * @return null|string
	 */
	public function getLastErrorCode($serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->getLastErrorCode($serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->getLastErrorCode($serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->getLastErrorCode($serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $serverId
	 * @return null|string
	 */
	public function getLastErrorMessage($serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->getLastErrorMessage($serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->getLastErrorMessage($serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->getLastErrorMessage($serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function select($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function set($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function optimize($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function analyze($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function check($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function insert($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->write($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function delete($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->write($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function update($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->write($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->write($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function alter($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result
	 */
	public function create($query, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->read($query, $serverId);
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->read($query, $serverId);
			default:
				return false;
		}
	}

	/**
	 * @param $serverId
	 * @return bool|int
	 */
	public function insertId($serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->insertId($this->getActiveServerId($serverId));
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->insertId($this->getActiveServerId($serverId));
			default:
				return false;
		}
	}

	/**
	 * @param PDOStatement $queryObject
	 * @param $serverId
	 * @return bool|int
	 */
	public function affectedRows(PDOStatement $queryObject = null, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->affectedRows($this->getActiveServerId($serverId));
			case PzPHP_Config::get('DATABASE_PDO'):
				return $this->pdoInteract()->affectedRows($queryObject, $this->getActiveServerId($serverId));
			default:
				return false;
		}
	}

	/**
	 * @param $object
	 * @return bool|int
	 */
	public function returnedRows($object)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return (is_object($object)?$object->num_rows:0);
			case PzPHP_Config::get('DATABASE_PDO'):
				return (is_object($object)?$object->rowCount():0);
			default:
				return false;
		}
	}

	/**
	 * @param $object
	 * @return array|bool
	 */
	public function fetchNextRowAssoc($object)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return (is_object($object)?$object->fetch_assoc():false);
			case PzPHP_Config::get('DATABASE_PDO'):
				return (is_object($object)?$object->fetch(PDO::FETCH_ASSOC):false);
			default:
				return false;
		}
	}

	/**
	 * @param $object
	 * @return array|bool
	 */
	public function fetchNextRowEnum($object)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return (is_object($object)?$object->fetch_row():false);
			case PzPHP_Config::get('DATABASE_PDO'):
				return (is_object($object)?$object->fetch(PDO::FETCH_NUM):false);
			default:
				return false;
		}
	}

	/**
	 * @param $object
	 */
	public function freeResult($object)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				if(is_object($object) && method_exists($object, 'close'))
				{
					$object->close();
				}

				break;
			case PzPHP_Config::get('DATABASE_PDO'):
				if(is_object($object) && method_exists($object, 'closeCursor'))
				{
					$object->closeCursor();
				}
		}

	}

	/**
	 * @param $name
	 * @param $serverId
	 * @return bool
	 */
	public function changeDatabase($name, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->selectDatabase($name, $this->getActiveServerId($serverId));
			case PzPHP_Config::get('DATABASE_COUCHBASE'):
				return $this->couchbaseInteract()->selectDatabase($name, $this->getActiveServerId($serverId));
			default:
				return false;
		}
	}

	/**
	 * @param $user
	 * @param $password
	 * @param null $dbName
	 * @param $serverId
	 * @return bool
	 */
	public function changeUser($user, $password, $dbName = null, $serverId = -1)
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return $this->mysqliInteract()->changeUser($user, $password, $dbName, $this->getActiveServerId($serverId));
			default:
				return false;
		}
	}
}
