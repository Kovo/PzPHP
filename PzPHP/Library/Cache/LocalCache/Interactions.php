<?php
class PzPHP_Library_Cache_LocalCache_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * @var array
	 */
	protected $_localCache = array();

	/**
	 * @param $key
	 * @return bool
	 */
	protected function _keyExists($key)
	{
		return isset($this->_localCache[$key]);
	}

	/**
	 * @param $key
	 * @param $value
	 * @param bool $deleteOnExist
	 * @return bool
	 */
	public function write($key, $value, $deleteOnExist = true)
	{
		if($this->_keyExists($key) && $deleteOnExist === false)
		{
			return false;
		}
		else
		{
			$this->_localCache[$key] = $value;

			return true;
		}
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function read($key)
	{
		if($this->_keyExists($key))
		{
			return $this->_localCache[$key];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function delete($key)
	{
		if($this->_keyExists($key))
		{
			unset($this->_localCache[$key]);

			return true;
		}
		else
		{
			return false;
		}
	}
}
