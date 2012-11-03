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
	 * @package Pz_APC_Interactions
	 */
	class Pz_APC_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * @param      $key
		 * @param      $value
		 * @param int  $expires
		 * @param bool $deleteLock
		 * @param bool $deleteOnExist
		 *
		 * @return bool
		 */
		public function write($key, $value, $expires = 0, $deleteLock = false, $deleteOnExist = true)
		{
			if(apc_add($key, (is_scalar($value)?(string)$value:$value), $expires) === true)
			{
				$this->pzCore()->debugger('apcWritesInc');

				if((is_scalar($value)?(string)$value:$value) == $this->read($key))
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
				if($deleteOnExist === true)
				{
					$this->delete($key, true);

					$return = $this->write($key, $value, $expires, $deleteLock);
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
		 * @param $key
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
		 * @param      $key
		 * @param bool $checkLock
		 *
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
