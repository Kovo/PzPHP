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
 * The database class allows you to send queries to a database server, run through results, and a lot more.
 */
class PzPHP_Module_Security extends PzPHP_Wrapper
{
	public function init(PzPHP_Core $PzPHPCore)
	{
		parent::init($PzPHPCore);

		PzPHP_Library_Security_Crypt::init();
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
		return PzPHP_Library_Security_Crypt::encrypt($value, array(PzPHP_Library_Security_Crypt::TWO_WAY));
	}

	/**
	 * Decrypts a string that was encrypted using the two-way method.
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public function twoWayDecrypt($value)
	{
		return PzPHP_Library_Security_Crypt::decrypt($value, array(PzPHP_Library_Security_Crypt::DE_POISON));
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
		return PzPHP_Library_Security_Crypt::encrypt($value, array(PzPHP_Library_Security_Crypt::ONE_WAY));
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
		return PzPHP_Library_Security_Crypt::compareHashes($unhashedValue, $hashedComparisonValue, array(PzPHP_Library_Security_Crypt::ONE_WAY));
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
		return PzPHP_Library_Security_Crypt::compareHashes($unhashedValue, $hashedComparisonValue, array(PzPHP_Library_Security_Crypt::TWO_WAY));
	}
}
