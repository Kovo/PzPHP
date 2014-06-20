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
 * Class that stores and controls access to a memcache server (using memcached).
 */
class PzPHP_Library_Cache_Memcached_Server
{
	/**
	 * @var int
	 */
	const CONNECTED = 1;

	/**
	 * @var int
	 */
	const DISCONNECTED = 2;

	/**
	 * @var int
	 */
	const CONNECTING = 3;

	/**
	 * @var string
	 */
	protected $_ip = '';

	/**
	 * @var int
	 */
	protected $_port = 0;

	/**
	 * @var int
	 */
	protected $_connectRetryAttempts = 0;

	/**
	 * @var int
	 */
	protected $_connectRetryDelay = 0;

	/**
	 * @var int
	 */
	protected $_status = self::DISCONNECTED;

	/**
	 * @var null|memcached
	 */
	protected $_memcached_obj = null;

	/**
	 * @param $mcIp
	 * @param $mcPort
	 * @param $connectRetries
	 * @param $connectRetryWait
	 */
	function __construct($mcIp, $mcPort, $connectRetries, $connectRetryWait)
	{
		$this->_ip = $mcIp;
		$this->_port = $mcPort;
		$this->_connectRetryAttempts = $connectRetries;
		$this->_connectRetryDelay = $connectRetryWait;
	}

	/**
	 * @return bool
	 */
	public function connect()
	{
		if(!$this->isConnected())
		{
			$this->_status = self::CONNECTING;

			$this->_memcached_obj =  new Memcached();

			if($this->_memcached_obj->getOption(Memcached::OPT_COMPRESSION) !== true)
			{
				$this->_memcached_obj->setOption(Memcached::OPT_COMPRESSION, true);
			}

			if(!$this->_memcached_obj->addServer($this->_ip, $this->_port))
			{
				for($x=0;$x<$this->_connectRetryAttempts;$x++)
				{
					sleep($this->_connectRetryDelay);

					if(!$this->_memcached_obj->addServer($this->_ip, $this->_port))
					{
						continue;
					}
					else
					{
						$this->_status = self::CONNECTED;

						break;
					}
				}

				if($this->_status === self::CONNECTING)
				{
					$this->_status = self::DISCONNECTED;

					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				$this->_status = self::CONNECTED;

				return true;
			}
		}
		else
		{
			return true;
		}
	}

	public function disconnect()
	{
		if($this->isConnected() && is_object($this->_memcached_obj))
		{
			$this->_memcached_obj = null;

			$this->_status = self::DISCONNECTED;
		}
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->_status===self::CONNECTED&&is_object($this->_memcached_obj)?true:false);
	}

	/**
	 * @return memcached|null
	 */
	public function getCacheObject()
	{
		return $this->_memcached_obj;
	}
}
