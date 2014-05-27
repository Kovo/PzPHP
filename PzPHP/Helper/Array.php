<?php
class PzPHP_Helper_Array
{
	/**
	 * @param $sourceArray
	 * @param $keyToSortBy
	 * @param int $sortType
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
