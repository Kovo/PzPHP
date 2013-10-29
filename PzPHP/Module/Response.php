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
 * Allows you to set response headers and status codes.
 */
class PzPHP_Module_Response extends PzPHP_Wrapper
{
	/**
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * @var int
	 */
	protected $_statusCode = 200;

	/**
	 * @var string
	 */
	protected $_httpVersion = '1.1';

	/**
	 * @var array
	 */
	protected $_statusText = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Reserved for WebDAV advanced collections expired proposal',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required'
	);

	/**
	 * @param $name
	 * @param string $value
	 * @param bool $replace
	 * @param null $responsecode
	 */
	public function setHeader($name, $value = '', $replace = false, $responsecode = null)
	{
		header($name.($value !== ''?': '.$value:''), $replace, $responsecode);

		$this->_headers[$name] = ($value !== ''?$value:$name);
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getHeader($name)
	{
		return $this->_headers[$name];
	}

	/**
	 * @param $url
	 * @param bool $exit
	 */
	public function redirect($url, $exit = true)
	{
		$this->setHeader('HTTP/1.1 301 Moved Permanently');
		$this->setHeader('Location', $url);
		$this->setHeader('Connection', 'close');

		if($exit)
		{
			exit();
		}
	}

	/**
	 * @param $code
	 */
	public function setStatusCode($code)
	{
		$this->_statusCode = (int)$code;

		$this->setHeader(sprintf('HTTP/%s %s %s', $this->_httpVersion, $this->_statusCode, $this->_statusText[$this->_statusCode]), '', true, $this->_statusCode);
	}

	/**
	 * @param $version
	 */
	public function setHttpVersion($version)
	{
		$this->_httpVersion = $version;
	}

	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->_statusCode;
	}

	/**
	 * @return string
	 */
	public function getHttpVersion()
	{
		return $this->_httpVersion;
	}

	/**
	 * @return mixed
	 */
	public function getStatusText()
	{
		return $this->_statusText[$this->_statusCode];
	}
}
