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
	 * @package Pz_Server_NSAPI
	 */
	class Pz_Server_NSAPI
	{
		/**
		 * @param $uri
		 *
		 * @return mixed
		 */
		public static function virtual($uri)
		{
			return nsapi_virtual($uri);
		}

		/**
		 * @return mixed
		 */
		public static function getAllRequestHeaders()
		{
			return nsapi_request_headers();
		}

		/**
		 * @return mixed
		 */
		public static function getAllResponseHeaders()
		{
			return nsapi_response_headers();
		}
	}
