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
	final class Pz_Memcached_Server
	{
		/**
		 * Status constant when connection has been established to memcache.
		 *
		 * @var int
		 */
		const CONNECTED = 1;

		/**
		 * Status constant when there is no connection to the memcache server.
		 *
		 * @var int
		 */
		const DISCONNECTED = 2;

		/**
		 * Status constant when connecting to the memcache server.
		 *
		 * @var int
		 */
		const CONNECTING = 3;

		/**
		 * The IP of the target memcache server.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_ip = '';

		/**
		 * The port of the target memcache server.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_port = 0;

		/**
		 * The amount of times Pz should try to reconnect to memcache on failure.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_connectRetryAttempts = 0;

		/**
		 * The time in seconds to wait between retry attempts.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_connectRetryDelay = 0;

		/**
		 * The current connection status.
		 *
		 * @access protected
		 * @var int
		 */
		protected $_status = self::DISCONNECTED;

		/**
		 * The current active memcached object.
		 *
		 * @access protected
		 * @var null|memcached
		 */
		protected $_memcached_obj = null;

		/**
		 * The constructor handles setting the default settings.
		 *
		 * @param string $mcIp
		 * @param int $mcPort
		 * @param int $connectRetries
		 * @param int $connectRetryWait
		 */
		function __construct($mcIp, $mcPort, $connectRetries, $connectRetryWait)
		{
			$this->_ip = $mcIp;
			$this->_port = $mcPort;
			$this->_connectRetryAttempts = $connectRetries;
			$this->_connectRetryDelay = $connectRetryWait;
		}

		/**
		 * Attempts to connect to the memcache server.
		 *
		 * @access public
		 * @return bool
		 */
		public function connect()
		{
			if($this->isConnected() === false)
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

		/**
		 * Disconnect the memcache server.
		 *
		 * @access public
		 */
		public function disconnect()
		{
			if($this->isConnected() === true && is_object($this->_memcached_obj))
			{
				$this->_memcached_obj = null;

				$this->_status = self::DISCONNECTED;
			}
		}

		/**
		 * Returns true or false depending on if the memcache server has been connected to.
		 *
		 * @access public
		 * @return bool
		 */
		public function isConnected()
		{
			return ($this->_status===self::CONNECTED&&is_object($this->_memcached_obj)?true:false);
		}

		/**
		 * Return the current active memcached object.
		 *
		 * @access public
		 * @return null|memcached
		 */
		public function returnMemcachedObj()
		{
			return $this->_memcached_obj;
		}
	}
