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
		 * @var string
		 */
		const REGEX_TERM_PATTERN = "#(\\()?<[^>]++>(\\))?#";

		/**
		 * @var string
		 */
		const REGEX_TERM_OPT_PATTERN = "#\\(<[^>]++>\\)#";

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
		protected $_exposed = array();

		/**
		 * @return mixed
		 * @throws PzPHP_Exception
		 */
		protected function _overrideListen()
		{
			$class = $_GET['controller'];
			$method = $_GET['action'];
			$terms = $_GET['terms'];

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

					$arguments['actionCalled'] = $method;
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
			$resultFromParse = array();
			$uriParts = explode('/', $this->stripBaseUri($this->getUri()));
			$uriPartsCount = count($uriParts);
			$resultFromParse['foundKey'] = null;
			$resultFromParse['terms'] = array();

			foreach($this->_routes as $routeKey => $routeValues)
			{
				$patternParts = explode('/', $routeValues[self::PATTERN]);
				$broken = false;
				$uriHits = 0;

				foreach($patternParts as $order => $partString)
				{
					if(!$this->_isPartATerm($partString))
					{
						if(!isset($uriParts[$order]) || $uriParts[$order] !== $partString || $uriParts[$order] == '')
						{
							$broken = true;
							break;
						}

						$uriHits++;
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
								if(!$this->_constraintCheck($routeValues[self::CONSTRAINTS],$partString,$uriParts[$order]) || $uriParts[$order] == '')
								{
									$broken = true;
									break;
								}
								else
								{
									$resultFromParse['terms'][str_replace(array('(',')','<','>'), '', $partString)] = $uriParts[$order];
								}

								$uriHits++;
							}
						}
						else
						{
							if(isset($uriParts[$order]))
							{
								if((!empty($uriParts[$order]) && !$this->_constraintCheck($routeValues[self::CONSTRAINTS],$partString,$uriParts[$order])) || $uriParts[$order] == '')
								{
									$broken = true;
									break;
								}
								else
								{
									$resultFromParse['terms'][str_replace(array('(',')','<','>'), '', $partString)] = $uriParts[$order];
								}

								$uriHits++;
							}
						}
					}
				}

				if(!$broken && $uriHits == $uriPartsCount)
				{
					$resultFromParse['foundKey'] = $routeKey;
					$resultFromParse['finalRouteValues'] = $routeValues;
					$this->_currentRoute = $routeKey;

					break;
				}
				else
				{
					$resultFromParse['terms'] = array();
				}
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

				$resultFromParse['terms']['actionCalled'] = $resultFromParse['finalRouteValues'][self::ACTION];
				if(method_exists($classObj, 'before'))
				{
					call_user_func_array(
						array($classObj, 'before'),
						$resultFromParse['terms']
					);
				}

				array_shift($resultFromParse['terms']);
				array_pop($resultFromParse['terms']);

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
				if(isset($this->_routes[$identifier][self::CONSTRAINTS]['lang']))
				{
					$terms['lang'] = $this->_MantraCore->locale()->getCurrentLocale();
				}

				$mergedPattern = $this->_mergeTermsWithPattern($terms, $this->_routes[$identifier][self::PATTERN], $this->_routes[$identifier][self::CONSTRAINTS]);

				return $this->addTrailingSlash($siteUrl.'/'.$mergedPattern);
			}
			else
			{
				throw new PzPHP_Exception('Route not found.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE);
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
				throw new PzPHP_Exception('Route not found.', PzPHP_Helper_Codes::ROUTING_ERROR_NO_ROUTE);
			}
		}

		/**
		 * @param $partString
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
		 * @param $partString
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
		 * @param array $terms
		 * @param $pattern
		 * @param array $constraints
		 * @return string
		 * @throws PzPHP_Exception
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
										throw new PzPHP_Exception('Term constraint rule failed for "'.$term.'". Value was "'.$value.'".', PzPHP_Helper_Codes::ROUTING_ERROR_REGEX_MATCH_ERROR);
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
								throw new PzPHP_Exception('Could not fulfill required terms.', PzPHP_Helper_Codes::ROUTING_ERROR_MISSING_REQ_TERMS);
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
		 * @param $partString
		 * @param $term
		 * @return bool
		 */
		protected function _termMatchesPart($partString, $term)
		{
			return (strpos($partString, '<'.$term.'>') !== false || strpos($partString, '(<'.$term.'>)') !== false);
		}

		/**
		 * @param $constraints
		 * @param $term
		 * @param $value
		 * @return bool
		 */
		protected function _constraintCheck($constraints, $term, $value)
		{
			$term = str_replace(array('<','>','(',')'),'',$term);

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
