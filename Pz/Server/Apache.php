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
	 * @package Pz_Server_Apache
	 */
	class Pz_Server_Apache
	{
		/**
		 * @return mixed
		 */
		public static function childTerminate()
		{
			return apache_child_terminate();
		}

		/**
		 * @return mixed
		 */
		public static function loadedModules()
		{
			return apache_get_modules();
		}

		/**
		 * @return mixed
		 */
		public static function version()
		{
			return apache_get_version();
		}

		/**
		 * @param $filename
		 *
		 * @return mixed
		 */
		public static function virtual($filename)
		{
			return virtual($filename);
		}

		/**
		 * @return mixed
		 */
		public static function getAllRequestHeaders()
		{
			return apache_request_headers();
		}

		/**
		 * @return mixed
		 */
		public static function getAllResponseHeaders()
		{
			return apache_response_headers();
		}

		/**
		 * @param      $varname
		 * @param      $value
		 * @param bool $walktotop
		 *
		 * @return mixed
		 */
		public static function setEnvVar($varname, $value, $walktotop = true)
		{
			return apache_setenv($varname, $value, $walktotop);
		}

		/**
		 * @param      $varname
		 * @param bool $walktotop
		 *
		 * @return mixed
		 */
		public static function getEnvVar($varname, $walktotop = true)
		{
			return apache_getenv($varname, $walktotop);
		}

		/**
		 * @return mixed
		 */
		public static function resetTimeout()
		{
			return apache_reset_timeout();
		}

		/**
		 * @param $noteName
		 *
		 * @return mixed
		 */
		public static function tableGet($noteName)
		{
			return apache_note($noteName);
		}

		/**
		 * @param $noteName
		 * @param $noteValue
		 *
		 * @return mixed
		 */
		public static function tableSet($noteName, $noteValue)
		{
			return apache_note($noteName, $noteValue);
		}

		/**
		 * @param $filename
		 *
		 * @return mixed
		 */
		public static function lookupUri($filename)
		{
			return apache_lookup_uri($filename);
		}
	}
