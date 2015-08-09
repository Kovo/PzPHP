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

	/**
	 * @param array $array
	 * @param       $pos
	 * @param       $value
	 * @return array
	 */
	public static function insertValueAtPos(array $array, $pos, $value)
	{
		return array_slice($array, 0, $pos, true)+$value+array_slice($array, $pos, count($array)-$pos, true);
	}
}
