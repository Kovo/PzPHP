<?php
class PzPHP_Controller
{
	/**
	 * @var null|PzPHP_Core
	 */
	protected $_PzPHP = NULL;

	public function before(){}

	/**
	 * @param PzPHP_Core $PzPHPCore
	 */
	function __construct(PzPHP_Core $PzPHPCore)
	{
		$this->_PzPHP = $PzPHPCore;
	}

	/**
	 * @return null|PzPHP_Core
	 */
	public function pzphp()
	{
		return $this->_PzPHP;
	}

	public function after(){}
}
