<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP
	 */
	/**
	 * The security class gives you methods that allow you to create one-way, or two-way encryptions, among other things.
	 */
	class PzPHP_Security extends PzPHP_Wrapper
	{
		/**
		 * The init method takes care of applying custom security rules (if any).
		 *
		 * @access public
		 * @param PzPHP_Core $PzphpCore
		 */
		public function init(PzPHP_Core $PzphpCore)
		{
			parent::init($PzphpCore);

			if(PZ_SECURITY_HASH_TABLE !== '')
			{
				$this->pzphp()->pz()->pzSecurity()->replacePzCryptHash(unserialize(PZ_SECURITY_HASH_TABLE));
			}

			if(PZ_SECURITY_SALT !== '')
			{
				$this->pzphp()->pz()->pzSecurity()->replacePzCryptSalt(PZ_SECURITY_SALT);
			}

			if(PZ_SECURITY_POISON_CONSTRAINTS !== '')
			{
				$this->pzphp()->pz()->pzSecurity()->replacePzCryptPoisonConstraints(unserialize(PZ_SECURITY_POISON_CONSTRAINTS));
			}

			if(PZ_SECURITY_REHASH_DEPTH !== '')
			{
				$this->pzphp()->pz()->pzSecurity()->replacePzCryptRehashDepth(PZ_SECURITY_REHASH_DEPTH);
			}
		}

		/**
		 * Create a randomly generated string of characters.
		 *
		 * @access public
		 * @param int $length
		 * @param int  $type
		 * @param bool $regenerateString
		 * @return string
		 */
		public function createCode($length, $type = Pz_Helper_String::ALPHANUMERIC, $regenerateString = true)
		{
			return Pz_Helper_String::createCode($length, $type, $regenerateString);
		}

		/**
		 * Takes a string and creates a decryptable encryption.
		 *
		 * @access public
		 * @param string $value
		 * @return string
		 */
		public function twoWayEncrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->encrypt($value, array(Pz_Security::TWO_WAY));
		}

		/**
		 * Decrypts a string that was encrypted using the two-way method.
		 *
		 * @access public
		 * @param strng $value
		 * @return string
		 */
		public function twoWayDecrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->decrypt($value, array(Pz_Security::DE_POISON));
		}

		/**
		 * Creates a strong one-way encryption/hash.
		 *
		 * This will generate a 44 character long hash by default (unless you have provided custom security rules (see init())).
		 *
		 * @access public
		 * @param string $value
		 * @return string
		 */
		public function oneWayEncrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->encrypt($value, array(Pz_Security::ONE_WAY));
		}

		/**
		 * Compare a string to a one-way hash string.
		 *
		 * @access public
		 * @param string $unhashedValue
		 * @param string $hashedComparisonValue
		 * @return bool
		 */
		public function oneWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->pzSecurity()->compareHashes($unhashedValue, $hashedComparisonValue, array(Pz_Security::ONE_WAY));
		}

		/**
		 * Compare a string to a two-way encrypted string.
		 *
		 * @access public
		 * @param string $unhashedValue
		 * @param string $hashedComparisonValue
		 * @return bool
		 */
		public function twoWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->pzSecurity()->compareHashes($unhashedValue, $hashedComparisonValue, array(Pz_Security::TWO_WAY));
		}
	}
