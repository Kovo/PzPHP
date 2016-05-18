<?php
class PzPHP_Module_Routing extends PzPHP_Wrapper
{
	/**
	 * @var int
	 */
	const PATTERN = 0;

	/**
	 * @var int
	 */
	const CONTROLLER = 1;

	/**
	 * @var int
	 */
	const ACTION = 2;

	/**
	 * @var int
	 */
	const CONSTRAINTS = 3;

	/**
	 * @var int
	 */
	const RULES = 4;

	/**
	 * @var string
	 */
	const REGEX_TERM_PATTERN = "#<([^>]++)>#";

	/**
	 * @var string
	 */
	const REGEX_TERM_OPT_PATTERN = "#\\(([^()]++)\\)#";

	/**
	 * @var string
	 */
	const REGEX_RULE_ESCAPE = '#[.\\+*?[^\\]${}=!|]#';

	/**
	 * @var string
	 */
	const REGEX_RULE_SEGMENT = '[^/.,;?\n]++';

	/**
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * @var string
	 */
	protected $_siteUrl = '';

	/**
	 * @var string
	 */
	protected $_baseUri = '';

	/**
	 * @var bool
	 */
	protected $_throwExceptionForReqTermMiss = true;

	/**
	 * @var bool
	 */
	protected $_throwExceptionForConstraintTermMiss = true;

	/**
	 * @var string
	 */
	protected $_currentRoute = '';

	/**
	 * @var array
	 */
	protected $_currentTerms = array();

	/**
	 * @var array
	 */
	protected $_exposed = array();

	/**
	 * @var bool
	 */
	protected $_secure = false;

	/**
	 * @return $this
	 */
	public function secure()
	{
		$this->_secure = true;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function unsecure()
	{
		$this->_secure = false;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function secured()
	{
		return $this->_secure;
	}

	/**
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	protected function _overrideListen()
	{
		$class	= $_GET['controller'];
		$method	= $_GET['action'];
		$terms	= $_GET['terms'];

		if($class !== null && $method !== null)
		{
			if(class_exists($class) && method_exists($class, $method))
			{
				if($terms === null)
				{
					$arguments = array();
				}
				elseif(!is_array($terms))
				{
					$arguments = array($terms);
				}
				else
				{
					$arguments = $terms;
				}

				$classObj = new $class($this->pzphp());

				$arguments = PzPHP_Helper_Array::insertValueAtPos($arguments, 1, array('controllerCalled'=> $class, 'actionCalled'=> $method));

				if(method_exists($classObj, 'before'))
				{
					call_user_func_array(
						array($classObj, 'before'),
						$arguments
					);
				}

				$return = call_user_func_array(
					array($classObj, $method),
					$arguments
				);

				$arguments['returnFromAction'] = $return;
				if(method_exists($classObj, 'after'))
				{
					call_user_func_array(
						array($classObj, 'after'),
						$arguments
					);
				}

				return $return;
			}
			else
			{
				throw new PzPHP_Exception('Requested class or action does not exist.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_CLASS_OR_ACTION);
			}
		}
	}

	/**
	 * @return array
	 */
	protected function _listenParseURI()
	{
		$resultFromParse	= array(
			'foundKey'	=> null,
			'terms'		=> array()
		);

		foreach($this->_routes as $routeKey => $routeValues)
		{
			if(preg_match($routeValues[self::RULES], $this->stripBaseUri($this->getUri()), $terms))
			{
				$resultFromParse['foundKey']			= $routeKey;
				$resultFromParse['finalRouteValues']	= $routeValues;
			}
			else
			{
				continue;
			}

			## Clean up the terms
			if(is_array($terms) && !empty($terms))
			{
				foreach($terms as $offset => $term)
				{
					if(is_int($offset))
					{
						unset($terms[$offset]);
					}
					else
					{
						$resultFromParse['terms'][$offset]	= $term;
					}
				}
			}

			## Save current values
			$this->_currentRoute    = $routeKey;
			$this->_currentTerms    = isset($resultFromParse['terms'])? $resultFromParse['terms']:array();

			break;
		}

		return $resultFromParse;
	}

	/**
	 * @return string
	 */
	public function getCurrentRoute()
	{
		return $this->_currentRoute;
	}

	/**
	 * @return array
	 */
	public function getCurrentTerms()
	{
		return $this->_currentTerms;
	}

	/**
	 * @param bool $allowGetOverride
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	public function listen($allowGetOverride = false)
	{
		if($allowGetOverride)
		{
			$this->_overrideListen();
		}

		if(!empty($this->_routes))
		{
			$resultFromParse = $this->_listenParseURI();

			if(!isset($resultFromParse['finalRouteValues']))
			{
				$resultFromParse['finalRouteValues'] = array();
			}

			if(!isset($resultFromParse['terms']['lang']))
			{
				$resultFromParse['terms'] = array('lang' => '')+$resultFromParse['terms'];
			}

			if($resultFromParse['foundKey'] !== null)
			{
				return $this->_listenFinalExecutions($resultFromParse);
			}
			else
			{
				throw new PzPHP_Exception('No valid route found for this request.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE);
			}
		}
		else
		{
			throw new PzPHP_Exception('No routes to match this request to.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * @param $identifier
	 * @param array $terms
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	public function reroute($identifier, array $terms = array())
	{
		if(!empty($this->_routes))
		{
			if(isset($this->_routes[$identifier]))
			{
				$finalRouteValues	= array(
					'foundKey'			=> $identifier,
					'finalRouteValues'	=> $this->_routes[$identifier],
					'terms'				=> is_array($terms)? $terms:array()
				);

				if(!isset($finalRouteValues['terms']['lang']))
				{
					$finalRouteValues['terms'] = array('lang' => '')+$finalRouteValues['terms'];
				}

				return $this->_listenFinalExecutions($finalRouteValues);
			}
			else
			{
				throw new PzPHP_Exception('Route "'.$identifier.'" not found.', PzPHP_Helper_Codes::ROUTING_ERROR_INVALID_ROUTE);
			}

		}
		else
		{
			throw new PzPHP_Exception('No routes to match this request to.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * @param $pattern
	 * @param $constraints
	 * @return string
	 */
	protected function _generateRules($pattern, $constraints)
	{
		## Strip both slashes
		$pattern	= $this->stripBothSlashes($pattern);

		## Treat the pattern literal, except for keys and optional parts.
		$pattern	= preg_replace(self::REGEX_RULE_ESCAPE, '\\\\$0', $pattern);

		## Make optional parts of the URI non-capturing and optional
		$pattern	= str_replace(array('(', ')'), array('(?:', ')?'), $pattern);

		## Default regex for keys
		$pattern	= str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_RULE_SEGMENT.')'), $pattern);

		## Constraints
		if(is_array($constraints) && !empty($constraints))
		{
			foreach($constraints as $term => $constraint)
			{
				$pattern	= str_replace('<'.$term.'>'.self::REGEX_RULE_SEGMENT, '<'.$term.'>'.$constraint,$pattern);
			}
		}

		return '#^'.$pattern.'$#uD';
	}

