<?php
class PzPHP_Module_Request extends PzPHP_Wrapper
{
	/**
	 * @var bool
	 */
	protected $_isAjax = false;

	/**
	 * @var string
	 */
	protected $_queryString = '';

	/**
	 * @var array
	 */
	protected $_mediaTypes = array();

	/**
	 * @var array
	 */
	protected $_charsets = array();

	/**
	 * @var array
	 */
	protected $_encodings = array();

	/**
	 * @var array
	 */
	protected $_languages = array();

	/**
	 * @var string
	 */
	protected $_referer = '';

	/**
	 * @var bool
	 */
	protected $_secure = false;

	/**
	 * @param PzPHP_Core $PzPHPCore
	 */
	public function init(PzPHP_Core $PzPHPCore)
	{
		parent::init($PzPHPCore);

		$this->_populateRequestParameters();
	}

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

	protected function _detectReferer()
	{
		$rawData = $this->server('HTTP_REFERER');

		if($rawData !== NULL)
		{
			$this->_referer = trim($rawData);
		}
	}

	protected function _detectHttps()
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

	protected function _detectQueryString()
	{
		$rawData = $this->server('QUERY_STRING');

		if($rawData !== NULL)
		{
			$this->_queryString = $this->cleanQueryString($rawData);
		}
	}

	protected function _detectMediaTypes()
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

	protected function _detectCharsets()
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

	protected function _detectEncodings()
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

	protected function _detectLanguages()
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

	protected function _detectAjax()
	{
		$serverXmlHttpVar = $this->server('HTTP_X_REQUESTED_WITH');

		$this->_isAjax = (!empty($serverXmlHttpVar) && strtolower($serverXmlHttpVar) === 'xmlhttprequest');
	}

	/**
	 * @param $header
	 * @return array|null
	 */
	public function parseAcceptHeader($header)
	{
		$return = NULL;
		$header = str_replace(array("\r\n", "\r", "\n"), ' ', trim($header));
		$types = explode(',', $header);
		$types = array_map(array('PzPHP_Helper_String', 'trim'), $types);

		if($header !== '')
		{
			foreach($types as $ruleSets)
			{
				$ruleSet = array_map(array('PzPHP_Helper_String', 'trim'), explode(';', $ruleSets));
				$rule = array_shift($ruleSet);

				if($rule)
				{
					$array = array_map(array('PzPHP_Helper_String', 'trim'), explode('/', $rule));

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

			PzPHP_Helper_Array::aasort($return, 'precedence', SORT_NUMERIC, false);
		}

		return $return;
	}

	/**
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

		$ruleSet = array_map(array('PzPHP_Helper_String', 'trim'), $ruleSet);

		foreach($ruleSet as $option)
		{
			$option = explode('=', $option);
			$option = array_map(array('PzPHP_Helper_String', 'trim'), $option);

			if($option[0] === 'q')
			{
				$precedence = $option[1];
			}
			else
			{
				$tokens[$option[0]] = $option[1];
			}
		}

		$tokens = (!empty($tokens)?$tokens:false);

		return array($precedence, $tokens);
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
	 * @return null
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
	 * @return null
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
	 * @return null
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
	 * @return null
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
	 * @return null|string
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
