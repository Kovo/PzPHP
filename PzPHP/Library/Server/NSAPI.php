<?php
class PzPHP_Library_Server_NSAPI
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
