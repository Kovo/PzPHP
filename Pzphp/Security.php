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
				$this->pzphp()->pz()->getSecurityObject()->replacePzCryptHash(unserialize(PZ_SECURITY_HASH_TABLE));
			}

			if(PZ_SECURITY_SALT !== '')
			{
				$this->pzphp()->pz()->getSecurityObject()->replacePzCryptSalt(PZ_SECURITY_SALT);
			}

			if(PZ_SECURITY_POISON_CONSTRAINTS !== '')
			{
				$this->pzphp()->pz()->getSecurityObject()->replacePzCryptPoisonConstraints(unserialize(PZ_SECURITY_POISON_CONSTRAINTS));
			}

			if(PZ_SECURITY_REHASH_DEPTH !== '')
			{
				$this->pzphp()->pz()->getSecurityObject()->replacePzCryptRehashDepth(PZ_SECURITY_REHASH_DEPTH);
			}
		}

		/**
		 * @param     $length
		 * @param int $type
		 *
		 * @return mixed
		 */
		public function createCode($length, $type = PzSecurity::ALPHANUMERIC)
		{
			return $this->pzphp()->pz()->createCode($length, $type);
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function twoWayEncrypt($value)
		{
			return $this->pzphp()->pz()->encrypt($value, array(PzSecurity::TWO_WAY));
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function twoWayDecrypt($value)
		{
			return $this->pzphp()->pz()->decrypt($value, array(PzSecurity::DE_POISON));
		}

		/**
		 * @param $value
		 *
		 * @return mixed
		 */
		public function oneWayEncrypt($value)
		{
			return $this->pzphp()->pz()->encrypt($value, array(PzSecurity::ONE_WAY));
		}

		/**
		 * @param $unhashedValue
		 * @param $hashedComparisonValue
		 *
		 * @return mixed
		 */
		public function oneWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->getSecurityObject()->compareHashes($unhashedValue, $hashedComparisonValue, array(PzSecurity::ONE_WAY));
		}

		/**
		 * @param $unhashedValue
		 * @param $hashedComparisonValue
		 *
		 * @return mixed
		 */
		public function twoWayHashComparison($unhashedValue, $hashedComparisonValue)
		{
			return $this->pzphp()->pz()->getSecurityObject()->compareHashes($unhashedValue, $hashedComparisonValue, array(PzSecurity::TWO_WAY));
		}

		/**
		 * @param     $value
		 * @param int $decimalPlaces
		 *
		 * @return mixed
		 */
		public function sanitizeNumeric($value, $decimalPlaces = 0)
		{
			return $this->pzphp()->pz()->sanitize($value, true, $decimalPlaces);
		}

		/**
		 * @param     $value
		 * @param int $cleanMethod
		 *
		 * @return mixed
		 */
		public function sanitizeString($value, $cleanMethod = PzSecurity::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			return $this->pzphp()->pz()->sanitize($value, false, 0, $cleanMethod);
		}
	}
