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
	 * This class provides methods that normally should be handled by the server itself.
	 */
	class Pz_Server_Security extends Pz_Abstract_StaticGeneric
	{
		/**
		 * Checks if domain is allowed.
		 *
		 * @static
		 * @access public
		 */
		public static function domainCheck()
		{
			$serverName = self::pzCore()->pzHttpRequest()->server('SERVER_NAME');

			//domain protection prevents certain rare exploits, where attackers may play with the HEADER information
			//this also helps redirect users when they type example.com instead of www.example.com
			if(!empty($serverName))
			{
				$serverName = trim($serverName);
				$serverHttps = self::pzCore()->pzHttpRequest()->isSecure();
				$serverRequestUri = self::pzCore()->pzHttpRequest()->server('REQUEST_URI');

				$targetRedirectUrl = ($serverHttps?'https://':'http://').self::pzCore()->getSetting('domain_target_domain').$serverRequestUri;

				$allowedDomains = self::pzCore()->getSetting('domain_allowed_domains');

				if(is_array($allowedDomains) || strpos($allowedDomains, ',') !== false)
				{
					if(!is_array($allowedDomains))
					{
						$allowedDomains = array_map('trim', explode(',', $allowedDomains));
					}

					if(count($allowedDomains) > 0)
					{
						$exists = false;

						foreach($allowedDomains as $domain)
						{
							if(strrpos($serverName, $domain) === true)
							{
								$exists = true;
								break;
							}
						}

						if($exists === false)
						{
							self::pzCore()->pzHttpResponse()->redirect($targetRedirectUrl);
						}
					}
				}
				else
				{
					if(strrpos($serverName, $allowedDomains) === false)
					{
						self::pzCore()->pzHttpResponse()->redirect($targetRedirectUrl);
					}
				}
			}
		}

		/**
		 * Check if the visitors IP is whitelisted (if enabled).
		 *
		 * @static
		 * @access public
		 */
		public static function whitelistCheck()
		{
			$whitelistedips = self::pzCore()->getSetting('whitelist_ips');

			if(!is_array($whitelistedips))
			{
				$whitelistedips = array_map('trim', explode(',', $whitelistedips));
			}

			if(self::pzCore()->getSetting('whitelist_auto_allow_host_server_ip') === true)
			{
				$whitelistedips[] = self::pzCore()->pzHttpRequest()->serverIpAddress();
			}

			if(count($whitelistedips) > 0)
			{
				$ip = self::pzCore()->pzHttpRequest()->clientIpAddress();

				$ipFound = false;

				foreach($whitelistedips as $allowedIp)
				{
					if($allowedIp !== '' && $allowedIp === $ip)
					{
						$ipFound = true;
					}
				}

				if($ipFound === false)
				{
					$whatToDo = self::pzCore()->getSetting('whitelist_action');

					if($whatToDo['action'] === 'exit')
					{
						echo $whatToDo['message'];
						exit();
					}
					elseif($whatToDo['action'] === 'url')
					{
						self::pzCore()->pzHttpResponse()->redirect($whatToDo['target']);
					}
				}
			}
		}

		/*
		 * Check if the visitors IP is blacklisted (if enabled).
		 *
		 * @static
		 * @access public
		 */
		public static function blacklistCheck()
		{
			$blacklistedips = self::pzCore()->getSetting('blacklist_ips');

			if(!is_array($blacklistedips))
			{
				$blacklistedips = array_map('trim', explode(',', $blacklistedips));
			}

			if(count($blacklistedips) > 0)
			{
				$ip = self::pzCore()->pzHttpRequest()->clientIpAddress();

				$serverIp = self::pzCore()->pzHttpRequest()->serverIpAddress();

				$ignoreServerIp = self::pzCore()->getSetting('blacklist_ignore_host_server_ip');

				$ipFound = false;

				foreach($blacklistedips as $notallowedIp)
				{
					if($notallowedIp !== '' && ($ignoreServerIp === false || $serverIp !== $ip) && $notallowedIp === $ip)
					{
						$ipFound = true;
					}
				}

				if($ipFound === true)
				{
					$whatToDo = self::pzCore()->getSetting('blacklist_action');

					if($whatToDo['action'] === 'exit')
					{
						echo $whatToDo['message'];

						exit();
					}
					elseif($whatToDo['action'] === 'url')
					{
						self::pzCore()->pzHttpResponse()->redirect($whatToDo['target']);
					}
				}
			}
		}
	}
