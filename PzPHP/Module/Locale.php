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
		 * An array of key => value paris representing languages.
		 *
		 * The key must be the long format, while the value must be the short format.
		 * Ex: en_us => en
		 *
		 * @access protected
		 * @var array
		 */
		protected $_languages = array();

		/**
		 * The loaded translations array.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_translations = array();

		/**
		 * The current active language.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_localeLong = '';
		protected $_localeShort = '';

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
			$this->_localeLong = $this->getLongLocaleId($locale);
			$this->_localeShort = $this->getShortLocaleId($locale);
		}

		/**
		 * @param bool $short
		 *
		 * @return string
		 */
		public function getLocale($short = false)
		{
			if($short)
			{
				return $this->_localeShort;
			}
			else
			{
				return $this->_localeLong;
			}
		}

		/**
		 * @param string $localeoverride
		 *
		 * @throws PzPHP_Exception
		 */
		public function loadTranslationSet($localeoverride = '')
		{
			$shortLocale = ($localeoverride===''?$this->_localeShort:$this->getShortLocaleId($localeoverride));
			$translations = array();

			if(file_exists(PzPHP_Config::get('PZPHP_TRANSLATIONS_DIR').$shortLocale.'.php'))
			{
				require PzPHP_Config::get('PZPHP_TRANSLATIONS_DIR').$shortLocale.'.php';
			}
			else
			{
				throw new PzPHP_Exception('Could not find "'.PzPHP_Config::get('PZPHP_TRANSLATIONS_DIR').$shortLocale.'.php"!');
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
			$shortLocale = ($localeOverride===''?$this->_localeShort:$this->getShortLocaleId($localeOverride));

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
