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
	 * Interaction class for dealing with memcache using memcached.
	 */
	class Pz_Memcached_Interactions extends Pz_Abstract_Generic
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
			$id = $this->pzCore()->decideActiveMemcachedId($id);

			if($this->pzCore()->memcachedActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcachedActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcachedConnect($id) === false)
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
					$replace = $this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->replace($key, $value, $expires);

					$this->pzCore()->debugger('mcdWritesInc');

					if($replace === false)
					{
						$return = $this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->add($key, $value, $expires);

						$this->pzCore()->debugger('mcdWritesInc');
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					if($this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->add($key, $value, $expires) === true)
					{
						$this->pzCore()->debugger('mcdWritesInc');

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
		 * Reads a value from the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @param int $id
		 * @return bool|mixed
		 */
		public function read($key, $checkLock = false, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMemcachedId($id);

			if($this->pzCore()->memcachedActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcachedActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcachedConnect($id) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->pzCore()->debugger('mcdReadsInc');

					return $this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->get($key);
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
		 * Deletes a value from the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @param int $id
		 * @return bool
		 */
		public function delete($key, $checkLock = false, $id = -1)
		{
			$id = $this->pzCore()->decideActiveMemcachedId($id);

			if($this->pzCore()->memcachedActiveObject($id) === false)
			{
				return false;
			}
			else
			{
				if($this->pzCore()->memcachedActiveObject($id)->isConnected() === false)
				{
					if($this->pzCore()->memcachedConnect($id) === false)
					{
						return false;
					}
				}

				if($checkLock === false)
				{
					$this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->delete($key);

					$this->pzCore()->debugger('mcdDeletesInc');

					if(substr($key, -7) !== '_pzLock')
					{
						$this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->delete($key.'_pzLock');

						$this->pzCore()->debugger('mcdDeletesInc');
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