	/**
	 * @param $resultFromParse
	 *
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	protected function _listenFinalExecutions($resultFromParse)
	{
		if(class_exists($resultFromParse['finalRouteValues'][self::CONTROLLER]) && method_exists($resultFromParse['finalRouteValues'][self::CONTROLLER], $resultFromParse['finalRouteValues'][self::ACTION]))
		{
			$classObj = new $resultFromParse['finalRouteValues'][self::CONTROLLER]($this->pzphp());

			$resultFromParse['terms'] = PzPHP_Helper_Array::insertValueAtPos($resultFromParse['terms'], 1, array('controllerCalled'=> $resultFromParse['finalRouteValues'][self::CONTROLLER], 'actionCalled'=> $resultFromParse['finalRouteValues'][self::ACTION]));

			if(method_exists($classObj, 'before'))
			{
				call_user_func_array(
					array($classObj, 'before'),
					$resultFromParse['terms']
				);
			}

			if(isset($resultFromParse['terms']['lang']))
			{
				unset($resultFromParse['terms']['lang']);
			}

			if(isset($resultFromParse['terms']['controllerCalled']))
			{
				unset($resultFromParse['terms']['controllerCalled']);
			}

			if(isset($resultFromParse['terms']['actionCalled']))
			{
				unset($resultFromParse['terms']['actionCalled']);
			}

			$return = call_user_func_array(
				array($classObj, $resultFromParse['finalRouteValues'][self::ACTION]),
				$resultFromParse['terms']
			);

			$resultFromParse['terms']['returnFromAction'] = $return;
			if(method_exists($classObj, 'after'))
			{
				call_user_func_array(
					array($classObj, 'after'),
					$resultFromParse['terms']
				);
			}

			return $return;
		}
		else
		{
			throw new PzPHP_Exception('Requested class or action does not exist.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_CLASS_OR_ACTION);
		}
	}

	/**
	 * @param $uri
	 * @return string
	 */
	public function stripBaseUri($uri)
	{
		$uri = $this->stripBothSlashes($uri);
		$baseUri = $this->stripTrailingSlash($this->_baseUri);

		if($this->_baseUri !== '')
		{
			$baseUriExploded = explode('/', $baseUri);

			$uriExploded = explode('/', $uri);

			foreach($baseUriExploded as $key => $value)
			{
				if(isset($uriExploded[$key]) && $uriExploded[$key] === $value)
				{
					unset($uriExploded[$key]);
				}
			}

			$uri = implode('/', $uriExploded);
		}

		return $uri;
	}

