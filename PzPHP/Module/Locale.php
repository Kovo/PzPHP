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
 * The Locale class allows your application to support multiple languages.
 */
class PzPHP_Module_Locale extends PzPHP_Wrapper
{
	/**
	 * @var array
	 */
	protected $_translations = array();

	/**
	 * @var array
	 */
	protected $_languages = array();

	/**
	 * @var string
	 */
	protected $_currentLocale = '';

	/**
	 * @param $shortForm
	 * @param $longForm
	 * @param bool $autoload
	 * @return $this
	 */
	public function addLanguage($shortForm, $longForm, $autoload = false)
	{
		$this->_languages[$longForm] = $shortForm;

		if($autoload)
		{
			$this->load($shortForm);
		}

		return $this;
	}

	/**
	 * @param $shortform
	 * @return $this
	 */
	public function setCurrentLocale($shortform)
	{
		$shortLocale = $this->getShortLocaleId($shortform);
		$this->_currentLocale = $shortLocale;

		return $this;
	}

	/**
	 * @param string $localeoverride
	 * @return $this
	 * @throws PzPHP_Exception
	 */
	public function load($localeoverride = '')
	{
		$shortLocale = ($localeoverride===''?$this->_currentLocale:$this->getShortLocaleId($localeoverride));
		$translations = array();

		if(file_exists(PzPHP_Config::get('TRANSLATIONS_DIR').$shortLocale.'.php'))
		{
			require PzPHP_Config::get('TRANSLATIONS_DIR').$shortLocale.'.php';
		}
		else
		{
			throw new PzPHP_Exception('Could not find "'.PzPHP_Config::get('TRANSLATIONS_DIR').$shortLocale.'.php"!', PzPHP_Helper_Codes::LOCALE_FILE_NOT_FOUND);
		}

		$this->_translations[$shortLocale] = $translations;

		return $this;
	}

	/**
	 * @param $locale
	 * @return string
	 */
	public function getShortLocaleId($locale)
	{
		$locale = strtolower($locale);

		if(isset($this->_languages[$locale]))
		{
			return $this->_languages[$locale];
		}
		elseif(in_array($locale, $this->_languages))
		{
			return $locale;
		}
		else
		{
			return '';
		}
	}


	/**
	 * @param $locale
	 * @return mixed|string
	 */
	public function getLongLocaleId($locale)
	{
		$locale = strtolower($locale);

		if(isset($this->_languages[$locale]))
		{
			return $locale;
		}
		elseif(in_array($locale, $this->_languages))
		{
			return array_search($locale, $this->_languages);
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param $key
	 * @param array $replacements
	 * @param string $localeOverride
	 * @param bool $noReplace
	 * @return mixed
	 */
	public function translate($key, $replacements = array(), $localeOverride = '', $noReplace = false)
	{
		$shortLocale = ($localeOverride===''?$this->_currentLocale:$this->getShortLocaleId($localeOverride));

		if(isset($this->_translations[$shortLocale][$key]))
		{
			$returnString = $this->_translations[$shortLocale][$key];

			if($noReplace === false && count($replacements) > 0)
			{
				foreach($replacements as $key => $value)
				{
					$returnString = str_replace('%'.$key.'%', $value, $returnString);
				}
			}

			return $returnString;
		}
		else
		{
			return $key;
		}
	}
}
