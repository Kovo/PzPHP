<?php
	/**
	 * Website: http://www.pzphp.com
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzMemcachedServer
	 */
	final class PzMemcachedServer
	{
		/*
		 * Status constants
		 */
		const CONNECTED = 1;
		const DISCONNECTED = 2;
		const CONNECTING = 3;

		/**
		 * @var string
		 */
		private $_ip = '';

		/**
		 * @var int
		 */
		private $_port = 0;

		/**
		 * @var int
		 */
		private $_connectRetryAttempts = 0;

		/**
		 * @var int
		 */
		private $_connectRetryDelay = 0;

		/**
		 * @var int
		 */
		private $_status = self::DISCONNECTED;

		/**
		 * @var null
		 */
		private $_memcached_obj = NULL;

		function __construct($mcIp, $mcPort, $connectRetries, $connectRetryWait)
		{
			$this->_ip = $mcIp;
			$this->_port = $mcPort;
			$this->_connectRetryAttempts = $connectRetries;
			$this->_connectRetryDelay = $connectRetryWait;
		}

		/**
		 * @return bool
		 *
		 * Attempts to connect to the mecached server
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

		/*
		 * Disconnect the mysql server
		 */
		public function disconnect()
		{
			if($this->isConnected() === true && is_object($this->_memcached_obj))
			{
				$this->_memcached_obj->close();

				$this->_memcached_obj = NULL;

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
		 * @return null
		 */
		public function returnMemcachedObj()
		{
			return $this->_memcached_obj;
		}
	}
