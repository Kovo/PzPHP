<?php
class PzPHP_Library_Cache_Memcache_Server
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
	 * @var null|memcache
	 */
	protected $_memcache_obj = null;

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

			$this->_memcache_obj =  new Memcache();

			if(!$this->_memcache_obj->connect($this->_ip, $this->_port))
			{
				for($x=0;$x<$this->_connectRetryAttempts;$x++)
				{
					sleep($this->_connectRetryDelay);

					if(!$this->_memcache_obj->connect($this->_ip, $this->_port))
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
		if($this->isConnected() && is_object($this->_memcache_obj))
		{
			$this->_memcache_obj->close();

			$this->_memcache_obj = null;

			$this->_status = self::DISCONNECTED;
		}
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->_status===self::CONNECTED&&is_object($this->_memcache_obj)?true:false);
	}

	/**
	 * @return memcache|null
	 */
	public function getCacheObject()
	{
		return $this->_memcache_obj;
	}
}
