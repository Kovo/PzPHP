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
 * The Cache class gives you a layer of abstraction to access the caching options in the Pz Library.
 *
 * The Cache class is agnostic to the type of caching (if any) you have configured PzPHP to use.
 */
class PzPHP_Module_Cache extends PzPHP_Wrapper
{
	/**
	 * @var array
	 */
	protected $_servers = array();

	/**
	 * @var null|PzPHP_Library_Cache_APC_Interactions
	 */
	protected $_apcInteractions = null;

	/**
	 * @var null|PzPHP_Library_Cache_LocalCache_Interactions
	 */
	protected $_localCacheInteractions = null;

	/**
	 * @var null|PzPHP_Library_Cache_Memcache_Interactions
	 */
	protected $_memcacheInteractions = null;

	/**
	 * @var null|PzPHP_Library_Cache_Memcached_Interactions
	 */
	protected $_memcachedInteractions = null;

	/**
	 * @var null|PzPHP_Library_Cache_SHM_Interactions
	 */
	protected $_shmInteractions = null;

	/**
	 * @var int
	 */
	protected $_activeServerId = -1;

	/**
	 * @return null|PzPHP_Library_Cache_APC_Interactions
	 */
	public function apcInteract()
	{
		if($this->_apcInteractions === null)
		{
			$this->_apcInteractions = new PzPHP_Library_Cache_APC_Interactions($this->_PzPHP);
		}

		return $this->_apcInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Cache_LocalCache_Interactions
	 */
	public function localCacheInteract()
	{
		if($this->_localCacheInteractions === null)
		{
			$this->_localCacheInteractions = new PzPHP_Library_Cache_LocalCache_Interactions($this->_PzPHP);
		}

		return $this->_localCacheInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Cache_Memcache_Interactions
	 */
	public function memcacheInteract()
	{
		if($this->_memcacheInteractions === null)
		{
			$this->_memcacheInteractions = new PzPHP_Library_Cache_Memcache_Interactions($this->_PzPHP);
		}

		return $this->_memcacheInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Cache_Memcached_Interactions
	 */
	public function memcachedInteract()
	{
		if($this->_memcachedInteractions === null)
		{
			$this->_memcachedInteractions = new PzPHP_Library_Cache_Memcached_Interactions($this->_PzPHP);
		}

		return $this->_memcachedInteractions;
	}

	/**
	 * @return null|PzPHP_Library_Cache_SHM_Interactions
	 */
	public function shmInteract()
	{
		if($this->_shmInteractions === null)
		{
			$this->_shmInteractions = new PzPHP_Library_Cache_SHM_Interactions($this->_PzPHP);
		}

		return $this->_shmInteractions;
	}

	/**
	 * @param $ip
	 * @param $port
	 * @param bool $preventAutoAssign
	 * @return bool|mixed
	 * @throws PzPHP_Exception
	 */
	public function add($ip, $port, $preventAutoAssign = false)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				$this->_servers[] = new PzPHP_Library_Cache_Memcache_Server($ip, $port, PzPHP_Config::get('SETTING_CACHE_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_CACHE_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				$this->_servers[] = new PzPHP_Library_Cache_Memcached_Server($ip, $port, PzPHP_Config::get('SETTING_CACHE_CONNECT_RETRY_ATTEMPTS'), PzPHP_Config::get('SETTING_CACHE_CONNECT_RETRY_DELAY_SECONDS'));
				break;
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
			case PzPHP_Config::get('CACHE_MODE_APC'):
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
			case PzPHP_Config::get('CACHE_MODE_NO_CACHING'):
				return false;
			default:
				throw new PzPHP_Exception('Invalid cache mode provided.', PzPHP_Helper_Codes::CACHE_INVALID_MODE);
		}

		$serverId = max(array_keys($this->_servers));

		$autoAssign = (PzPHP_Config::get('SETTING_CACHE_AUTO_ASSIGN_ACTIVE_SERVER') && !$preventAutoAssign);
		if($autoAssign)
		{
			$this->setActiveServerId($serverId);
		}

		if(PzPHP_Config::get('SETTING_CACHE_AUTO_CONNECT_SERVER'))
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
			if(PzPHP_Config::get('SETTING_CACHE_AUTO_ASSIGN_ACTIVE_SERVER') && !$preventAutoAssign)
			{
				$this->setActiveServerId($serverId);
			}

			return true;
		}
		else
		{
			throw new PzPHP_Exception('Could not connect to server with id: '.$serverId, PzPHP_Helper_Codes::CACHE_CONNECT_FAILURE);
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
	 * @return PzPHP_Library_Cache_Memcache_Server|PzPHP_Library_Cache_Memcached_Server
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
			throw new PzPHP_Exception('Active server not found!', PzPHP_Helper_Codes::CACHE_NO_ACTIVE_SERVER_ID);
		}
	}

	/**
	 * @param $keyName
	 * @param $id
	 * @return array|bool|mixed|string
	 */
	public function read($keyName, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->read($keyName);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->read($keyName, false, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->read($keyName, false, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->read($keyName);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->read($keyName);
			default:
				return false;
		}
	}

	/**
	 * @param $keyName
	 * @param $id
	 * @return array|bool|mixed|string
	 */
	public function aread($keyName, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->read($keyName, true);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->read($keyName, true, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->read($keyName, true, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->read($keyName, true);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->read($keyName);
			default:
				return false;
		}
	}

	/**
	 * @param $keyName
	 * @param $value
	 * @param int $expires
	 * @param $id
	 * @return bool
	 */
	public function write($keyName, $value, $expires = 0, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->write($keyName, $value, $expires, false, true);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->write($keyName, $value, $expires, false, true, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->write($keyName, $value, $expires, false, true, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->write($keyName, $value, false, true);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->write($keyName, $value, true);
			default:
				return false;
		}
	}

	/**
	 * @param $keyName
	 * @param $value
	 * @param int $expires
	 * @param $id
	 * @return bool
	 */
	public function awrite($keyName, $value, $expires = 0, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->write($keyName, $value, $expires, true, true);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->write($keyName, $value, $expires, true, true, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->write($keyName, $value, $expires, true, true, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->write($keyName, $value, true, true);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->write($keyName, $value, true);
			default:
				return false;
		}
	}

	/**
	 * @param $keyName
	 * @param $id
	 * @return bool|mixed
	 */
	public function delete($keyName, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->delete($keyName, false);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->delete($keyName, false, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->delete($keyName, false, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->delete($keyName, false);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->delete($keyName);
			default:
				return false;
		}
	}

	/**
	 * @param $keyName
	 * @param $id
	 * @return bool|mixed
	 */
	public function adelete($keyName, $id = -1)
	{
		switch(PzPHP_Config::get('CACHING_MODE'))
		{
			case PzPHP_Config::get('CACHE_MODE_APC'):
				return $this->apcInteract()->delete($keyName, true);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHE'):
				return $this->memcacheInteract()->delete($keyName, true, $id);
			case PzPHP_Config::get('CACHE_MODE_MEMCACHED'):
				return $this->memcachedInteract()->delete($keyName, true, $id);
			case PzPHP_Config::get('CACHE_MODE_SHARED_MEMORY'):
				return $this->shmInteract()->delete($keyName, true);
			case PzPHP_Config::get('CACHE_MODE_LOCALCACHE'):
				return $this->localCacheInteract()->delete($keyName);
			default:
				return false;
		}
	}
}
