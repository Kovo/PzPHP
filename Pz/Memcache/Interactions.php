<?php
	/**
	 * Contributions by:
	 *      Fayez Awad
	 *      Yann Madeleine (http://www.yann-madeleine.com)
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package Pz Library
	 */
	/**
	 * Interaction class for dealing with memcache using memcache.
	 */
	class Pz_Memcache_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * Writes a value to the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param mixed $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $checkFirst
		 * @param int $id
		 * @return bool
		 */
		public function write($key, $value, $expires = 0, $deleteLock = false, $checkFirst = true, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMemcacheId($id);

			if($this->pzCore()->memcacheActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcacheActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcacheConnect($id) === false)
					{
						return false;
					}
				}

				if(is_scalar($value))
				{
					$value = (string)$value;
				}

				if($checkFirst === true)
				{
					$replace = $this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->replace($key, $value, MEMCACHE_COMPRESSED, $expires);

					$this->pzCore()->debugger('mcWritesInc');

					if($replace === false)
					{
						$return = $this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->add($key, $value, MEMCACHE_COMPRESSED, $expires);

						$this->pzCore()->debugger('mcWritesInc');
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					if($this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->add($key, $value, MEMCACHE_COMPRESSED, $expires) === true)
					{
						$this->pzCore()->debugger('mcWritesInc');

						if($value == $this->read($key, false, $id))
						{
							$return = true;
						}
						else
						{
							$return = false;
						}
					}
					else
					{
						$return = false;
					}
				}

				if($deleteLock === true)
				{
					$this->delete($key.'_pzLock', false, $id);
				}

				return $return;
			}
		}

		/**
		 * Get value from the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @param int $id
		 * @return array|bool|string
		 */
		public function read($key, $checkLock = false, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMemcacheId($id);

			if($this->pzCore()->memcacheActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcacheActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcacheConnect($id) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->pzCore()->debugger('mcReadsInc');

					return $this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->get($key);
				}
				else
				{
					while($this->write($key.'_pzLock', mt_rand(1,2000000000), 15, false, false, $id) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->read($key, false, $id);
				}
			}
		}

		/**
		 * Delete value from cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @param int $id
		 * @return bool
		 */
		public function delete($key, $checkLock = false, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMemcacheId($id);

			if($this->pzCore()->getActiveMemcacheServerId($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcacheActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcacheConnect($id) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->delete($key);

					$this->pzCore()->debugger('mcDeletesInc');

					if(substr($key, -7) !== '_pzLock')
					{
						$this->pzCore()->memcacheActiveObject($id)->returnMemcacheObj()->delete($key.'_pzLock');

						$this->pzCore()->debugger('mcDeletesInc');
					}

					return true;
				}
				else
				{
					while($this->write($key.'_pzLock', mt_rand(1,2000000000), 15, false, false, $id) === false)
					{
						usleep(mt_rand(1000,500000));
					}

					return $this->delete($key, false, $id);
				}
			}
		}
	}
