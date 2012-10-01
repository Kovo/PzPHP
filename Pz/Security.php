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
	 * @package PzSecurity
	 */
	class PzSecurity extends PzCrypt
	{
		const CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES = 0;
		const CLEAN_HTML_JS_STYLE_COMMENTS = 1;
		const CLEAN_JS_STYLE_COMMENTS = 2;
		const CLEAN_STYLE_COMMENTS = 3;
		const CLEAN_NOTHING = false;
		/**
		 * @param $value
		 * @param int $cleanall
		 * @return mixed|string
		 */
		public function cleanHTML($value, $cleanall = self::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			/*
			* $value } the string to search through
			* $cleanall | flag that dictates what is done to the string ($value)
			* 0 = clean everything (javascript tags, styling tags, comments, html tags, convert all <,> to html entities)
			* 1 = clean everything (javascript tags, styling tags, comments, html tags)
			* 2 = clean almost everything (javascript tags, styling tags, comments)
			* 3 = clean some things (styling tags, comments)
			* false = don't clean anything
			*/
			if($cleanall !== false)
			{
				//empty blacklist array
				$htmlblacklist = array();

				//go through and see what we want cleaned (ideally all)
				if($cleanall === self::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES || $cleanall === self::CLEAN_HTML_JS_STYLE_COMMENTS)
				{
					if($cleanall === self::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
					{
						foreach(array('<:&lt;', '>:&gt;') as $k => $v)
						{
							$ex = explode(':', $v); //explode array item in question
							$value = str_replace($ex[0], $ex[1], $value); //do the replacements
						}
					}

					$htmlblacklist[0] = '@<script[^>]*?>.*?</script>@si'; //bye bye javascript
					$htmlblacklist[2] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$htmlblacklist[3] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($htmlblacklist, '', $value);
					$value = strip_tags($value);
				}
				elseif($cleanall === self::CLEAN_JS_STYLE_COMMENTS)
				{
					$htmlblacklist[0] = '@<script[^>]*?>.*?</script>@si'; //bye bye javascript
					$htmlblacklist[1] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$htmlblacklist[2] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($htmlblacklist, '', $value);
				}
				elseif($cleanall === self::CLEAN_STYLE_COMMENTS)
				{
					$htmlblacklist[0] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$htmlblacklist[1] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($htmlblacklist, '', $value);
				}
			}

			//all done
			return $value;
		}

		/**
		* Function that sanitizes a given query string
		* Do not pass an entire query string to this function, only the individual varaibles that make up the string should be passed
		* @param $dbLinkRes
		* @param $value
		* @param bool $mustBeNumeric
		* @param int $decimalPlaces
		* @param int $cleanall
		* @return array|float|int|mixed|string
		*/
		public function cleanQuery($dbLinkRes, $value, $mustBeNumeric = true, $decimalPlaces = 2, $cleanall = self::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
		{
			if(is_array($value) === false)
			{
				if($mustBeNumeric === true)
				{
					if(is_float($value) === true)
					{
						return bcmul((float)$value, 1, $decimalPlaces);
					}
					else
					{
						return bcmul((int)$value, 1, 0);
					}
				}

				//if magic quotes present, dont strip slashes
				if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() === 1)
				{
					$value = stripslashes($value); //stripslashes
				}

				$value = $this->cleanHTML($value, $cleanall); //clean html stuff

				//good old php function as last defense
				$value = $dbLinkRes->real_escape_string($value);

				//we are done!
				return $value;
			}
			else
			{
				$recallvar = array();

				if(count($value) > 0)
				{
					foreach($value as $key => $val)
					{
						$recallvar[$key] = $this->cleanQuery($dbLinkRes, $val, $mustBeNumeric, $decimalPlaces, $cleanall);
					}
				}

				return $recallvar;
			}
		}

		/**
		 * @return mixed
		 */
		public function getIpAddress()
		{
			if(!empty($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			return $ip;
		}
	}

