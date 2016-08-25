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
	 * @return array
	 */
	public function getExposed()
	{
		$exposed = array();

		if(!empty($this->_translations[$this->_currentLocale]))
		{
			foreach($this->_translations[$this->_currentLocale] as $key => $value)
			{
				if(substr($key,0,7) === 'exposed')
				{
					$exposed[$key] = $value;
				}
			}
		}

		return $exposed;
	}

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
	 * @param      $shortform
	 * @param bool $autoload
	 *
	 * @return $this
	 */
	public function setCurrentLocale($shortform, $autoload = true)
	{
		$shortLocale = $this->getShortLocaleId($shortform);
		$this->_currentLocale = $shortLocale;

		if($autoload)
		{
			$this->load($shortLocale);
		}

		return $this;
	}

	/**
	 * @param $lang
	 *
	 * @return bool
	 */
	public function languageExists($lang)
	{
		if(empty($this->getShortLocaleId($lang)))
		{
			return false;
		}
		else
		{
			return true;
		}
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
			require_once PzPHP_Config::get('TRANSLATIONS_DIR').$shortLocale.'.php';
		}
		else
		{
			throw new PzPHP_Exception('Could not find "'.PzPHP_Config::get('TRANSLATIONS_DIR').$shortLocale.'.php"!', PzPHP_Helper_Codes::LOCALE_FILE_NOT_FOUND);
		}

		if(!isset($this->_translations[$shortLocale]))
		{
			$this->_translations[$shortLocale] = $translations;
		}
		else
		{
			$this->_translations[$shortLocale] += $translations;
		}

		return $this;
	}

	/**
	 * @param        $file
	 * @param string $localeoverride
	 *
	 * @return $this
	 * @throws PzPHP_Exception
	 */
	public function addAdditionalFile($file, $localeoverride = '')
	{
		$shortLocale = ($localeoverride===''?$this->_currentLocale:$this->getShortLocaleId($localeoverride));
		$translations = array();

		if(file_exists($file))
		{
			require_once $file;
		}
		else
		{
			throw new PzPHP_Exception('Could not find "'.$file.'"!', PzPHP_Helper_Codes::LOCALE_FILE_NOT_FOUND);
		}

		$this->_translations[$shortLocale] += $translations;

		return $this;
	}

	/**
	 * @param $locale
	 * @return string
	 */
	public function getShortLocaleId($locale)
	{
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

			if($noReplace === false && !empty($replacements))
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

	/**
	 * @return string
	 */
	public function getCurrentLocale()
	{
		return $this->_currentLocale;
	}
}