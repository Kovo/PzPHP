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
	 * @var int
	 */
	protected $_activeServerId = -1;

	public function add($user, $password, $name = '', $host = 'localhost', $port = 3306, $preventAutoAssign = false, $pdoDriverOptions = array(), $pdoServer = '', $pdoProtocol = '', $pdoSocket = '', $pdoCharset = '')
	{
		switch(PzPHP_Config::get('DATABASE_MODE'))
		{
			case PzPHP_Config::get('DATABASE_MYSQLI'):
				$this->_servers[] = new PzPHP_Library_Db_Mysqli_Server($user, $password, $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			case PzPHP_Config::get('DATABASE_MYSQL'):
				$this->_servers[] = new PzPHP_Library_Db_Mysql_Server($user, $password, $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			default:
				$this->_servers[] = new PzPHP_Library_Db_PDO_Server($user, $password, $name, $host, $port, PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'), $pdoDriverOptions, $pdoServer, $pdoProtocol, $pdoSocket, $pdoCharset);
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
	 * @param $id
	 * @return int
	 */
	public function getActiveServerId($id = -1)
	{
		return ($id===-1?$this->_activeServerId:$id);
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
	 * @param $id
	 * @return PzPHP_Library_Db_Mysqli_Server|PzPHP_Library_Db_Mysql_Server|PzPHP_Library_Db_PDO_Server
	 * @throws PzPHP_Exception
	 */
	public function getActiveServer($id = -1)
	{
		if(isset($this->_servers[($id===-1?$this->_activeServerId:$id)]))
		{
			return $this->_servers[($id===-1?$this->_activeServerId:$id)];
		}
		else
		{
			throw new PzPHP_Exception('Active server not found!', PzPHP_Helper_Codes::DATABASE_NO_ACTIVE_SERVER_ID);
		}
	}

	public function sanitizeNumeric($value, $decimalPlaces = 2, $id = -1)
	{
		switch($this->_databaseMethod)
		{
/*			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			case PzPHP_Config::get('DATABASE_MYSQL'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);;
			case self::PDO:
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			default:
				return false;*/
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
	public function sanitizeNonNumeric($value, $cleanHtmlLevel = PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $id = -1)
	{
		switch($this->_databaseMethod)
		{
		/*	case PzPHP_Config::get('DATABASE_MYSQLI'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			case PzPHP_Config::get('DATABASE_MYSQL'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);;
			case self::PDO:
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			default:
				return false;*/
		}
	}
}
