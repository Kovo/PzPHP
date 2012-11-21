<?php
	/**
	 * Website: http://www.pzphp.com
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzPHP_Security
	 */
	class PzPHP_Security extends PzPHP_Wrapper
	{
		/**
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
		 * @param      $length
		 * @param int  $type
		 * @param bool $regenerateString
		 *
		 * @return string
		 */
		public function createCode($length, $type = Pz_Helper_String::ALPHANUMERIC, $regenerateString = true)
		{
			return Pz_Helper_String::createCode($length, $type, $regenerateString);
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function twoWayEncrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->encrypt($value, array(Pz_Security::TWO_WAY));
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function twoWayDecrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->decrypt($value, array(Pz_Security::DE_POISON));
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function oneWayEncrypt($value)
		{
			return $this->pzphp()->pz()->pzSecurity()->encrypt($value, array(Pz_Security::ONE_WAY));
		}

		/**
		 * @param $unhashedValue
		 * @param $hashedComparisonValue
		 *
		 * @return mixed
		 */
		public function oneWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->pzSecurity()->compareHashes($unhashedValue, $hashedComparisonValue, array(Pz_Security::ONE_WAY));
		}

		/**
		 * @param $unhashedValue
		 * @param $hashedComparisonValue
		 *
		 * @return mixed
		 */
		public function twoWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->pzSecurity()->compareHashes($unhashedValue, $hashedComparisonValue, array(Pz_Security::TWO_WAY));
		}
	}
