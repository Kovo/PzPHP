<?php
class PzPHP_Library_Cache_SHM_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @param $keyname
	 * @return string
	 */
	protected function _shmKeyToHex($keyname)
	{
		return bin2hex($keyname);
	}

	/**
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
	 * @param $value
	 * @return mixed
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
	 * @param $key
	 * @param $value
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
				$validKey = $this->_shmKeyToHex($key.self::LOCK_VALUE);

				$shm_id = @shmop_open($validKey, 'a', 0644, 0);

				if(!empty($shm_id))
				{
					shmop_delete($shm_id);
				}

				shmop_close($shm_id);
			}

			$return = true;
		}

		return $return;
	}

	/**
	 * @param $key
	 * @param bool $checkLock
	 * @return bool|mixed
	 */
	public function read($key, $checkLock = false)
	{
		$validKey = $this->_shmKeyToHex($key);

		if($checkLock === false)
		{
			$shm_id = @shmop_open($validKey, 'a', 0644, 0);

			if(!empty($shm_id))
			{
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
			while($this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), false, false) === false)
			{
				usleep(mt_rand(1000,500000));
			}

			return $this->read($key);
		}
	}

	/**
	 * @param $key
	 * @param bool $checkLock
	 * @return bool
	 */
	public function delete($key, $checkLock = false)
	{
		$validKey = $this->_shmKeyToHex($key);

		if($checkLock === false)
		{
			$shm_id = @shmop_open($validKey, 'a', 0644, 0);

			if(!empty($shm_id))
			{
				shmop_delete($shm_id);

				shmop_close($shm_id);

				if(substr($key, -strlen(self::LOCK_VALUE)) !== self::LOCK_VALUE)
				{
					$validKey = $this->_shmKeyToHex($key.self::LOCK_VALUE);

					$shm_id = @shmop_open($validKey, 'a', 0644, 0);

					if(!empty($shm_id))
					{
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
			while($this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), false, false) === false)
			{
				usleep(mt_rand(1000,500000));
			}

			return $this->delete($key);
		}
	}
}
