<?php
class PzPHP_Library_Server_Apache
{
	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function childTerminate()
	{
		return apache_child_terminate();
	}

	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function loadedModules()
	{
		return apache_get_modules();
	}

	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function version()
	{
		return apache_get_version();
	}

	/**
	 * @static
	 * @access public
	 * @param string $filename
	 * @return mixed
	 */
	public static function virtual($filename)
	{
		return virtual($filename);
	}

	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function getAllRequestHeaders()
	{
		return apache_request_headers();
	}

	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function getAllResponseHeaders()
	{
		return apache_response_headers();
	}

	/**
	 * @static
	 * @access public
	 * @param string $varname
	 * @param mixed $value
	 * @param bool $walktotop
	 * @return mixed
	 */
	public static function setEnvVar($varname, $value, $walktotop = true)
	{
		return apache_setenv($varname, $value, $walktotop);
	}

	/**
	 * @static
	 * @access public
	 * @param string $varname
	 * @param bool $walktotop
	 * @return mixed
	 */
	public static function getEnvVar($varname, $walktotop = true)
	{
		return apache_getenv($varname, $walktotop);
	}

	/**
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function resetTimeout()
	{
		return apache_reset_timeout();
	}

	/**
	 * @static
	 * @access public
	 * @param string $noteName
	 * @return mixed
	 */
	public static function tableGet($noteName)
	{
		return apache_note($noteName);
	}

	/**
	 * @static
	 * @access public
	 * @param string $noteName
	 * @param string $noteValue
	 * @return mixed
	 */
	public static function tableSet($noteName, $noteValue)
	{
		return apache_note($noteName, $noteValue);
	}

	/**
	 * @static
	 * @access public
	 * @param string $filename
	 * @return mixed
	 */
	public static function lookupUri($filename)
	{
		return apache_lookup_uri($filename);
	}
}
