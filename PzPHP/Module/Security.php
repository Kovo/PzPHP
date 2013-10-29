<?php
class PzPHP_Module_Security extends PzPHP_Wrapper
{
	/**
	 * @param PzPHP_Core $PzPHPCore
	 */
	public function init(PzPHP_Core $PzPHPCore)
	{
		parent::init($PzPHPCore);

		PzPHP_Library_Security_Crypt::init();
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function twoWayEncrypt($value)
	{
		return PzPHP_Library_Security_Crypt::encrypt($value, array(PzPHP_Library_Security_Crypt::TWO_WAY));
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function twoWayDecrypt($value)
	{
		return PzPHP_Library_Security_Crypt::decrypt($value, array(PzPHP_Library_Security_Crypt::DE_POISON));
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function oneWayEncrypt($value)
	{
		return PzPHP_Library_Security_Crypt::encrypt($value, array(PzPHP_Library_Security_Crypt::ONE_WAY));
	}

	/**
	 * @param $unhashedValue
	 * @param $hashedComparisonValue
	 * @return bool
	 */
	public function oneWayHashComparison($unhashedValue, $hashedComparisonValue)
	{
		return PzPHP_Library_Security_Crypt::compareHashes($unhashedValue, $hashedComparisonValue, array(PzPHP_Library_Security_Crypt::ONE_WAY));
	}

	/**
	 * @param $unhashedValue
	 * @param $hashedComparisonValue
	 * @return bool
	 */
	public function twoWayHashComparison($unhashedValue, $hashedComparisonValue)
	{
		return PzPHP_Library_Security_Crypt::compareHashes($unhashedValue, $hashedComparisonValue, array(PzPHP_Library_Security_Crypt::TWO_WAY));
	}

	/**
	 * @throws PzPHP_Exception
	 */
	public function domainCheck()
	{
		$serverName = trim($this->_PzPHP->request()->server('SERVER_NAME'));

		//domain protection prevents certain rare exploits, where attackers may play with the HEADER information
		//this also helps redirect users when they type example.com instead of www.example.com
		if(!empty($serverName))
		{
			$allowedDomains = PzPHP_Config::get('SETTING_DOMAIN_ALLOWED_DOMAINS');

			if(is_array($allowedDomains) || strpos($allowedDomains, ',') !== false)
			{
				if(!is_array($allowedDomains))
				{
					$allowedDomains = array_map(array('PzPHP_Helper_String', 'trim'), explode(',', $allowedDomains));
				}

				if(!empty($allowedDomains))
				{
					$exists = false;

					foreach($allowedDomains as $domain)
					{
						if(strrpos($serverName, $domain) === true)
						{
							$exists = true;
							break;
						}
					}

					if($exists === false)
					{
						throw new PzPHP_Exception('Illegal domain detected!', PzPHP_Helper_Codes::SECURITY_ILLEGAL_DOMAIN_DETECTED);
					}
				}
			}
			elseif(strrpos($serverName, $allowedDomains) === false)
			{
				throw new PzPHP_Exception('Illegal domain detected!', PzPHP_Helper_Codes::SECURITY_ILLEGAL_DOMAIN_DETECTED);
			}
		}
	}
}
