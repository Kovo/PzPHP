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
	 * A collection of methods allowing you to interact directly with Netscape/iPlanet/Sun servers.
	 */
	class Pz_Server_NSAPI
	{
		/**
		 * @static
		 * @access public
		 * @param string $uri
		 * @return mixed
		 */
		public static function virtual($uri)
		{
			return nsapi_virtual($uri);
		}

		/**
		 * @static
		 * @access public
		 * @return mixed
		 */
		public static function getAllRequestHeaders()
		{
			return nsapi_request_headers();
		}

		/**
		 * @static
		 * @access public
		 * @return mixed
		 */
		public static function getAllResponseHeaders()
		{
			return nsapi_response_headers();
		}
	}
