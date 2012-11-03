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
	 * @package Pz_Memcached_Interactions
	 */
	class Pz_Memcached_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * @param      $key
		 * @param      $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $checkFirst
		 * @param      $id
		 *
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

				if($checkFirst === true)
				{
					$replace = $this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->replace($key, (is_scalar($value)?(string)$value:$value), $expires);

					$this->pzCore()->debugger('mcdWritesInc');

					if($replace === false)
					{
						$return = $this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->add($key, (is_scalar($value)?(string)$value:$value), $expires);

						$this->pzCore()->debugger('mcdWritesInc');
					}
					else
					{
						$return = true;
					}
				}
				else
				{
					if($this->pzCore()->memcachedActiveObject($id)->returnMemcachedObj()->add($key, (is_scalar($value)?(string)$value:$value), $expires) === true)
					{
						$this->pzCore()->debugger('mcdWritesInc');

						if((is_scalar($value)?(string)$value:$value) == $this->read($key, false, $id))
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
		 * @param      $key
		 * @param bool $checkLock
		 * @param      $id
		 *
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
		 * @param      $key
		 * @param bool $checkLock
		 * @param      $id
		 *
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
