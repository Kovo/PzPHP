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
 * Interaction class for dealing with the local cache system.
 */
class PzPHP_Library_Cache_LocalCache_Interactions extends PzPHP_Library_Abstract_Interactions
{
	/**
	 * The key / value pairs are stored in a simple array.
	 *
	 * @access private
	 * @var array
	 */
	protected $_localCache = array();

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected function _keyExists($key)
	{
		return isset($this->_localCache[$key]);
	}

	/**
	 * Writes a value to the cache.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
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
	 * Gets a value from the cache.
	 *
	 * @access public
	 * @param string $key
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
	 * Deletes a value from the cache.
	 *
	 * @access public
	 * @param string $key
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
