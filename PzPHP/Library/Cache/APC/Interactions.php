<?php
class PzPHP_Library_Cache_APC_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @param $key
	 * @param $value
	 * @param int $expires
	 * @param bool $deleteLock
	 * @param bool $replaceOnExist
	 * @return bool
	 */
	public function write($key, $value, $expires = 0, $deleteLock = false, $replaceOnExist = true)
	{
		if(is_scalar($value) && !is_bool($value))
		{
			$value = (string)$value;
		}

		if(apc_add($key, $value, $expires) === true)
		{
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
			$this->delete($key.self::LOCK_VALUE);
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
			return apc_fetch($key);
		}
		else
		{
			while($this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), 15, false, false) === false)
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
		if($checkLock === false)
		{
			apc_delete($key);

			if(substr($key, -7) !== self::LOCK_VALUE)
			{
				apc_delete($key.self::LOCK_VALUE);
			}

			return true;
		}
		else
		{
			while($this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), 15, false, false) === false)
			{
				usleep(mt_rand(1000,500000));
			}

			return $this->delete($key);
		}
	}
}
