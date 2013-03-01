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
	class Pz_LocalCache_Interactions extends Pz_Abstract_Generic
	{
		/**
		 * The key / value pairs are stored in a simple array.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_localCache = array();

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
			if(isset($this->_localCache[$key]) && $deleteOnExist === false)
			{
				return false;
			}
			else
			{
				$this->_localCache[$key] = $value;

				$this->pzCore()->debugger('lcWritesInc');

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
			if(isset($this->_localCache[$key]))
			{
				$this->pzCore()->debugger('lcReadsInc');

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
			if(isset($this->_localCache[$key]))
			{
				unset($this->_localCache[$key]);

				$this->pzCore()->debugger('lcDeletesInc');

				return true;
			}
			else
			{
				return false;
			}
		}
	}
