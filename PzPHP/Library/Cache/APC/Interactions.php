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
	 * This class allows you to interact with APC.
	 */
	class Pz_APC_Interactions
	{
		/**
		 * Writes a value to the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param mixed $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $replaceOnExist
		 * @return bool
		 */
		public function write($key, $value, $expires = 0, $deleteLock = false, $replaceOnExist = true)
		{
			if(is_scalar($value))
			{
				$value = (string)$value;
			}

			if(apc_add($key, $value, $expires) === true)
			{
				$this->pzCore()->debugger('apcWritesInc');

				if($value == $this->read($key))
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
				if($replaceOnExist === true)
				{
					apc_delete($key);
					$return = apc_add($key, $value, $expires);
				}
				else
				{
					$return = false;
				}
			}

			if($deleteLock === true)
			{
				$this->delete($key.'_pzLock');
			}

			return $return;
		}

		/**
		 * Reads a value from the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function read($key, $checkLock = false)
		{
			if($checkLock === false)
			{
				$this->pzCore()->debugger('apcReadsInc');

				return apc_fetch($key);
			}
			else
			{
				while($this->write($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->read($key);
			}
		}

		/**
		 * Deletes a value from the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function delete($key, $checkLock = false)
		{
			if($checkLock === false)
			{
				apc_delete($key);

				$this->pzCore()->debugger('apcDeletesInc');

				if(substr($key, -7) !== '_pzLock')
				{
					apc_delete($key.'_pzLock');

					$this->pzCore()->debugger('apcDeletesInc');
				}

				return true;
			}
			else
			{
				while($this->write($key.'_pzLock', mt_rand(1,2000000000), 15, false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->delete($key);
			}
		}
	}
