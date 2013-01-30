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
	class PzPHP_Locale extends PzPHP_Wrapper
	{
		/**
		 * An array of key => value paris representing languages.
		 *
		 * The key must be the long format, while the value must be the short format.
		 * Ex: en_us => en
		 *
		 * @access private
		 * @var array
		 */
		protected $_languages = array();

		/**
		 * The loaded translations array.
		 *
		 * @access private
		 * @var array
		 */
		protected $_translations = array();

		/**
		 * The current active language.
		 *
		 * @access private
		 * @var string
		 */
		protected $_locale = '';

		/**
		 * Add a language to be used in translation.
		 *
		 * @access public
		 * @param string $languageFull
		 * @param string $languageShort
		 */
		public function addLanguage($languageFull, $languageShort)
		{
			$this->_languages[$languageFull] = $languageShort;
		}

		/**
		 * Set the current active language.
		 *
		 * @access public
		 * @param $locale
		 */
		public function setLocale($locale)
		{
			$this->_locale = $this->getLongLocaleId($locale);
		}

		/**
		 * Get the current active language.
		 *
		 * @access public
		 * @return string
		 */
		public function getLocale()
		{
			return $this->_locale;
		}

		/**
		 * Load an array of translations for current active language.
		 *
		 * @access public
		 * @param string $localeoverride
		 */
		public function loadTranslationSet($localeoverride = '')
		{
			$shortLocale = $this->getShortLocaleId(($localeoverride===''?$this->_locale:$localeoverride));
			$translations = array();

			if(file_exists(PZPHP_TRANSLATIONS_DIR.$shortLocale.'.php'))
			{
				include PZPHP_TRANSLATIONS_DIR.$shortLocale.'.php';
			}

			$this->_translations[$shortLocale] = $translations;
		}

		/**
		 * Convert the locale string to short format (if necessary).
		 *
		 * @access public
		 * @param string $locale
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
		 * Convert the locale string to long format (if necessary).
		 *
		 * @access public
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
		 * Retrieve the text for the given key.
		 *
		 * @access public
		 * @param string $key
		 * @param array $replacements
		 * @param string $localeOverride
		 * @return mixed
		 */
		public function translate($key, $replacements = array(), $localeOverride = '')
		{
			$shortLocale = ($localeOverride===''?$this->_locale:$this->getShortLocaleId($localeOverride));

			if(isset($this->_translations[$shortLocale][$key]))
			{
				$returnString = $this->_translations[$shortLocale][$key];

				if(count($replacements) > 0)
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
