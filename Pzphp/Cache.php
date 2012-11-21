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
		 * @var int
		 */
		private $_cachingMethod = PZPHP_CACHE_MODE_NO_CACHING;

		/**
		 * @var bool
		 */
		private $_cachingEnabled = false;

		function __construct()
		{
			$this->_verifyIfCachingEnabled();

			$this->setCachingMethod();
		}

		private function _verifyIfCachingEnabled()
		{
			if(PZPHP_CACHING_MODE !== PZPHP_CACHE_MODE_NO_CACHING)
			{
				$this->_cachingEnabled = true;
			}
		}

		/**
		 * @param int $method
		 *
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
		 * @return bool
		 */
		public function cacheEnabled()
		{
			return $this->_cachingEnabled;
		}

		/**
		 * @return int
		 */
		public function cacheMethod()
		{
			return $this->_cachingMethod;
		}

		/**
		 * @param $mcIp
		 * @param $mcPort
		 *
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
		 * @param $keyName
		 *
		 * @return bool|mixed
		 *
		 * Cache read without a lock check and set
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
		 * @param $keyName
		 *
		 * @return bool|mixed
		 *
		 * Cache read with a lock check and set
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
		 * @param     $keyName
		 * @param     $value
		 * @param int $expires
		 *
		 * @return bool|mixed
		 *
		 * Cache write without a lock delete
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
		 * @param     $keyName
		 * @param     $value
		 * @param int $expires
		 *
		 * @return bool|mixed
		 *
		 * Cache write with a lock delete
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
		 * @param $keyName
		 *
		 * @return bool|mixed
		 *
		 * Cache delete without a lock check
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
		 * @param $keyName
		 *
		 * @return bool|mixed
		 *
		 * Cache delete with a lock check
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
