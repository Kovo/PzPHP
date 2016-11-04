<?php
class PzPHP_Library_Db_Couchbase_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @var array
	 */
	protected $_lastErrorNo = array();

	/**
	 * @var array
	 */
	protected $_lastErrorMsg = array();

	/**
	 * @param $serverId
	 * @return null
	 */
	public function getLastErrorCode($serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		return (isset($this->_lastErrorNo[$serverId])?$this->_lastErrorNo[$serverId]:null);
	}

	/**
	 * @param $serverId
	 * @return null
	 */
	public function getLastErrorMessage($serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		return (isset($this->_lastErrorMsg[$serverId])?$this->_lastErrorMsg[$serverId]:null);
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result|PDOStatement
	 */
	public function read($query, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			$query = trim($query);
			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->query(CouchbaseN1qlQuery::fromString($query));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Query failed: "'.$query.'". | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				$this->_lastErrorMsg[$serverId] = null;
				$this->_lastErrorNo[$serverId] = null;

				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during query: "'.$query.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $query
	 * @param $serverId
	 * @return bool|mysqli_result|PDOStatement
	 */
	public function write($query, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			$query = trim($query);

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->query(CouchbaseN1qlQuery::fromString($query));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Query failed: "'.$query.'". | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				$this->_lastErrorMsg[$serverId] = null;
				$this->_lastErrorNo[$serverId] = null;

				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during query: "'.$query.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function insert($key, $value, $expiry = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->insert($key, $value, array('expiry' => $expiry));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Insert failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during insert: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function append($key, $value, $expiry = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->append($key, $value, array('expiry' => $expiry));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Append failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during append: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function prepend($key, $value, $expiry = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->prepend($key, $value, array('expiry' => $expiry));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Prepend failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during prepend: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function counter($key, $value = 1, $initial = null, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				if($initial === null)
				{
					$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->counter($key, $value);
				}
				else
				{
					$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->counter($key, $value, array('initial' => $initial));
				}
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Counter failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during counter: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function upsert($key, $value, $expiry = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->upsert($key, $value, array('expiry' => $expiry));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Upsert failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during upsert: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param bool $touch
	 * @param int $expiry
	 * @param bool $locked
	 * @param int $lockfor
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function get($key, $touch = false, $expiry = 0, $locked = false, $lockfor = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				if(!$locked)
				{
					if($touch)
					{
						$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->getAndTouch($key, $expiry);
					}
					else
					{
						$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->get($key);
					}
				}
				else
				{
					$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->getAndLock($key, $lockfor);
				}
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Get failed: "'.$key.'". | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during get: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param bool $touch
	 * @param int $expiry
	 * @param bool $locked
	 * @param int $lockfor
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function unlock($key, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->unlock($key);
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Unlock failed: "'.$key.'". | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during unlock: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $expiry
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function replace($key, $value, $expiry = 0, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->replace($key, $value, array('expiry' => $expiry));
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Replace failed: "'.$key.'" // '.serialize($value).'. | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during replace: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param int $serverId
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function remove($key, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}

			try
			{
				$result = $this->pzphp()->db()->getActiveServer($serverId)->getBucketObject()->remove($key);
			}
			catch(Exception $e)
			{
				$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Remove failed: "'.$key.'". | Error: "#'.$e->getMessage().' / '.$e->getCode().'"');

				$this->_lastErrorMsg[$serverId] = $e->getMessage();
				$this->_lastErrorNo[$serverId] = $e->getCode();
			}

			if(empty($result))
			{
				return false;
			}
			else
			{
				return $result;
			}
		}
		catch(Exception $e)
		{
			$this->_lastErrorMsg[$serverId] = $e->getMessage();
			$this->_lastErrorNo[$serverId] = $e->getCode();

			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during remove: "'.$key.' | Exception: "#'.$this->_lastErrorNo[$serverId].' / '.$this->_lastErrorMsg[$serverId].'"');

			return false;
		}
	}

	/**
	 * @param $dbName
	 * @param $serverId
	 * @return bool
	 */
	public function selectDatabase($dbName, $serverId = -1)
	{
		$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

		$this->_connect($serverId);

		return ($this->pzphp()->db()->getActiveServer($serverId)?$this->pzphp()->db()->getActiveServer($serverId)->selectDatabase($dbName):false);
	}

	/**
	 * @param $value
	 * @param bool $mustBeNumeric
	 * @param int $decimalPlaces
	 * @param int $cleanall
	 * @param $serverId
	 * @return array|float|int|mixed|string
	 */
	public function sanitize($value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $serverId = -1)
	{
		$this->_connect($serverId);

		return PzPHP_Library_Security_Cleanse::cleanQuery($this->pzphp()->db()->getActiveServer($this->pzphp()->db()->getActiveServerId($serverId))->getBucketObject(),$value,$mustBeNumeric, $decimalPlaces, $cleanall);
	}

	/**
	 * @param $serverId
	 * @return bool
	 */
	protected function _connect($serverId)
	{
		try
		{
			$serverId = $this->pzphp()->db()->getActiveServerId($serverId);

			if(!$this->pzphp()->db()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->db()->connect($serverId))
				{
					return false;
				}
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_CB_ERROR_LOG_FILE_NAME'), 'Exception during connection | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}