	/**
	 * @param       $identifier
	 * @param       $pattern
	 * @param       $controller
	 * @param       $action
	 * @param array $constraints
	 * @param bool  $expose
	 *
	 * @return $this
	 */
	public function add($identifier, $pattern, $controller, $action, array $constraints = array(), $expose = false)
	{
		if(!isset($this->_routes[$identifier]))
		{
			$this->set($identifier, $pattern, $controller, $action, $constraints);
		}

		if($expose)
		{
			$this->_exposed[$identifier] = $pattern;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSiteUrl()
	{
		return $this->_siteUrl;
	}

	/**
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->_baseUri;
	}

	/**
	 * @return array
	 */
	public function getExposed()
	{
		return $this->_exposed;
	}

	/**
	 * @param $identifier
	 * @param $pattern
	 * @param $controller
	 * @param $action
	 * @param $constraints
	 * @return $this
	 */
	public function set($identifier, $pattern, $controller, $action, $constraints)
	{
		$this->_routes[$identifier] = array(
			self::PATTERN => $this->stripBothSlashes($pattern),
			self::CONTROLLER => $controller,
			self::ACTION => $action,
			self::CONSTRAINTS => $constraints,
			self::RULES => $this->_generateRules($pattern, $constraints)
		);

		return $this;
	}

	/**
	 * @param $identifier
	 * @return $this
	 */
	public function remove($identifier)
	{
		unset($this->_routes[$identifier]);

		return $this;
	}

	/**
	 * @param $baseUrl
	 * @return $this
	 */
	public function setSiteUrl($baseUrl)
	{
		$this->_siteUrl = $this->stripLeadingSlash($this->addTrailingSlash($baseUrl));

		return $this;
	}

	/**
	 * @param $baseUri
	 * @return $this
	 */
	public function setBaseUri($baseUri)
	{
		$this->_baseUri = $this->stripLeadingSlash($this->addTrailingSlash($baseUri));

		return $this;
	}

	/**
	 * @return $this
	 */
	public function enableExceptionsForReqTermMiss()
	{
		$this->_throwExceptionForReqTermMiss = true;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableExceptionsForReqTermMiss()
	{
		$this->_throwExceptionForReqTermMiss = false;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function enableExceptionsForConstraintTermMiss()
	{
		$this->_throwExceptionForConstraintTermMiss = true;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableExceptionsForConstraintTermMiss()
	{
		$this->_throwExceptionForConstraintTermMiss = false;

		return $this;
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function stripTrailingSlash($string)
	{
		return (
		substr($string, -1) === '/'?
			substr($string, 0, -1):
			$string
		);
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function addTrailingSlash($string)
	{
		return (
		substr($string, -1) !== '/'?
			$string.'/':
			$string
		);
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function stripLeadingSlash($string)
	{
		return (
		substr($string, 0, 1) === '/'?
			substr($string, 1):
			$string
		);
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function addLeadingSlash($string)
	{
		return (
		substr($string, 0, 1) !== '/'?
			'/'.$string:
			$string
		);
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function stripBothSlashes($string)
	{
		$string = $this->stripTrailingSlash($string);
		$string = $this->stripLeadingSlash($string);

		return $string;
	}

	/**
	 * @param $identifier
	 * @param array $terms
	 * @param null $overrideSiteUrl
	 * @param null $secure
	 * @return string
	 * @throws PzPHP_Exception
	 */
	public function get($identifier, array $terms = array(), $overrideSiteUrl = null, $secure = null)
	{
		if($overrideSiteUrl === null)
		{
			$siteUrl = $this->_siteUrl;
		}
		else
		{
			$siteUrl = $overrideSiteUrl;
		}

		$siteUrl = $this->stripBaseUri($siteUrl);

		if(($this->_secure || $secure) && $secure !== false)
		{
			$siteUrl = str_replace('http://','https://', $siteUrl);
		}

		if(isset($this->_routes[$identifier]))
		{
			if(isset($this->_routes[$identifier][self::CONSTRAINTS]['lang']) && !isset($terms['lang']))
			{
				$terms['lang'] = $this->_PzPHP->locale()->getCurrentLocale();
			}

			$mergedPattern = $this->_mergeTermsWithPattern($terms, $this->_routes[$identifier][self::PATTERN], $this->_routes[$identifier][self::CONSTRAINTS]);

			return $this->addTrailingSlash($siteUrl.'/'.$mergedPattern);
		}
		else
		{
			throw new PzPHP_Exception('Route "'.$identifier.'" not found.', PzPHP_Helper_Codes::ROUTING_ERROR_NOT_FOUND);
		}
	}

	/**
	 * @param $identifier
	 *
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	public function fetch($identifier)
	{
		if(isset($this->_routes[$identifier]))
		{
			return $this->_routes[$identifier];
		}
		else
		{
			throw new PzPHP_Exception('Route "'.$identifier.'" not found.', PzPHP_Helper_Codes::ROUTING_ERROR_NOT_FOUND);
		}
	}

	/**
	 * @param array $terms
	 * @param $pattern
	 * @param array $constraints
	 * @return mixed
	 * @throws PzPHP_Exception
	 */
	protected function _mergeTermsWithPattern(array $terms, $pattern, array $constraints = array())
	{
		$finalUri	= $pattern;

		## Optional terms
		while(preg_match(self::REGEX_TERM_OPT_PATTERN, $finalUri, $match))
		{
			$patternPart	= isset($match[0])? $match[0]:null;
			$patternTerm	= isset($match[1])? $match[1]:null;

			if(preg_match(self::REGEX_TERM_PATTERN, $patternTerm, $match) !== false)
			{
				$term		= isset($match[0])? $match[0]:null;
				$termName	= isset($match[1])? $match[1]:null;

				if(isset($terms[$termName]))
				{
					$mergedTerm	= htmlentities(str_replace($term, $terms[$termName], $patternTerm));
				}
				else
				{
					$mergedTerm	= null;
				}

				$finalUri	= str_replace($patternPart, $mergedTerm, $finalUri);
			}
		}

		## Mandatory terms
		while(preg_match(self::REGEX_TERM_PATTERN, $finalUri, $match))
		{
			$term		= isset($match[0])? $match[0]:null;
			$termName	= isset($match[1])? $match[1]:null;

			## Required term
			if(!isset($terms[$termName]))
			{
				if($this->_throwExceptionForReqTermMiss)
				{
					throw new PzPHP_Exception('Term requirement failed for "'.$termName.'".', PzPHP_Helper_Codes::ROUTING_ERROR_MISSING_REQ_TERMS);
				}
				else
				{
					$finalUri	= str_replace($term, htmlentities($term), $finalUri);

					continue;
				}
			}

			## Constraint
			if(!$this->_constraintCheck($constraints, $term, $terms[$termName]))
			{
				if($this->_throwExceptionForConstraintTermMiss)
				{
					throw new PzPHP_Exception('Term constraint rule failed for "'.$termName.'". Value was "'.$terms[$termName].'".', PzPHP_Helper_Codes::ROUTING_ERROR_REGEX_MATCH_ERROR);
				}
				else
				{
					$finalUri	= str_replace($term, htmlentities($term), $finalUri);

					continue;
				}
			}

			$finalUri	= str_replace($term, htmlentities($terms[$termName]), $finalUri);
		}

		return $finalUri;
	}

	/**
	 * @param $constraints
	 * @param $term
	 * @param $value
	 * @return bool
	 */
	protected function _constraintCheck($constraints, $term, $value)
	{
		$term	= str_replace(array('<','>','(',')'),'',$term);

		if(isset($constraints[$term]) && preg_match("#^".$constraints[$term]."$#", $value) !== 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * @return string
	 * @throws PzPHP_Exception
	 */
	public function getUri()
	{
		if(isset($_SERVER['REDIRECT_URL']))
		{
			return trim($_SERVER['REDIRECT_URL']);
		}
		elseif($_SERVER['REQUEST_URI'])
		{
			return trim($_SERVER['REQUEST_URI']);
		}
		else
		{
			throw new PzPHP_Exception('Cannot get URI.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_URI);
		}
	}
}