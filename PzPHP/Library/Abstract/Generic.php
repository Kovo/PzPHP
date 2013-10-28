<?php
abstract class PzPHP_Library_Abstract_Generic
{
	/**
	 * @var null|PzPHP_Core
	 */
	protected $_PzPHPCore = null;

	/**
	 * @param PzPHP_Core $PzPHPCore
	 */
	function __construct(PzPHP_Core $PzPHPCore)
	{
		$this->init($PzPHPCore);
	}

	/**
	 * @param PzPHP_Core $PzPHPCore
	 */
	public function init(PzPHP_Core $PzPHPCore)
	{
		$this->_PzPHPCore = $PzPHPCore;
	}

	/**
	 * @return null|PzPHP_Core
	 */
	public function pzphp()
	{
		return $this->_PzPHPCore;
	}
}
