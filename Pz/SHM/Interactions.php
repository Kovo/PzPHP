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
	 * This class allows you to interact with the shared memory cache.
	 */
	class Pz_SHM_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * Converts key to a valid hex value (for proper storage with shmop).
		 *
		 * @access protected
		 * @param string $keyname
		 * @return string
		 */
		protected function _shmKeyToHex($keyname)
		{
			return bin2hex($keyname);
		}

		/**
		 * Converts a value to be stored to a string, or a serialized string.
		 *
		 * @access protected
		 * @param $value
		 * @return string
		 */
		protected function _shmValueToString($value)
		{
			if(is_scalar($value))
			{
				return (string)$value;
			}
			else
			{
				return serialize($value);
			}
		}

		/**
		 * Returns a stringback to its original type.
		 *
		 * @access protected
		 * @param $value
		 * @return string
		 */
		protected function _shmStringToValue($value)
		{
			$validValue = @unserialize($value);

			if($validValue !== false)
			{
				return $validValue;
			}
			else
			{
				return $value;
			}
		}

		/**
		 * Writes a value to the cache.
		 *
		 * @access public
		 * @param string $key
		 * @param mixed $value
		 * @param bool $deleteLock
		 * @param bool $deleteOnExist
		 * @return bool
		 */
		public function write($key, $value, $deleteLock = false, $deleteOnExist = true)
		{
			$validKey = $this->_shmKeyToHex($key);
			$validValue = $this->_shmValueToString($value);

			$shm_id = @shmop_open($validKey, 'a', 0644, 0);

			if(!empty($shm_id))
			{
				if($deleteOnExist === true)
				{
					$this->pzCore()->debugger('shmDeletesInc');

					shmop_delete($shm_id);

					shmop_close($shm_id);

					$return = $this->write($key, $value, $deleteLock, $deleteOnExist);
				}
				else
				{
					$return = false;
				}
			}
			else
			{
				$shm_id = shmop_open($validKey, "c", 0644, strlen($validValue));
				shmop_write($shm_id, $validValue, 0);

				shmop_close($shm_id);

				if($deleteLock === true)
				{
					$validKey = $this->_shmKeyToHex($key.'_pzLock');

					$shm_id = @shmop_open($validKey, 'a', 0644, 0);

					if(!empty($shm_id))
					{
						$this->pzCore()->debugger('shmDeletesInc');

						shmop_delete($shm_id);
					}

					shmop_close($shm_id);
				}

				$return = true;
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
			$validKey = $this->_shmKeyToHex($key);

			if($checkLock === false)
			{
				$shm_id = @shmop_open($validKey, 'a', 0644, 0);

				if(!empty($shm_id))
				{
					$this->pzCore()->debugger('shmReadsInc');

					$data = shmop_read($shm_id, 0, shmop_size($shm_id));

					shmop_close($shm_id);

					return $this->_shmStringToValue($data);
				}
				else
				{
					return false;
				}
			}
			else
			{
				while($this->write($key.'_pzLock', mt_rand(1,2000000000), false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->read($key);
			}
		}

		/**
		 * Deletes a value from the cache.
		 *
		 * @qccess public
		 * @param string $key
		 * @param bool $checkLock
		 * @return mixed
		 */
		public function delete($key, $checkLock = false)
		{
			$validKey = $this->_shmKeyToHex($key);

			if($checkLock === false)
			{
				$shm_id = @shmop_open($validKey, 'a', 0644, 0);

				if(!empty($shm_id))
				{
					$this->pzCore()->debugger('shmDeletesInc');

					shmop_delete($shm_id);

					shmop_close($shm_id);

					if(substr($key, -7) !== '_pzLock')
					{
						$validKey = $this->_shmKeyToHex($key.'_pzLock');

						$shm_id = @shmop_open($validKey, 'a', 0644, 0);

						if(!empty($shm_id))
						{
							$this->pzCore()->debugger('shmDeletesInc');

							shmop_delete($shm_id);

							shmop_close($shm_id);
						}
					}

					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				while($this->write($key.'_pzLock', mt_rand(1,2000000000), false, false) === false)
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->delete($key);
			}
		}
	}
