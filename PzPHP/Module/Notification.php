<?php
class PzPHP_Module_Notification extends PzPHP_Wrapper
{
	const LEVEL_INFO = 1;
	const LEVEL_ERROR = 2;
	const LEVEL_DEBUG = 3;

	const CACHE_KEY_NAME = 'PZPHP_NOTIF_ARRAY';

	/**
	 * @param $msg
	 * @param $level
	 * @param $cacheServId
	 *
	 * @return bool
	 */
	public function push($msg, $level, $cacheServId = -1)
	{
		$value = $this->pzphp()->cache()->aread(self::CACHE_KEY_NAME, $cacheServId);
		if($value === false)
		{
			$value = array();
		}

		$value[$level][] = $msg;

		return $this->pzphp()->cache()->awrite(self::CACHE_KEY_NAME, $value, 0, $cacheServId);
	}

	/**
	 * @param $cacheServId
	 *
	 * @return array|bool|mixed|string
	 */
	public function get($cacheServId = -1)
	{
		$value = $this->pzphp()->cache()->aread(self::CACHE_KEY_NAME, $cacheServId);
		$this->pzphp()->cache()->deleteLock(self::CACHE_KEY_NAME, $cacheServId);

		return $value;
	}

	/**
	 * @param $cacheServId
	 *
	 * @return array|bool|mixed|string
	 */
	public function getAndClear($cacheServId = -1)
	{
		$value = $this->get($cacheServId);
		$this->pzphp()->cache()->delete(self::CACHE_KEY_NAME, $cacheServId);

		return $value;
	}
}
