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
	 * @package Pz Library
	 */
	/**
	 * The Request class allows you to learn about the essentials of the current request.
	 */
	class Pz_Http_Request extends Pz_Abstract_Generic
	{
		/**
		 * Whether the current request is an AJAX request or not.
		 *
		 * @access protected
		 * @var bool
		 */
		protected $_isAjax = false;

		/**
		 * The query string (if any) associated with this request.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_queryString = '';

		/**
		 * The different accepted media types.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_mediaTypes = array();

		/**
		 * The different accepted charsets.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_charsets = array();

		/**
		 * The different accepted encodings.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_encodings = array();

		/**
		 * The different accepted languages.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_languages = array();

		/**
		 * The referer url for this request.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_referer = '';

		/**
		 * Whether this request is being sent over https.
		 *
		 * @access protected
		 * @var bool
		 */
		protected $_secure = false;

		/**
		 * Sets Pz Core object and starts gathering request data.
		 *
		 * @param Pz_Core $PzCore
		 */
		function __construct(Pz_Core $PzCore)
		{
			parent::__construct($PzCore);

			$this->_populateRequestParameters();
		}

		/**
		 * Gathers various information about the request and stores it.
		 *
		 * @access protected
		 */
		protected function _populateRequestParameters()
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

		/**
		 * Detects the referer url (if any).
		 *
		 * @access protected
		 */
		protected function _detectReferer()
		{
			$rawData = $this->server('HTTP_REFERER');

			if($rawData !== null)
			{
				$this->_referer = trim($rawData);
			}
		}

		/**
		 * Detects whether https is being used.
		 *
		 * @access protected
		 */
		protected function _detectHttps()
		{
			$rawData = $this->server('HTTPS');

			if($rawData !== null)
			{
				$rawData = strtolower(trim($rawData));

				if($rawData !== '' && $rawData !== 'off')
				{
					$this->_secure = true;
				}
			}
		}

		/**
		 * Detects and cleans the query string (if any).
		 *
		 * @access protected
		 */
		protected function _detectQueryString()
		{
			$rawData = $this->server('QUERY_STRING');

			if($rawData !== null)
			{
				$this->_queryString = $this->cleanQueryString($rawData);
			}
		}

		/**
		 * Reads header information for media types and extracts them.
		 *
		 * @access protected
		 */
		protected function _detectMediaTypes()
		{
			$rawData = $this->server('HTTP_ACCEPT');

			if($rawData !== null)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== null)
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

		/**
		 * Reads header information for charsets and extracts them.
		 *
		 * @access protected
		 */
		protected function _detectCharsets()
		{
			$rawData = $this->server('HTTP_ACCEPT_CHARSET');

			if($rawData !== null)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== null)
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

		/**
		 * Reads header information for accepted encodings and extracts them.
		 *
		 * @access protected
		 */
		protected function _detectEncodings()
		{
			$rawData = $this->server('HTTP_ACCEPT_ENCODING');

			if($rawData !== null)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== null)
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

		/**
		 * Reads header information for accepted languages and extracts them.
		 *
		 * @access protected
		 */
		protected function _detectLanguages()
		{
			$rawData = $this->server('HTTP_ACCEPT_LANGUAGE');

			if($rawData !== null)
			{
				$rawData = trim($rawData);

				if($rawData !== '')
				{
					$extractInfo = $this->parseAcceptHeader($rawData);

					if($extractInfo !== null)
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
		 * Parses any kind of accept header and extracts its data.
		 *
		 * @access public
		 * @param $header
		 * @return array|null
		 */
		public function parseAcceptHeader($header)
		{
			$return = null;
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
						$array = array_map('trim', explode('/', $rule));

						if(!isset($array[1]))
						{
							$array[1] = $array[0];
						}

						list($precedence, $tokens) = $this->acceptHeaderOptions($ruleSet);
						list($mainOption, $subOption) = $array;

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
		 * Breaks-apart the accept header value.
		 *
		 * @access public
		 * @param $ruleSet
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

		/**
		 * Detects if current request is an ajax call.
		 *
		 * @access protected
		 */
		protected function _detectAjax()
		{
			$serverXmlHttpVar = $this->server('HTTP_X_REQUESTED_WITH');

			$this->_isAjax = (!empty($serverXmlHttpVar) && strtolower($serverXmlHttpVar) === 'xmlhttprequest');
		}

		/**
		 * Returns true or false depending on if the current request is ajax.
		 *
		 * @access public
		 * @return bool
		 */
		public function isAjax()
		{
			return $this->_isAjax;
		}

		/**
		 * Returns this requests accepted media types.
		 *
		 * @access public
		 * @return array
		 */
		public function getMediaTypes()
		{
			return $this->_mediaTypes;
		}

		/**
		 * Returns this requests accepted charsets.
		 *
		 * @access public
		 * @return array
		 */
		public function getCharsets()
		{
			return $this->_charsets;
		}

		/**
		 * Returns this requests accepted encodings.
		 *
		 * @access public
		 * @return array
		 */
		public function getEncodings()
		{
			return $this->_encodings;
		}

		/**
		 * Returns this requests accepted languages.
		 *
		 * @access public
		 * @return array
		 */
		public function getLanguages()
		{
			return $this->_languages;
		}

		/**
		 * Returns true or false depending on if this request is secure or not.
		 *
		 * @access public
		 * @return bool
		 */
		public function isSecure()
		{
			return $this->_secure;
		}

		/**
		 * Returns this request's referer url (if any).
		 *
		 * @access public
		 * @return string
		 */
		public function getReferer()
		{
			return $this->_referer;
		}

		/**
		 * Returns a value from the SERVER super global.
		 *
		 * @access public
		 * @param $varname
		 * @return null|string
		 */
		public function server($varname)
		{
			$return = null;

			if(isset($_SERVER[$varname]))
			{
				$return = $_SERVER[$varname];
			}

			return $return;
		}

		/**
		 * Returns a value from the GET super global.
		 *
		 * @access public
		 * @param $varname
		 * @return null|string
		 */
		public function get($varname)
		{
			$return = null;

			if(isset($_GET[$varname]))
			{
				$return = $_GET[$varname];
			}

			return $return;
		}

		/**
		 * Returns a value from the POST super global.
		 *
		 * @access public
		 * @param $varname
		 * @return null|string
		 */
		public function post($varname)
		{
			$return = null;

			if(isset($_POST[$varname]))
			{
				$return = $_POST[$varname];
			}

			return $return;
		}

		/**
		 * Returns a value from the COOKIE super global.
		 *
		 * @access public
		 * @param $varname
		 * @return null|string
		 */
		public function cookie($varname)
		{
			$return = null;

			if(isset($_COOKIE[$varname]))
			{
				$return = $_COOKIE[$varname];
			}

			return $return;
		}

		/**
		 * Returns a value from the FILES super global.
		 *
		 * @access public
		 * @param $varname
		 * @return null
		 */
		public function files($varname)
		{
			$return = null;

			if(isset($_FILES[$varname]))
			{
				$return = $_FILES[$varname];
			}

			return $return;
		}

		/**
		 * Returns the client IP address.
		 *
		 * @access public
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
		 * Returns the server's IP address/
		 *
		 * @access public
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
		 * Cleans a raw query string.
		 *
		 * @access public
		 * @param $queryString
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
