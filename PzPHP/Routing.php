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
	 * The Routing class allows your application to use pretty urls, and route them to the correct classes.
	 *
	 * These classes can be controllers, or any other type of object.
	 */
	class PzPHP_Routing extends PzPHP_Wrapper
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
		const ERROR_NO_URI = 100;

		/**
		 * @var int
		 */
		const ERROR_NO_ROUTE = 101;

		/**
		 * @var int
		 */
		const ERROR_REGEX_ERROR = 102;

		/**
		 * @var int
		 */
		const ERROR_MISSING_REQ_TERMS = 103;

		/**
		 * @var int
		 */
		const ERROR_REGEX_MATCH_ERROR = 104;

		/**
		 * @var int
		 */
		const ERROR_NO_CLASS_OR_ACTION = 105;

		/**
		 * @var string
		 */
		const REGEX_TERM_PATTERN = "#(\\()?<[^>]++>(\\))?#";

		/**
		 * @var string
		 */
		const REGEX_TERM_OPT_PATTERN = "#\\(<[^>]++>\\)#";

		/**
		 * An array that holds all routing information.
		 *
		 * @access protected
		 * @var array
		 */
		protected $_routes = array();

		/**
		 * The site url for all routes.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_siteUrl = '';

		/**
		 * The base uri for all routes.
		 *
		 * @access protected
		 * @var string
		 */
		protected $_baseUri = '';

		/**
		 * @access protected
		 * @var bool
		 */
		protected $_throwExceptionForReqTermMiss = true;

		/**
		 * @access protected
		 * @var bool
		 */
		protected $_throwExceptionForConstraintTermMiss = true;

		/**
		 * This method scans the request uri and attempts to match it to a registered route.
		 *
		 * This method scans the rquest uri and attempts to match it to a registered route. Once a route is found, the specified class and method are executed with the found terms passed to it.
		 *
		 * @access public
		 * @var $allowGetOverride
		 * @return mixed
		 * @throws Exception
		 */
		public function listen($allowGetOverride = false)
		{
			if($allowGetOverride)
			{
				$class = $this->pzphp()->pz()->pzHttpRequest()->get('controller');
				$method = $this->pzphp()->pz()->pzHttpRequest()->get('action');
				$terms = $this->pzphp()->pz()->pzHttpRequest()->get('terms');

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

						if(method_exists($classObj, 'before'))
						{
							$classObj->before($method);
						}

						$return = call_user_func_array(
							array($classObj, $method),
							$arguments
						);

						if(method_exists($classObj, 'after'))
						{
							$classObj->after($method, $return);
						}

						return $return;
					}
					else
					{
						throw new PzPHP_Exception('Requested class or action does not exist.', self::ERROR_NO_CLASS_OR_ACTION);
					}
				}
			}

			if(count($this->_routes) > 0)
			{
				$uriParts = explode('/', $this->stripBaseUri($this->getUri()));
				$foundKey = null;
				$terms = array();

				foreach($this->_routes as $routeKey => $routeValues)
				{
					$patternParts = explode('/', $routeValues[self::PATTERN]);
					$broken = false;

					foreach($patternParts as $order => $partString)
					{
						if(!$this->_isPartATerm($partString))
						{
							if(!isset($uriParts[$order]) || $uriParts[$order] !== $partString)
							{
								$broken = true;
								break;
							}
						}
						else
						{
							if(!$this->_isPartAnOptionalTerm($partString))
							{
								if(!isset($uriParts[$order]))
								{
									$broken = true;
									break;
								}
								else
								{
									if(!$this->_constraintCheck($routeValues[self::CONSTRAINTS],$partString,$uriParts[$order]))
									{
										$broken = true;
										break;
									}
									else
									{
										$terms[] = $uriParts[$order];
									}
								}
							}
							else
							{
								if(isset($uriParts[$order]))
								{
									if(!$this->_constraintCheck($routeValues[self::CONSTRAINTS],$partString,$uriParts[$order]))
									{
										$broken = true;
										break;
									}
									else
									{
										$terms[] = $uriParts[$order];
									}
								}
							}
						}
					}

					if(!$broken)
					{
						$foundKey = $routeKey;

						break;
					}
				}

				if($foundKey !== null)
				{
					if(class_exists($routeValues[self::CONTROLLER]) && method_exists($routeValues[self::CONTROLLER], $routeValues[self::ACTION]))
					{
						$classObj = new $routeValues[self::CONTROLLER]($this->pzphp());

						if(method_exists($classObj, 'before'))
						{
							$classObj->before($routeValues[self::ACTION]);
						}

						$return = call_user_func_array(
							array($classObj, $routeValues[self::ACTION]),
							$terms
						);

						if(method_exists($classObj, 'after'))
						{
							$classObj->after($routeValues[self::ACTION], $return);
						}

						return $return;
					}
					else
					{
						throw new PzPHP_Exception('Requested class or action does not exist.', self::ERROR_NO_CLASS_OR_ACTION);
					}
				}
				else
				{
					throw new PzPHP_Exception('No valid route found for this request.', self::ERROR_NO_ROUTE);
				}
			}
			else
			{
				throw new PzPHP_Exception('No routes to match this request to.', self::ERROR_NO_ROUTE);
			}
		}

		/**
		 * @access public
		 * @param $uri
		 *
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
		 * Adds a new route.
		 *
		 * If the identifier is not found, a new route is added. If the identifier exists, nothing happens.
		 *
		 * @access public
		 * @param $identifier
		 * @param $pattern
		 * @param $controller
		 * @param $action
		 * @param array $constraints
		 * @return PzPHP_Routing
		 */
		public function add($identifier, $pattern, $controller, $action, array $constraints = array())
		{
			if(!isset($this->_routes[$identifier]))
			{
				$this->set($identifier, $pattern, $controller, $action, $constraints);
			}

			return $this;
		}

		/**
		 * Adds a new route or replaces an existing one.
		 *
		 * If the identifier is not found, a new route is added. If the identifier exists, it is overwritten with the new data.
		 *
		 * @access public
		 * @param $identifier
		 * @param $pattern
		 * @param $controller
		 * @param $action
		 * @param $constraints
		 * @return PzPHP_Routing
		 */
		public function set($identifier, $pattern, $controller, $action, $constraints)
		{
			$this->_routes[$identifier] = array(
				self::PATTERN => $this->stripBothSlashes($pattern),
				self::CONTROLLER => $controller,
				self::ACTION => $action,
				self::CONSTRAINTS => $constraints,
			);

			return $this;
		}

		/**
		 * Removes a defined route.
		 *
		 * If you need to remove a route from the Routing class, this is where you do it.
		 *
		 * @access public
		 * @param $identifier
		 * @return PzPHP_Routing
		 */
		public function remove($identifier)
		{
			unset($this->_routes[$identifier]);

			return $this;
		}

		/**
		 * Sets the base url used for all routes.
		 *
		 * @access public
		 * @param $baseUrl
		 */
		public function setSiteUrl($baseUrl)
		{
			$this->_siteUrl = $this->stripLeadingSlash($this->addTrailingSlash($baseUrl));
		}

		/**
		 * Sets the base uri used for all routes.
		 *
		 * @access public
		 * @param $baseUri
		 */
		public function setBaseUri($baseUri)
		{
			$this->_baseUri = $this->stripLeadingSlash($this->addTrailingSlash($baseUri));
		}

		/**
		 * Enables exception throwing when a required term is not found.
		 *
		 * @access public
		 */
		public function enableExceptionsForReqTermMiss()
		{
			$this->_throwExceptionForReqTermMiss = true;
		}

		/**
		 * Disables exception throwing when a required term is not found.
		 *
		 * @access public
		 */
		public function disableExceptionsForReqTermMiss()
		{
			$this->_throwExceptionForReqTermMiss = false;
		}

		/**
		 * Enables exception throwing when a constraint for a term fails validation.
		 *
		 * @access public
		 */
		public function enableExceptionsForConstraintTermMiss()
		{
			$this->_throwExceptionForConstraintTermMiss = true;
		}

		/**
		 * Disables exception throwing when a constraint for a term fails validation.
		 *
		 * @access public
		 */
		public function disableExceptionsForConstraintTermMiss()
		{
			$this->_throwExceptionForConstraintTermMiss = false;
		}

		/**
		 * @access public
		 * @param $string
		 *
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
		 * @access public
		 * @param $string
		 *
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
		 * @access public
		 * @param $string
		 *
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
		 * @access public
		 * @param $string
		 *
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
		 * @access public
		 * @param $string
		 *
		 * @return string
		 */
		public function stripBothSlashes($string)
		{
			$string = $this->stripTrailingSlash($string);
			$string = $this->stripLeadingSlash($string);

			return $string;
		}

		/**
		 * @param       $identifier
		 * @param array $terms
		 * @param null  $overrideSiteUrl
		 *
		 * @return string
		 * @throws PzPHP_Exception
		 */
		public function get($identifier, array $terms = array(), $overrideSiteUrl = null)
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

			if(isset($this->_routes[$identifier]))
			{
				$mergedPattern = $this->_mergeTermsWithPattern($terms, $this->_routes[$identifier][self::PATTERN], $this->_routes[$identifier][self::CONSTRAINTS]);

				return $this->addTrailingSlash($siteUrl.'/'.$mergedPattern);
			}
			else
			{
				throw new PzPHP_Exception('Route not found.', self::ERROR_NO_ROUTE);
			}
		}

		/**
		 * @access protected
		 * @param $partString
		 *
		 * @return bool
		 */
		protected function _isPartATerm($partString)
		{
			if(preg_match(self::REGEX_TERM_PATTERN, $partString) !== 1)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		/**
		 * @access protected
		 * @param $partString
		 *
		 * @return bool
		 */
		protected function _isPartAnOptionalTerm($partString)
		{
			if(preg_match(self::REGEX_TERM_OPT_PATTERN, $partString) !== 1)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		/**
		 * @access protected
		 * @param array $terms
		 * @param       $pattern
		 * @param array $constraints
		 *
		 * @return string
		 * @throws Exception
		 */
		protected function _mergeTermsWithPattern(array $terms, $pattern, array $constraints = array())
		{
			if(count($terms) === 0)
			{
				return $pattern;
			}
			else
			{
				$patternParts = explode('/', $pattern);
				$finalUri = '';

				foreach($patternParts as $partString)
				{
					if(!$this->_isPartATerm($partString))
					{
						$finalUri .= $partString.'/';
					}
					else
					{
						$found = false;

						foreach($terms as $term => $value)
						{
							if($this->_termMatchesPart($partString, $term))
							{
								if(!$this->_constraintCheck($constraints, $term, $value))
								{
									if($this->_throwExceptionForConstraintTermMiss)
									{
										throw new PzPHP_Exception('Term constraint rule failed for "'.$term.'". Value was "'.$value.'".', self::ERROR_REGEX_MATCH_ERROR);
									}
									else
									{
										$finalUri .= htmlentities($partString).'/';

										$found = true;

										break;
									}
								}

								$finalUri .= $value.'/';

								$found = true;

								break;
							}
						}

						if(!$found && !$this->_isPartAnOptionalTerm($partString))
						{
							if($this->_throwExceptionForReqTermMiss)
							{
								throw new PzPHP_Exception('Could not fulfill required terms.', self::ERROR_MISSING_REQ_TERMS);
							}
							else
							{
								$finalUri .= htmlentities($partString).'/';
							}
						}
					}
				}

				return $finalUri;
			}
		}

		/**
		 * @access protected
		 * @param $partString
		 * @param $term
		 *
		 * @return bool
		 */
		protected function _termMatchesPart($partString, $term)
		{
			return (strpos($partString, '<'.$term.'>') !== false || strpos($partString, '(<'.$term.'>)') !== false);
		}

		/**
		 * @access protected
		 * @param $constraints
		 * @param $term
		 * @param $value
		 *
		 * @return bool
		 */
		protected function _constraintCheck($constraints, $term, $value)
		{
			if(isset($constraints[$term]) && preg_match("#".$constraints[$term]."#", $value) !== 1)
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		/**
		 * Retrieves the current page URI.
		 *
		 * @access public
		 * @return mixed
		 * @throws Exception
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
				throw new PzPHP_Exception('Cannot get URI.', self::ERROR_NO_URI);
			}
		}
	}
