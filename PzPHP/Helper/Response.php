<?php
class PzPHP_Helper_Response
{
	/**
	 * @param      $url
	 * @param bool $exit
	 */
	public static function redirect($url, $exit = true)
	{
		global $_PZPHP;

		ob_end_clean();

		if($_PZPHP->request()->isAjax())
		{
			echo self::jsonRedirect($url);

			if($exit)
			{
				exit();
			}
		}
		else
		{
			$_PZPHP->response()->redirect($url, $exit);
		}
	}

	/**
	 * @param $data
	 * @param $code
	 *
	 * @return string
	 */
	public static function json($data, $code)
	{
		return json_encode(array('code' => $code, 'data' => $data));
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public static function jsonRedirect($data)
	{
		return self::json($data, PzPHP_Helper_Codes::ROUTING_ERROR_RESPONSE_REDIRECT);
	}

	/**
	 * @param     $content
	 * @param     $subject
	 * @param int $code
	 *
	 * @return string
	 */
	public static function jsonSuccess($content, $subject, $code = PzPHP_Helper_Codes::ROUTING_SUCCESS)
	{
		return self::json(array(
			'content' => $content,
			'subject' => $subject
		), $code);
	}

	/**
	 * @param     $content
	 * @param     $subject
	 * @param int $code
	 *
	 * @return string
	 */
	public static function jsonError($content, $subject, $code = PzPHP_Helper_Codes::ROUTING_ERROR)
	{
		return self::json(array(
			'content' => $content,
			'subject' => $subject
		), $code);
	}

	/**
	 * @param $backupURL
	 *
	 * @return null
	 */
	public static function getReferer($backupURL)
	{
		global $_PZPHP;

		$referer = $_PZPHP->request()->server('HTTP_REFERER');
		$currentRoute = $_PZPHP->routing()->get($_PZPHP->routing()->getCurrentRoute());

		if(empty($referer) || strpos($referer, PzPHP_Config::get('ROOT_URL')) === false || $referer === $currentRoute || strpos($referer, $currentRoute) !== false || strpos($currentRoute, $referer) !== false)
		{
			return $backupURL;
		}
		else
		{
			return $referer;
		}
	}
}
