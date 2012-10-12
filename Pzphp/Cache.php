<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP_Cache
	 */
	class PzPHP_Cache extends PzPHP_Wrapper
	{
		/**
		 * @var int
		 */
		private $_cachingMethod = CACHE_MODE_NO_CACHING;

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
			if(CACHING_MODE !== CACHE_MODE_NO_CACHING)
			{
				$this->_cachingEnabled = true;
			}
		}

		/**
		 * @param int $method
		 *
		 * @return int
		 */
		public function setCachingMethod($method = CACHING_MODE)
		{
			switch($method)
			{
				case CACHE_MODE_APC:
					$this->_cachingMethod = CACHE_MODE_APC;
					break;
				case CACHE_MODE_MEMCACHE:
					$this->_cachingMethod = CACHE_MODE_MEMCACHE;
					break;
				case CACHE_MODE_MEMCACHED:
					$this->_cachingMethod = CACHE_MODE_MEMCACHED;
					break;
				case CACHE_MODE_SHARED_MEMORY:
					$this->_cachingMethod = CACHE_MODE_SHARED_MEMORY;
					break;
				default:
					$this->_cachingMethod = CACHE_MODE_NO_CACHING;
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
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->addMemcacheServer($mcIp, $mcPort);
					case CACHE_MODE_MEMCACHED:
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcRead($keyName);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcRead($keyName);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdRead($keyName);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmRead($keyName);
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcRead($keyName, true);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcRead($keyName, true);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdRead($keyName, true);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmRead($keyName, true);
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcWrite($keyName, $value, $expires, false, true);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcWrite($keyName, $value, $expires, false, true);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdWrite($keyName, $value, $expires, false, true);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmWrite($keyName, $value, false, true);
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcWrite($keyName, $value, $expires, true, true);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcWrite($keyName, $value, $expires, true, true);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdWrite($keyName, $value, $expires, true, true);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmWrite($keyName, $value, true, true);
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcDelete($keyName, false);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcDelete($keyName, false);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdDelete($keyName, false);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmDelete($keyName, false);
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
					case CACHE_MODE_APC:
						return $this->pzphp()->pz()->apcDelete($keyName, true);
					case CACHE_MODE_MEMCACHE:
						return $this->pzphp()->pz()->mcDelete($keyName, true);
					case CACHE_MODE_MEMCACHED:
						return $this->pzphp()->pz()->mcdDelete($keyName, true);
					case CACHE_MODE_SHARED_MEMORY:
						return $this->pzphp()->pz()->shmDelete($keyName, true);
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
