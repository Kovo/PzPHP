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
	class PzPHP_Cache extends PzPHP_Wrapper
	{
		/**
		 * The established caching method (if any).
		 *
		 * @access private
		 * @var int
		 */
		private $_cachingMethod = PZPHP_CACHE_MODE_NO_CACHING;

		/**
		 * Flag if caching is enabled or not.
		 *
		 * @access private
		 * @var bool
		 */
		private $_cachingEnabled = false;

		/**
		 * The contstruct verifies if caching is enabled, and sets it, if it is.
		 */
		function __construct()
		{
			$this->_verifyIfCachingEnabled();

			$this->setCachingMethod();
		}

		/**
		 * Verifies if caching is enabled, and sets the flag accordingly.
		 *
		 * @access private
		 */
		private function _verifyIfCachingEnabled()
		{
			if(PZPHP_CACHING_MODE !== PZPHP_CACHE_MODE_NO_CACHING)
			{
				$this->_cachingEnabled = true;
			}
		}

		/**
		 * Sets the chosen caching method locally, and then returns its identifier.
		 *
		 * @access public
		 * @param int $method
		 * @return int
		 */
		public function setCachingMethod($method = PZPHP_CACHING_MODE)
		{
			switch($method)
			{
				case PZPHP_CACHE_MODE_APC:
					$this->_cachingMethod = PZPHP_CACHE_MODE_APC;
					break;
				case PZPHP_CACHE_MODE_MEMCACHE:
					$this->_cachingMethod = PZPHP_CACHE_MODE_MEMCACHE;
					break;
				case PZPHP_CACHE_MODE_MEMCACHED:
					$this->_cachingMethod = PZPHP_CACHE_MODE_MEMCACHED;
					break;
				case PZPHP_CACHE_MODE_SHARED_MEMORY:
					$this->_cachingMethod = PZPHP_CACHE_MODE_SHARED_MEMORY;
					break;
				case PZPHP_CACHE_MODE_LOCALCACHE:
					$this->_cachingMethod = PZPHP_CACHE_MODE_LOCALCACHE;
					break;
				default:
					$this->_cachingMethod = PZPHP_CACHE_MODE_NO_CACHING;
			}

			return $this->_cachingMethod;
		}

		/**
		 * Returns true or false depending on if caching is enabled or not.
		 *
		 * @access public
		 * @return bool
		 */
		public function cacheEnabled()
		{
			return $this->_cachingEnabled;
		}

		/**
		 * Returns the identifier for the current caching method.
		 *
		 * @access public
		 * @return int
		 */
		public function cacheMethod()
		{
			return $this->_cachingMethod;
		}

		/**
		 * Add a cache server (if using mecache).
		 *
		 * @access public
		 * @param string $mcIp
		 * @param int|string $mcPort
		 * @return mixed
		 */
		public function addServer($mcIp, $mcPort)
		{
			if($this->cacheEnabled())
			{
				switch($this->cacheMethod())
				{
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->addMemcacheServer($mcIp, $mcPort);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->addMemcachedServer($mcIp, $mcPort);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache read without a lock check and set.
		 *
		 * Reads from the cache without setting a lock, or checking for one.
		 *
		 * @access public
		 * @param string $keyName
		 * @return bool|mixed
		 */
		public function read_il($keyName)
		{
			if($this->cacheEnabled())
			{
				switch($this->cacheMethod())
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->read($keyName);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->read($keyName);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->read($keyName);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->read($keyName);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->read($keyName);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache read with a lock check and set.
		 *
		 * Reads from the cache, checking for a lock first (and waiting if one is present), and then sets a lock once the previous lock (if any) is removed.
		 *
		 * @access public
		 * @param $keyName
		 * @return bool|mixed
		 */
		public function read_csl($keyName)
		{
			if($this->cacheEnabled())
			{
				switch($this->cacheMethod())
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->read($keyName, true);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->read($keyName, true);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->read($keyName, true);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->read($keyName, true);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->read($keyName);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache write without a lock delete.
		 *
		 * Writes to the cache without deleting a lock on the specified key.
		 *
		 * @access public
		 * @param string $keyName
		 * @param mixed $value
		 * @param int $expires
		 * @return bool|mixed
		 */
		public function write_ddl($keyName, $value, $expires = 0)
		{
			if($this->cacheEnabled())
			{
				switch($this->_cachingMethod)
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->write($keyName, $value, $expires, false, true);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->write($keyName, $value, $expires, false, true);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->write($keyName, $value, $expires, false, true);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->write($keyName, $value, false, true);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->write($keyName, $value, true);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache write with a lock delete.
		 *
		 * Writes to the cache as well as deletes a lock (if present) on the specified key.
		 *
		 * @access public
		 * @param string $keyName
		 * @param mixed $value
		 * @param int $expires
		 * @return bool|mixed
		 */
		public function write_dl($keyName, $value, $expires = 0)
		{
			if($this->cacheEnabled())
			{
				switch($this->_cachingMethod)
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->write($keyName, $value, $expires, true, true);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->write($keyName, $value, $expires, true, true);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->write($keyName, $value, $expires, true, true);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->write($keyName, $value, true, true);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->write($keyName, $value, true);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache delete without a lock check.
		 *
		 * Deletes key from the cache without checking for a lock.
		 *
		 * @access public
		 * @param string $keyName
		 * @return bool|mixed
		 */
		public function delete_nlc($keyName)
		{
			if($this->cacheEnabled())
			{
				switch($this->_cachingMethod)
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->delete($keyName, false);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->delete($keyName, false);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->delete($keyName, false);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->delete($keyName, false);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->delete($keyName);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Cache delete with a lock check.
		 *
		 * Deletes key from the cache, checking for a lock first before deleting.
		 *
		 * @access public
		 * @param string $keyName
		 * @return bool|mixed
		 */
		public function delete_lc($keyName)
		{
			if($this->cacheEnabled())
			{
				switch($this->_cachingMethod)
				{
					case PZPHP_CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcInteract()->delete($keyName, true);
					case PZPHP_CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->memcacheInteract()->delete($keyName, true);
					case PZPHP_CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->memcachedInteract()->delete($keyName, true);
					case PZPHP_CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmInteract()->delete($keyName, true);
					case PZPHP_CACHE_MODE_LOCALCACHE:
						return $this->pzphp()->pz()->lcInteract()->delete($keyName);
					default:
						return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
