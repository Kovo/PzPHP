<?php
class PzPHP_Library_Cache_Memcached_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @param $key
	 * @param $value
	 * @param int $expires
	 * @param bool $deleteLock
	 * @param bool $checkFirst
	 * @param $serverId
	 * @return bool
	 */
	public function write($key, $value, $expires = 0, $deleteLock = false, $checkFirst = true, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->cache()->getActiveServerId($serverId);

			if(!$this->pzphp()->cache()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->cache()->connect($serverId))
				{
					return false;
				}
			}

			if(is_scalar($value) && !is_bool($value))
			{
				$value = (string)$value;
			}

			if($checkFirst)
			{
				$replace = $this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->replace($key, $value, $expires);

				if(!$replace)
				{
					$return = $this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->add($key, $value, $expires);
				}
				else
				{
					$return = true;
				}
			}
			else
			{
				if($this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->add($key, $value, $expires))
				{
					if($value == $this->read($key, false, $serverId))
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

			if($deleteLock)
			{
				$this->delete($key.self::LOCK_VALUE, false, $serverId);
			}

			return $return;
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MEMCACHE_ERROR_LOG_FILE_NAME'), 'Excpetion during writing of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param bool $checkLock
	 * @param $serverId
	 * @return array|bool|mixed|string
	 */
	public function read($key, $checkLock = false, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->cache()->getActiveServerId($serverId);

			if(!$this->pzphp()->cache()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->cache()->connect($serverId))
				{
					return false;
				}
			}

			if(!$checkLock)
			{
				return $this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->get($key);
			}
			else
			{
				while(!$this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), PzPHP_Config::get('SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'), false, false, $serverId))
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->read($key, false, $serverId);
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MEMCACHE_ERROR_LOG_FILE_NAME'), 'Excpetion during reading of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * @param $key
	 * @param bool $checkLock
	 * @param $serverId
	 * @return bool
	 */
	public function delete($key, $checkLock = false, $serverId = -1)
	{
		try
		{
			$serverId = $this->pzphp()->cache()->getActiveServerId($serverId);

			if(!$this->pzphp()->cache()->getActiveServer($serverId)->isConnected())
			{
				if(!$this->pzphp()->cache()->connect($serverId))
				{
					return false;
				}
			}

			if(!$checkLock)
			{
				$this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->delete($key);

				if(substr($key, -strlen(self::LOCK_VALUE)) !== self::LOCK_VALUE)
				{
					$this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->delete($key.self::LOCK_VALUE);
				}

				return true;
			}
			else
			{
				while(!$this->write($key.self::LOCK_VALUE, mt_rand(1,2000000000), PzPHP_Config::get('SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'), false, false, $serverId))
				{
					usleep(mt_rand(1000,500000));
				}

				return $this->delete($key, false, $serverId);
			}
		}
		catch(Exception $e)
		{
			$this->pzphp()->log()->add(PzPHP_Config::get('SETTING_MEMCACHE_ERROR_LOG_FILE_NAME'), 'Excpetion during deletion of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}
