<?php
/**
 * Website: http://www.pzphp.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 *
 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
 * @package PzPHP
 */
/**
 * The database class allows you to send queries to a database server, run through results, and a lot more.
 */
class PzPHP_Module_Db extends PzPHP_Wrapper
{

	public function sanitizeNumeric($value, $decimalPlaces = 2, $id = -1)
	{
		switch($this->_databaseMethod)
		{
/*			case PzPHP_Config::get('DATABASE_MYSQLI'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			case PzPHP_Config::get('DATABASE_MYSQL'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);;
			case self::PDO:
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			default:
				return false;*/
		}
	}

		/**
		 * Expects to be passed a non-numeric value to make sure it is safe.
		 *
		 * @access public
		 * @param mixed $value
		 * @param int $cleanHtmlLevel
		 * @param int $id
		 * @return mixed
		 */
	public function sanitizeNonNumeric($value, $cleanHtmlLevel = PzPHP_Library_Security_Cleanse::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES, $id = -1)
	{
		switch($this->_databaseMethod)
		{
		/*	case PzPHP_Config::get('DATABASE_MYSQLI'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			case PzPHP_Config::get('DATABASE_MYSQL'):
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);;
			case self::PDO:
				return PzPHP_Library_Security_Cleanse::cleanQuery(

				);
			default:
				return false;*/
		}
	}
}
