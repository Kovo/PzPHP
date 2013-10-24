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
	class Pz_LocalCache_Interactions
	{
		/**
		 * The key / value pairs are stored in a simple array.
		 *
		 * @access private
		 * @var array
		 */
		protected $_localCacheA = array();
		protected $_localCacheB = array();
		protected $_localCacheC = array();
		protected $_localCacheD = array();
		protected $_localCacheE = array();
		protected $_localCacheF = array();
		protected $_localCacheG = array();
		protected $_localCacheH = array();
		protected $_localCacheI = array();
		protected $_localCacheJ = array();
		protected $_localCacheK = array();
		protected $_localCacheL = array();
		protected $_localCacheM = array();
		protected $_localCacheN = array();
		protected $_localCacheO = array();
		protected $_localCacheP = array();
		protected $_localCacheQ = array();
		protected $_localCacheR = array();
		protected $_localCacheS = array();
		protected $_localCacheT = array();
		protected $_localCacheU = array();
		protected $_localCacheV = array();
		protected $_localCacheW = array();
		protected $_localCacheX = array();
		protected $_localCacheY = array();
		protected $_localCacheZ = array();
		protected $_localCache0 = array();
		protected $_localCache1 = array();
		protected $_localCache2 = array();
		protected $_localCache3 = array();
		protected $_localCache4 = array();
		protected $_localCache5 = array();
		protected $_localCache6 = array();
		protected $_localCache7 = array();
		protected $_localCache8 = array();
		protected $_localCache9 = array();
		protected $_localCache_ = array();

		/**
		 * @param $key
		 *
		 * @return bool
		 */
		protected function _keyExists($key)
		{
			$cahcheArray = &$this->_returnProperArray($key);

			if(isset($cahcheArray[$key]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param $key
		 *
		 * @return array
		 */
		protected function &_returnProperArray($key)
		{
			$firstLetter = strtoupper(substr($key,0,1));

			switch($firstLetter)
			{
				case 'A':
					return $this->_localCacheA;
				case 'B':
					return $this->_localCacheB;
				case 'C':
					return $this->_localCacheC;
				case 'D':
					return $this->_localCacheD;
				case 'E':
					return $this->_localCacheE;
				case 'F':
					return $this->_localCacheF;
				case 'G':
					return $this->_localCacheG;
				case 'H':
					return $this->_localCacheH;
				case 'I':
					return $this->_localCacheI;
				case 'J':
					return $this->_localCacheJ;
				case 'K':
					return $this->_localCacheK;
				case 'L':
					return $this->_localCacheL;
				case 'M':
					return $this->_localCacheM;
				case 'N':
					return $this->_localCacheN;
				case 'O':
					return $this->_localCacheO;
				case 'P':
					return $this->_localCacheP;
				case 'Q':
					return $this->_localCacheQ;
				case 'R':
					return $this->_localCacheR;
				case 'S':
					return $this->_localCacheS;
				case 'T':
					return $this->_localCacheT;
				case 'U':
					return $this->_localCacheU;
				case 'V':
					return $this->_localCacheV;
				case 'W':
					return $this->_localCacheW;
				case 'X':
					return $this->_localCacheX;
				case 'Y':
					return $this->_localCacheY;
				case 'Z':
					return $this->_localCacheZ;
				case '0':
					return $this->_localCache0;
				case '1':
					return $this->_localCache1;
				case '2':
					return $this->_localCache2;
				case '3':
					return $this->_localCache3;
				case '4':
					return $this->_localCache4;
				case '5':
					return $this->_localCache5;
				case '6':
					return $this->_localCache6;
				case '7':
					return $this->_localCache7;
				case '8':
					return $this->_localCache8;
				case '9':
					return $this->_localCache9;
				default:
					return $this->_localCache_;
			}
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
				$cacheArray = &$this->_returnProperArray($key);
				$cacheArray[$key] = $value;

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
			if($this->_keyExists($key))
			{
				$this->pzCore()->debugger('lcReadsInc');

				$cacheArray = &$this->_returnProperArray($key);

				return $cacheArray[$key];
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
				$cacheArray = &$this->_returnProperArray($key);

				unset($cacheArray[$key]);

				$this->pzCore()->debugger('lcDeletesInc');

				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param string $keysStartingWith
		 */
		public function deleteFromCache($keysStartingWith = '')
		{
			if($keysStartingWith === '')
			{
				$this->_localCacheA = array();
				$this->_localCacheB = array();
				$this->_localCacheC = array();
				$this->_localCacheD = array();
				$this->_localCacheE = array();
				$this->_localCacheF = array();
				$this->_localCacheG = array();
				$this->_localCacheH = array();
				$this->_localCacheI = array();
				$this->_localCacheJ = array();
				$this->_localCacheK = array();
				$this->_localCacheL = array();
				$this->_localCacheM = array();
				$this->_localCacheN = array();
				$this->_localCacheO = array();
				$this->_localCacheP = array();
				$this->_localCacheQ = array();
				$this->_localCacheR = array();
				$this->_localCacheS = array();
				$this->_localCacheT = array();
				$this->_localCacheU = array();
				$this->_localCacheV = array();
				$this->_localCacheW = array();
				$this->_localCacheX = array();
				$this->_localCacheY = array();
				$this->_localCacheZ = array();
				$this->_localCache0 = array();
				$this->_localCache1 = array();
				$this->_localCache2 = array();
				$this->_localCache3 = array();
				$this->_localCache4 = array();
				$this->_localCache5 = array();
				$this->_localCache6 = array();
				$this->_localCache7 = array();
				$this->_localCache8 = array();
				$this->_localCache9 = array();
				$this->_localCache_ = array();
			}
			else
			{
				$cahcheArray = &$this->_returnProperArray($keysStartingWith);

				if(!empty($cahcheArray))
				{
					$keySegmentLength = strlen($keysStartingWith);
					foreach($cahcheArray as $key => $value)
					{
						if(substr($key,0,$keySegmentLength) === $keysStartingWith)
						{
							unset($cahcheArray[$key]);
						}
					}
				}
			}
		}
	}
