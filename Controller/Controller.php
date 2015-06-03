<?php
class Controller_Controller
{
	/**
	 * @var null|PzPHP_Core
	 */
	protected $_PZPHP = null;

	/**
	 * @param PzPHP_Core $_PZPHP
	 */
	function __construct(PzPHP_Core $_PZPHP)
	{
		$this->_PZPHP = $_PZPHP;
	}

	/**
	 * @param $lang
	 * @param $action
	 *
	 * @throws Exception
	 */
	public function before($lang, $action)
	{
		$found = false;
		if(!empty($lang))
		{
			foreach(PzPHP_Config::get('LANGS') as $longId => $langInfoArray)
			{
				if($langInfoArray['short'] === strtolower($lang))
				{
					$this->_PZPHP->locale()
						->addLanguage($langInfoArray['short'], $longId)
						->setCurrentLocale($langInfoArray['short']);

					$found = true;

					break;
				}
			}

			if(!$found)
			{
				throw new Exception('Invalid language id given in the url! Id was: '.$lang, PzPHP_Helper_Codes::ROUTING_ERROR);
			}
		}
		else
		{
			foreach(PzPHP_Config::get('LANGS') as $longId => $langInfoArray)
			{
				if($langInfoArray['default'] === true)
				{
					$this->_PZPHP->locale()
						->addLanguage($langInfoArray['short'], $longId)
						->setCurrentLocale($langInfoArray['short']);

					$found = true;

					break;
				}
			}

			if(!$found)
			{
				throw new Exception('No default language has been set!', PzPHP_Helper_Codes::ROUTING_ERROR);
			}
		}
	}
}
