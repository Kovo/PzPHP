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
	 * @package Pz_Http_Request
	 */
	class Pz_Http_Request extends Pz_Abstract_Generic
	{
		/**
		 * @var bool
		 */
		private $_isAjax = false;

		/**
		 * @var string
		 */
		private $_queryString = '';

		/**
		 * @var array
		 */
		private $_mediaTypes = array();

		/**
		 * @var array
		 */
		private $_charsets = array();

		/**
		 * @var array
		 */
		private $_encodings = array();

		/**
		 * @var array
		 */
		private $_languages = array();

		/**
		 * @var string
		 */
		private $_referer = '';

		/**
		 * @var bool
		 */
		private $_secure = false;

		/**
		 * @param Pz_Core $PzCore
		 */
		function __construct(Pz_Core $PzCore)
		{
			parent::__construct($PzCore);

			$this->_populateRequestParameters();
		}

		private function _populateRequestParameters()
		{
			$this->_detectAjax();
			$this->_detectQueryString();
			$this->_detectReferer();
			$this->_detectHttps();
			$this->_detectMediaTypes();
			$this->_detectCharsets();
			$this->_detectEncodings();
			$this->_detectLanguages();
		}

		private function _detectReferer()
		{
			$rawData = $this->server('HTTP_REFERER');

			if($rawData !== NULL)
			{
				$this->_referer = trim($rawData);
			}
		}

		private function _detectHttps()
		{
			$rawData = $this->server('HTTPS');

			if($rawData !== NULL)
			{
				$rawData = strtolower(trim($rawData));

				if($rawData !== '' && $rawData !== 'off')
				{
					$this->_secure = true;
				}
			}
		}

		private function _detectQueryString()
		{
			$rawData = $this->server('QUERY_STRING');

			if($rawData !== NULL)
			{
				$this->_queryString = $this->cleanQueryString($rawData);
			}
		}

		private function _detectMediaTypes()
		{
			$rawData = $this->server('HTTP_ACCEPT');

			if($rawData !== NULL)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== NULL)
					{
						$this->_mediaTypes = $extractInfo;
					}
				}
			}

			if(count($this->_mediaTypes) === 0)
			{
				$this->_mediaTypes[] = array(
					'main_type' => '*/*',
					'sub_type' => '',
					'precedence' => 1,
					'tokens' => false
				);
			}
		}

		private function _detectCharsets()
		{
			$rawData = $this->server('HTTP_ACCEPT_CHARSET');

			if($rawData !== NULL)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== NULL)
					{
						$this->_charsets = $extractInfo;
					}
				}
			}

			if(count($this->_charsets) === 0)
			{
				$this->_charsets[] = array(
					'main_type' => 'ISO-8859-1',
					'sub_type' => '',
					'precedence' => 1,
					'tokens' => false
				);
			}
		}

		private function _detectEncodings()
		{
			$rawData = $this->server('HTTP_ACCEPT_ENCODING');

			if($rawData !== NULL)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== NULL)
					{
						$this->_encodings = $extractInfo;
					}
				}
			}

			if(count($this->_encodings) === 0)
			{
				$this->_encodings[] = array(
					'main_type' => '*',
					'sub_type' => '',
					'precedence' => 1,
					'tokens' => false
				);
			}
		}

		private function _detectLanguages()
		{
			$rawData = $this->server('HTTP_ACCEPT_LANGUAGE');

			if($rawData !== NULL)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== NULL)
					{
						$this->_languages = $extractInfo;
					}
				}
			}

			if(count($this->_languages) === 0)
			{
				$this->_languages[] = array(
					'main_type' => '*',
					'sub_type' => '',
					'precedence' => 1,
					'tokens' => false
				);
			}
		}

		/**
		 * @param $header
		 *
		 * @return array|null
		 */
		public function parseAcceptHeader($header)
		{
			$return = NULL;
			$header = str_replace(array("\r\n", "\r", "\n"), ' ', trim($header));
			$types = explode(',', $header);
			$types = array_map('trim', $types);

			if($header !== '')
			{
				foreach($types as $ruleSets)
				{
					$ruleSet = array_map('trim', explode(';', $ruleSets));
					$rule = array_shift($ruleSet);

					if($rule)
					{
						list($precedence, $tokens) = $this->acceptHeaderOptions($ruleSet);
						list($mainOption, $subOption) = array_map('trim', explode('/', $rule));

						$return[] = array(
							'main_type' => $mainOption,
							'sub_type' => $subOption,
							'precedence' => (float)$precedence,
							'tokens' => $tokens
						);
					}
				}

				Pz_Helper_Array::aasort($return, 'precedence', SORT_NUMERIC, false);
			}

			return $return;
		}

		/**
		 * @param $ruleSet
		 *
		 * @return array
		 */
		public function acceptHeaderOptions($ruleSet)
		{
			$precedence = 1;
			$tokens = array();

			if(is_string($ruleSet))
			{
				$ruleSet = explode(';', $ruleSet);
			}

			$ruleSet = array_map('trim', $ruleSet);

			foreach($ruleSet as $option)
			{
				$option = explode('=', $option);
				$option = array_map('trim', $option);

				if($option[0] === 'q')
				{
					$precedence = $option[1];
				}
				else
				{
					$tokens[$option[0]] = $option[1];
				}
			}

			$tokens = (count($tokens)?$tokens:false);

			return array($precedence, $tokens);
		}

		private function _detectAjax()
		{
			$serverXmlHttpVar = $this->server('HTTP_X_REQUESTED_WITH');

			$this->_isAjax = (!empty($serverXmlHttpVar) && strtolower($serverXmlHttpVar) === 'xmlhttprequest');
		}

		/**
		 * @return bool
		 */
		public function isAjax()
		{
			return $this->_isAjax;
		}

		/**
		 * @return array
		 */
		public function getMediaTypes()
		{
			return $this->_mediaTypes;
		}

		/**
		 * @return array
		 */
		public function getCharsets()
		{
			return $this->_charsets;
		}

		/**
		 * @return array
		 */
		public function getEncodings()
		{
			return $this->_encodings;
		}

		/**
		 * @return array
		 */
		public function getLanguages()
		{
			return $this->_languages;
		}

		/**
		 * @return bool
		 */
		public function isSecure()
		{
			return $this->_secure;
		}

		/**
		 * @return string
		 */
		public function getReferer()
		{
			return $this->_referer;
		}

		/**
		 * @param $varname
		 *
		 * @return null|string
		 */
		public function server($varname)
		{
			$return = NULL;

			if(isset($_SERVER[$varname]))
			{
				$return = $_SERVER[$varname];
			}

			return $return;
		}

		/**
		 * @param $varname
		 *
		 * @return null|string
		 */
		public function get($varname)
		{
			$return = NULL;

			if(isset($_GET[$varname]))
			{
				$return = $_GET[$varname];
			}

			return $return;
		}

		/**
		 * @param $varname
		 *
		 * @return null|string
		 */
		public function post($varname)
		{
			$return = NULL;

			if(isset($_POST[$varname]))
			{
				$return = $_POST[$varname];
			}

			return $return;
		}

		/**
		 * @param $varname
		 *
		 * @return null|string
		 */
		public function cookie($varname)
		{
			$return = NULL;

			if(isset($_COOKIE[$varname]))
			{
				$return = $_COOKIE[$varname];
			}

			return $return;
		}

		/**
		 * @param $varname
		 *
		 * @return null
		 */
		public function files($varname)
		{
			$return = NULL;

			if(isset($_FILES[$varname]))
			{
				$return = $_FILES[$varname];
			}

			return $return;
		}

		/**
		 * @return string
		 */
		public function clientIpAddress()
		{
			$httpClientIp = $this->server('HTTP_CLIENT_IP');
			$httpXForwardedFor = $this->server('HTTP_X_FORWARDED_FOR');
			$remoteAddr = $this->server('REMOTE_ADDR');

			if(!empty($httpClientIp))
			{
				$ip = $httpClientIp;
			}
			elseif(!empty($httpXForwardedFor))
			{
				$ip = $httpXForwardedFor;
			}
			elseif(!empty($remoteAddr))
			{
				$ip = $remoteAddr;
			}
			else
			{
				$ip = 'unknown';
			}

			return $ip;
		}

		/**
		 * @return null|string
		 */
		public function serverIpAddress()
		{
			$localAddr = $this->server('LOCAL_ADDR');
			$serverAddr = $this->server('SERVER_ADDR');

			if(!empty($localAddr))
			{
				$ip = $localAddr;
			}
			elseif(!empty($serverAddr))
			{
				$ip = $serverAddr;
			}
			else
			{
				$ip = 'unknown';
			}

			return $ip;
		}

		/**
		 * @param $queryString
		 *
		 * @return string
		 */
		public function cleanQueryString($queryString)
		{
			$queryString = trim($queryString);

			if($queryString !== '')
			{
				$parts = array();
				$order = array();

				foreach(explode('&', $queryString) as $param)
				{
					if($param === '' || $param[0] === '=')
					{
						continue;
					}

					$keyValuePair = explode('=', $param, 2);

					$parts[] = (isset($keyValuePair[1])?
						rawurlencode(urldecode($keyValuePair[0])).'='.rawurlencode(urldecode($keyValuePair[1])):
						rawurlencode(urldecode($keyValuePair[0]))
					);

					$order[] = urldecode($keyValuePair[0]);
				}

				array_multisort($order, SORT_ASC, $parts);

				$queryString = implode('&', $parts);
			}

			return $queryString;
		}
	}
