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
 * A helper class that allows you to manipulate or read arrays in useful ways that PHP does not natively support.
 */
class PzPHP_Helper_Array
{
	/**
	 * Sorts an array by a specific key.
	 *
	 * @static
	 * @access public
	 * @param array $sourceArray
	 * @param string|int|float $keyToSortBy
	 * @param int  $sortType
	 * @param bool $sortAscending
	 */
	public static function aasort(&$sourceArray, $keyToSortBy, $sortType = SORT_REGULAR, $sortAscending = true)
	{
		$temporaryArray = array();
		$replacementArray = array();

		reset($sourceArray);

		foreach($sourceArray as $index => $value)
		{
			$temporaryArray[$index] = $value[$keyToSortBy];
		}

		if($sortAscending)
		{
			asort($temporaryArray, $sortType);
		}
		else
		{
			arsort($temporaryArray, $sortType);
		}

		foreach($temporaryArray as $index => $value)
		{
			$replacementArray[$index] = $sourceArray[$index];
		}

		$sourceArray = $replacementArray;
	}
}
