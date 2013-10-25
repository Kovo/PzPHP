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
class PzPHP_Library_Cache_Memcache_Interactions extends PzPHP_Library_Abstract_Interactions
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
	 * @param int $serverId
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

			if(is_scalar($value))
			{
				$value = (string)$value;
			}

			if($checkFirst)
			{
				$replace = $this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->replace($key, $value, MEMCACHE_COMPRESSED, $expires);

				if(!$replace)
				{
					$return = $this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->add($key, $value, MEMCACHE_COMPRESSED, $expires);
				}
				else
				{
					$return = true;
				}
			}
			else
			{
				if($this->pzphp()->cache()->getActiveServer($serverId)->getCacheObject()->add($key, $value, MEMCACHE_COMPRESSED, $expires))
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
	 * Get value from the cache.
	 *
	 * @access public
	 * @param string $key
	 * @param bool $checkLock
	 * @param int $serverId
	 * @return array|bool|string
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
	 * Delete value from cache.
	 *
	 * @access public
	 * @param string $key
	 * @param bool $checkLock
	 * @param int $serverId
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

				if(substr($key, -7) !== self::LOCK_VALUE)
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
