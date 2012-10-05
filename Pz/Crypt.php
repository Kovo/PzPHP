<?php
	/**
	 * Contributions by:
	 *     Fayez Awad
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package PzCrypt
	 */
	class PzCrypt
	{
		const ALPHANUMERIC = 0;
		const ALPHANUMERIC_PLUS = 1;
		const HEX = 2;
		const TWO_WAY = 3;
		const ONE_WAY = 4;
		const STRICT = 5;
		const HASH = 6;
		const SALT = 7;
		const POISON_CONSTRAINTS = 8;
		const UNIQUE_SALT = 9;
		const DE_POISON = 10;

		/**
		 * @var string
		 *
		 * This passphrase is required when generating or verifying environment info
		 * That way, it wont be possible for somoene to call the public function from another program, without direct access to the source of this class
		 */
		private $_passPhrase = 'seeYouInShell';

		/**
		 * @var int
		 *
		 *  How many times should the default/custom salt be hashed by the default hashing algoritihim
		 */
		private $_kSaltDepth = 1024;

		/**
		 * @var string
		 *
		 *  Must exist in hash_algos() array
		 */
		private $_kHash = 'md5';

		/**
		 * @var string
		 *
		 *  It is recommended you change this salt as to distinguish yourself from other PzCrypt installations/hashes
		 */
		private $_kSalt = 'B^M#@^|>2x =<7r)t%M%y@X]8mK3b+9:e86.*6;|diL#&^|o$Ovu#K*Y>q!a<.r]_d#';

		/**
		 * @var array
		 *
		 *  Every sub-array has two elements:
		 *      the first element dictates at which point in the string will the poisoning begin
		 *      the second element dictates how long the poison will be (all in characters)
		 *
		 * it is good to provide constraints for long strings as well, even if you know you wont be feeding PzCrypt long strings
		 */
		private $_kPoisonConstraints = array(
			array(1,2),
			array(10,2),
			array(15,2),
			array(25,2),
			array(35,1),
			array(50,1),
			array(70,1),
			array(90,2),
			array(150,1),
			array(300,2),
			array(600,1),
			array(1000,2),
			array(10000,3),
			array(100000,1),
			array(1000000,3)
		);

		/**
		 * @var array
		 *
		 * Values covered to date: [empty space], !, ", #, $, %, &, ', (, ), *, +, [a comma], -, ., /, :, ;, <, =, >, ?, @, A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z, [, \, ], ^, _, `, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, {, |, }, ~, ¡, ¢, £, ¤, ¥, ¦, §, ¨, ©, ª, «, ¬, ®, ¯, °, ±, ², ³, ´, µ, ¶, ·, ¸, ¹, º, », ¼, ½, ¾, ¿, À, Á, Â, Ã, Ä, Å, Æ, Ç, È, É, Ê, Ë, Ì, Í, Î, Ï, Ð, Ñ, Ò, Ó, Ô, Õ, Ö, ×, Ø, Ù, Ú, Û, Ü, Ý, Þ, ß, à, á, â, ã, ä, è, é, ê, ë, ì, 0, å, æ, ç, í, î, ï, ð, ñ, ò, ó, ô, õ, ö, ÷, ø, ù, ú, û, ü, ý, þ, ÿ, ı, Œ, œ, Š, š, Ÿ, ƒ, ˆ, ˇ, ˘, ˙, ˚, ˛, ˜, ˝, Δ, Σ, Ω, –, —, ‘, ’, ‚, “, ”, „, †, ‡, •, …, ‰, ‹, ›, ⁄, ℗, ℠, ™, ∂, √, ∞, ∫, ≅, ≠, ≤, ≥, ⌘, ⌥, ☮, ☯, 1, 2, 3, 4, 5, 6, 7, 8, 9
		 *
		 * PzCrypt two way encryption currently supports all valid html entitity characters. If a character is not supported by PzCrypt, it will not be encrypted  (kcrpyt still works with characters it does not support, but inevitably produces weaker encryptions)
		 */
		private $_hashTableFrom = array(
			0 => 'q',1 => 'e',2 => 'u',3 => 't',4 => 'd',5 => 'w',6 => 'n',7 => 'v',8 => 'r',9 => 'h',10 => 'o',11 => 'm',12 => 'j',13 => 'l',14 => 'i',15 => 's',16 => 'y',17 => 'b',18 => 'z',19 => 'x',20 => 'f',21 => 'p',22 => 'k',23 => 'c',24 => 'a',25 => 'g',26 => 'Q',27 => 'C',28 => 'Z',29 => 'H',30 => 'P',31 => 'B',32 => 'X',33 => 'N',34 => 'W',35 => 'V',36 => 'E',37 => 'O',38 => 'J',39 => 'Y',40 => 'A',41 => 'R',42 => 'I',43 => 'S',44 => 'K',45 => 'F',46 => 'T',47 => 'U',48 => 'D',49 => 'L',50 => 'G',51 => 'M',52 => '2',53 => '6',54 => '5',55 => '0',56 => '9',57 => '1',58 => '8',59 => '3',60 => '7',61 => '4',62 => '`',63 => '!',64 => '@',65 => '#',66 => '$',67 => '%',68 => '^',69 => '&',70 => '*',71 => '(',72 => ')',73 => '-',74 => '_',75 => '=',76 => '+',77 => '[',78 => '{',79 => ']',80 => '}',81 => ';',82 => ':',83 => '\'',84 => '"',85 => '<',86 => '>',87 => ',',88 => '.',89 => '/',90 => '?',91 => '~',92 => '|',93 => '\\',94 => 'À',95 => 'à',96 => 'Á',97 => 'á',98 => 'Â',99 => 'â',100 => 'Ã',101 => 'ã',102 => 'Ä',103 => 'ä',104 => 'Å',105 => 'å',106 => 'Æ',107 => 'æ',108 => 'Ç',109 => 'ç',110 => 'È',111 => 'è',112 => 'É',113 => 'é',114 => 'Ê',115 => 'ê',116 => 'Ë',117 => 'ë',118 => 'Ì',119 => 'ì',120 => 'Í',121 => 'í',122 => 'Î',123 => 'î',124 => 'Ï',125 => 'ï',126 => 'µ',127 => 'Ñ',128 => 'ñ',129 => 'Ò',130 => 'ò',131 => 'Ó',132 => 'ó',133 => 'Ô',134 => 'ô',135 => 'Õ',136 => 'õ',137 => 'Ö',138 => 'ö',139 => 'Ø',140 => 'ø',141 => 'ß',142 => 'Ù',143 => 'ù',144 => 'Ú',145 => 'ú',146 => 'Û',147 => 'û',148 => 'Ü',149 => 'ü',150 => 'ÿ',151 => '¨',152 => '¯',153 => '´',154 => '¸',155 => '¡',156 => '¿',157 => '·',158 => '«',159 => '»',160 => '¶',161 => '§',162 => '©',163 => '®',164 => '÷',165 => 'ª',166 => 'º',167 => '¬',168 => '°',169 => '±',170 => '¤',171 => '¢',172 => '£',173 => '¥',174 => ' ',175 => 'Ð',176 => 'ð',177 => 'Þ',178 => 'þ',179 => 'Ý',180 => 'ý',181 => '¦',182 => '¹',183 => '²',184 => '³',185 => '×',186 => '¼',187 => '½',188 => '¾',189 => 'Δ',190 => 'ƒ',191 => 'Ω',192 => 'Œ',193 => 'œ',194 => 'Š',195 => 'š',196 => 'Ÿ',197 => 'ı',198 => 'ˆ',199 => 'ˇ',200 => '˘',201 => '˚',202 => '˙',203 => '˛',204 => '˝',205 => '˜',206 => '–',207 => '—',208 => '†',209 => '‡',210 => '•',211 => '…',212 => '‘',213 => '’',214 => '‚',215 => '“',216 => '”',217 => '„',218 => '‹',219 => '›',220 => '™',221 => '℠',222 => '℗',223 => '√',224 => '∞',225 => '∫',226 => '∂',227 => '≅',228 => '≠',229 => '≤',230 => '≥',231 => 'Σ',232 => '‰',233 => '⁄',234 => '⌘',235 => '⌥',236 => '☮',237 => '☯'
		);

		/**
		 * @var array
		 */
		private $_hashTableTo = array(
			0 => '1', 1 => '2',2 => '3',3 => '4',4 => '5',5 => '6',6 => '7',7 => '8',8 => '9',9 => '0',10 => 'a',11 => 'b',12 => 'c',13 => 'd',14 => 'e',15 => 'f',16 => 'g',17 => 'h',18 => 'i',19 => 'j',20 => 'k',21 => 'l',22 => 'm',23 => 'n',24 => 'o',25 => 'p',26 => 'q',27 => 'r',28 => 's',29 => 't',30 => 'u',31 => 'v',32 => 'w',33 => 'x',34 => 'y',35 => 'z',36 => 'A',37 => 'B',38 => 'C',39 => 'D',40 => 'E',41 => 'F',42 => 'G',43 => 'H',44 => 'I',45 => 'J',46 => 'K',47 => 'L',48 => 'M',49 => 'N',50 => 'O',51 => 'P',52 => 'Q',53 => 'R',54 => 'S',55 => 'T',56 => 'U',57 => 'V',58 => 'W',59 => 'X',60 => 'Y',61 => 'Z',62 => '\'',63 => '`',64 => '~',65 => '!',66 => '@',67 => '#',68 => '$',69 => '%',70 => '^',71 => '&',72 => '*',73 => '(',74 => ')',75 => '-',76 => '_',77 => '+',78 => '=',79 => '|',80 => '\\',81 => '[',82 => ']',83 => '}',84 => '{',85 => ';',86 => ':',87 => '"',88 => ',',89 => '<',90 => '>',91 => '.',92 => '?',93 => '/',94 => 'Ë',95 => 'ê',96 => 'Ê',97 => 'é',98 => 'É',99 => 'è',100 => 'È',101 => 'ç',102 => 'å',103 => 'æ',104 => 'Æ',105 => 'Ç',106 => 'Å',107 => 'ä',108 => 'Ä',109 => 'ã',110 => 'Ã',111 => 'â',112 => 'Â',113 => 'á',114 => 'Á',115 => 'à',116 => 'À',117 => 'í',118 => 'Î',119 => 'î',120 => 'Ï',121 => 'ï',122 => 'Í',123 => 'ì',124 => 'Ì',125 => 'ë',126 => 'ÿ',127 => 'ü',128 => 'Ü',129 => 'û',130 => 'Û',131 => 'ú',132 => 'Ú',133 => 'ù',134 => 'Ù',135 => 'ß',136 => 'ø',137 => 'Ø',138 => 'µ',139 => 'Ñ',140 => 'ñ',141 => 'Ò',142 => 'ò',143 => 'Ó',144 => 'ó',145 => 'Ô',146 => 'ô',147 => 'Õ',148 => 'õ',149 => 'Ö',150 => 'ö',151 => '£',152 => '¢',153 => '¤',154 => '±',155 => '°',156 => '¬',157 => 'º',158 => 'ª',159 => '÷',160 => '®',161 => '©',162 => '§',163 => '¶',164 => '»',165 => '«',166 => '·',167 => '¿',168 => '¡',169 => '¸',170 => '´',171 => '¯',172 => '¨',173 => 'Ÿ',174 => 'š',175 => 'Š',176 => 'œ',177 => 'Œ',178 => 'Ω',179 => 'ƒ',180 => 'Δ',181 => '¾',182 => '½',183 => '¼',184 => '×',185 => '³',186 => '¹',187 => '¹',188 => '¦',189 => 'ý',190 => 'Ý',191 => 'þ',192 => 'Þ',193 => 'ð',194 => 'Ð',195 => ' ',196 => '¥',197 => '•',198 => '‚',199 => '℗',200 => '℠',201 => '™',202 => '›',203 => '‹',204 => '„',205 => '”',206 => '“',207 => '’',208 => '‘',209 => '…',210 => '˘',211 => '‡',212 => '†',213 => '—',214 => '–',215 => '˜',216 => '˝',217 => '˛',218 => '˙',219 => '˚',220 => 'ˇ',221 => 'ˆ',222 => 'ı',223 => '☯',224 => '☮',225 => '⌥',226 => '⌘',227 => '⁄',228 => '‰',229 => 'Σ',230 => '≅',231 => '≤',232 => '≠',233 => '≥',234 => '∂',235 => '∫',236 => '∞',237 => '√'
		);

		/**
		 * @param $input
		 * @param array $flags
		 *
		 *          TWO_WAY     = default flag sent that tells PzCrypt to produce a two-way encryped string. PzCrypt uses its own hash and algorithim to produce the ecnrypted string.
		 *          ONE_WAY     = flag that tells PzCrypt to produce a one-way encrypted string. PzCrypt uses a special algorithim to encrypt strings
		 *          STRICT      = hashes produced in ONE_WAY encryption will honor the $kHash property's length and character type. eg. md5 generates 32 hex char hashes, so PzCrypt will honor that
		 *
		 *
		 *
		 * @param array $customRules
		 *
		 *          HASH                = overwrite the default hash algorithim set by $kHash. Make the the hash is supported by your system/php build
		 *
		 *          SALT                = a custom salt you want to use for one-way encryption
		 *                              = if you want to use a custom salt, then include this rule, if you want to use PzCrypts default salt, then do not include this rule
		 *          POISON_CONSTRAINTS   = a custom set of poisoning contraints for your one-way or two-way encryption.
		 *                                If you dont want any poisoning, then provide an empty array. If you want to use PzCrypts default poison constraints, then dont include this rule
		 *
		 *          UNIQUE_SALT         = A unique salt that you want to apply to this password. Unique salts make password has reversal much harder, since crackers must figure out which unique salt was used for each password hash.
		 *
		 *
		 * @return string
		 */
		public function encrypt($input, $flags = array(self::TWO_WAY), $customRules = array())
		{
			$flags = (array)$flags;
			//string that will be returned
			$finalOutput = '';

			//used for poisoning
			$inputLength = strlen($input);

			//main flags default states
			$ONE_WAY = false;
			$TWO_WAY = false;
			$STRICT = false;

			//the following statements modify the above default flag states (if necessary)
			if(in_array(self::TWO_WAY, $flags) === true)
			{
				$TWO_WAY = true;
			}
			else
			{
				$ONE_WAY = true;
			}

			if(in_array(self::STRICT, $flags) === true)
			{
				$STRICT = true;
			}

			//we are going to produce a oneway, super strong encryption (virtually irreversable)
			if($ONE_WAY === true)
			{
				$saltOne = hash((isset($customRules[self::HASH])&&$customRules[self::HASH]!==''?$customRules[self::HASH]:$this->_kHash), $input.(isset($customRules[self::SALT])&&$customRules[self::SALT]!==''?$customRules[self::SALT]:$this->_kSalt).(isset($customRules[self::UNIQUE_SALT])&&$customRules[self::UNIQUE_SALT]!==''?$customRules[self::UNIQUE_SALT]:''));
				for($x=0;$x<$this->_kSaltDepth;$x++)
				{
					$saltOne = hash((isset($customRules[self::HASH])&&$customRules[self::HASH]!==''?$customRules[self::HASH]:$this->_kHash), $saltOne);
				}

				//get list of supported hashing algorithims
				$supportedHashes = hash_algos();

				//first encrypt with salt first
				$finalOutput = (isset($supportedHashes['whirlpool'])?
					hash('whirlpool',$saltOne.$input):
					(isset($supportedHashes['sha512'])?
						hash('sha512',$saltOne.$input):
						(isset($supportedHashes['ripemd320'])?
							hash('ripemd320',$saltOne.$input):hash('md5', $saltOne.$input)
						)
					)
				);
				//then encrypt with salt last
				$finalOutput = (isset($supportedHashes['whirlpool'])?
					hash('whirlpool',$input.$saltOne):
					(isset($supportedHashes['sha512'])?
						hash('sha512',$input.$saltOne):
						(isset($supportedHashes['ripemd320'])?
							hash('ripemd320',$input.$saltOne):hash('md5', $input.$saltOne)
						)
					)
				);

				//begin poisoning
				if(!isset($customRules[self::POISON_CONSTRAINTS]) && count($this->_kPoisonConstraints) > 0)
				{
					$finalOutput = $this->_poisonString($finalOutput, $this->_kPoisonConstraints);
				}
				elseif(isset($customRules[self::POISON_CONSTRAINTS]) && count($customRules[self::POISON_CONSTRAINTS]) > 0)
				{
					$finalOutput = $this->_poisonString($finalOutput, $customRules[self::POISON_CONSTRAINTS]);
				}

				if($STRICT === true && $ONE_WAY === true)
				{
					if(isset($customRules[self::HASH]) && $customRules[self::HASH] !== '')
					{
						$finalOutput = substr($finalOutput, 0, strlen(hash($customRules[self::HASH], $input)));
					}
					else
					{
						$finalOutput = substr($finalOutput, 0, strlen(hash($this->_kHash, $input)));
					}
				}
			}
			//we are going to produce a two-way encrypted string (which will be extremely hard to crack without source code access)
			elseif($TWO_WAY === true)
			{
				$hashedCharacters = array();
				for($i=0;$i<$inputLength;$i++)
				{
					$thisCharacterArrayKey = array_search(substr($input, $i, 1), $this->_hashTableFrom);
					$hashedCharacters[] = $this->_hashTableTo[$thisCharacterArrayKey];
				}

				$finalOutput = implode('', $hashedCharacters);

				//begin poisoning
				if(!isset($customRules[self::POISON_CONSTRAINTS]) && count($this->_kPoisonConstraints) > 0)
				{
					$finalOutput = $this->_poisonString($finalOutput, $this->_kPoisonConstraints, 'alphan');
				}
				elseif(isset($customRules[self::POISON_CONSTRAINTS]) && count($customRules[self::POISON_CONSTRAINTS]) > 0)
				{
					$finalOutput = $this->_poisonString($finalOutput, $customRules[self::POISON_CONSTRAINTS], 'alphan');
				}
			}

			return $finalOutput;
		}

		/**
		 * @param $input
		 * @param array $flags
		 *
		 *          DE_POISON             = de-poison the string first
		 *
		 * @param array $customRules
		 *
		 *          POISON_CONSTRAINTS    = use this only if you overwrote the defualt poison constraints during encryption
		 *
		 * @return string
		 */
		public function decrypt($input, $flags = array(self::DE_POISON), $customRules = array())
		{
			$flags = (array)$flags;

			$DE_POISON = false;

			if(in_array(self::DE_POISON, $flags) === true)
			{
				$DE_POISON = true;
			}

			if($DE_POISON === true)
			{
				$input = $this->_depoisonString($input, (isset($customRules[self::POISON_CONSTRAINTS])?$customRules[self::POISON_CONSTRAINTS]:$this->_kPoisonConstraints));
			}

			$inputLength = strlen($input);

			$unhashedCharacters = array();
			for($i=0;$i<$inputLength;$i++)
			{
				$thisCharacterArrayKey = array_search(mb_substr($input, $i, 1, 'UTF-8'), $this->_hashTableTo);

				if($thisCharacterArrayKey !== false)
				{
					$unhashedCharacters[] = $this->_hashTableFrom[$thisCharacterArrayKey];
				}
			}

			$input = implode('', $unhashedCharacters);

			return $input;
		}

		/**
		 * @param $inputString
		 * @param $comparisonHash
		 * @param array $flags
		 * @param array $customRules
		 * @return bool
		 */
		public function compareHashes($inputString, $comparisonHash, $flags = array(self::TWO_WAY), $customRules = array())
		{
			//encrypt string first
			$hashedInputString = $this->encrypt($inputString, $flags, $customRules);
			//depoison it
			$hashedInputString = $this->_depoisonString($hashedInputString, (isset($customRules[self::POISON_CONSTRAINTS])?$customRules[self::POISON_CONSTRAINTS]:$this->_kPoisonConstraints));
			//depoison the comparison hash
			$comparisonHash = $this->_depoisonString($comparisonHash, (isset($customRules[self::POISON_CONSTRAINTS])?$customRules[self::POISON_CONSTRAINTS]:$this->_kPoisonConstraints));

			if($hashedInputString === $comparisonHash)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * @param     $input
		 * @param     $constraints
		 * @param int $type
		 *
		 * @return string
		 */
		private function _poisonString($input, $constraints, $type = self::HEX)
		{
			$startChar = 0;
			foreach($constraints as $coords)
			{
				if($coords[0]+$coords[1] < strlen($input))
				{
					$splitLeft = substr($input, 0, $coords[0]+$startChar);
					$splitRight = substr($input, $coords[0]+$startChar);

					$input = $splitLeft.$this->createCode($coords[1], $type).$splitRight;

					$startChar += $coords[1];
				}
			}

			return $input;
		}

		/**
		 * @param $input
		 * @param $constraints
		 *
		 * @return string
		 */
		private function _depoisonString($input, $constraints)
		{
			foreach($constraints as $coords)
			{
				if($coords[0] <= strlen($input))
				{
					if($coords[0]+$coords[1] < strlen($input))
					{
						$splitLeft = substr($input, 0, $coords[0]);
						$splitRight = substr($input, $coords[0]+$coords[1]);
					}
					else
					{
						$splitLeft = substr($input, 0, $coords[0]-$coords[1]);
						$splitRight = '';
					}

					$input = $splitLeft.$splitRight;
				}
			}

			return $input;
		}

		/**
		 * @param     $length
		 * @param int $type
		 *
		 * @return string
		 */
		public function createCode($length, $type = self::ALPHANUMERIC)
		{
			if($type === self::ALPHANUMERIC)
			{
				$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
			}
			elseif($type === self::ALPHANUMERIC_PLUS)
			{
				$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz`~!@#$%^&*()_+|}{:?><,./;'[]-=";
			}
			else
			{
				$chars = "0123456789abcdef";
			}

			$amountChars = strlen($chars);

			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * mt_rand(9,999999));

			mt_srand($seed+mt_rand(1,1000));

			$pass = '';

			for($i=0;$i<$length;$i++)
			{
				$num = mt_rand()%$amountChars;
				$tmp = substr($chars, $num, 1);
				$pass = $pass.$tmp;
			}

			return $pass;
		}

		/**
		 * @param $passPhrase
		 * @return string
		 *
		 * Will return a string that will include various info about the current setup of this class and relevant php/system settings
		 * You should use this once you go production, and store the string somewhere safe, in the event that you must switch servers, make sure to run that string through
		 * the verifyChecksum() method, as to make sure the new system will support all of the encrypted strings you have generated on the older system
		 *
		 * NOTE: This verification method cannot take into account custom rules you may have passed in to the encrypt method, so do not soley rely on this method to verify compatibility
		 * NOTE: This verification method will not take into account your passphrase
		 */
		public function getChecksum($passPhrase)
		{
			if($passPhrase === $this->_passPhrase)
			{
				$strungString = md5($this->_kSaltDepth);
				$strungString .= md5($this->_kHash);
				$strungString .= md5($this->_kSalt);
				$strungString .= md5(var_export($this->_kPoisonConstraints,true));
				$strungString .= md5(var_export($this->_hashTableFrom,true));
				$strungString .= md5(var_export($this->_hashTableTo,true));

				$supportedHashes = hash_algos();

				$strungString .=  md5((isset($supportedHashes['whirlpool'])?
					md5('whirlpool'):
					(isset($supportedHashes['sha512'])?
						md5('sha512'):
						(isset($supportedHashes['ripemd320'])?
							md5('ripemd320'):md5('md5')
						)
					)
				));

				return md5($strungString);
			}

			return 'Incorrect pass phrase supplied.';
		}

		/**
		 * @param $checksumString
		 * @param $passPhrase
		 * @return string
		 */
		public function verifyChecksum($checksumString, $passPhrase)
		{
			if($passPhrase === $this->_passPhrase)
			{
				if($this->getChecksum($passPhrase) === $checksumString)
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			return 'Incorrect passphrase supplied.';
		}

		/**
		 * @param $passPhrase
		 * @return mixed
		 *
		 * Will dump a new array to replace the defualt $hashTableFrom array
		 */
		public function regeneratePzCryptHash($passPhrase)
		{
			if($passPhrase === $this->_passPhrase)
			{
				$hashTableFrom = $this->_hashTableFrom;

				shuffle($hashTableFrom);

				$hashTableFrom = array_values($hashTableFrom);

				return $hashTableFrom;
			}

			return 'Incorrect passphrase supplied.';
		}

		/**
		 * @param array $newFromTableHashArray
		 */
		public function replacePzCryptHash(array $newFromTableHashArray)
		{
			$this->_hashTableFrom = $newFromTableHashArray;
		}

		/**
		 * @param $passPhrase
		 * @return string
		 */
		public function regeneratePzCryptSalt($passPhrase)
		{
			if($passPhrase === $this->_passPhrase)
			{
				return $this->createCode(mt_rand(35,45), self::ALPHANUMERIC_PLUS);
			}

			return 'Incorrect passphrase supplied.';
		}

		/**
		 * @param $passPhrase
		 */
		public function replacePzCryptSalt($passPhrase)
		{
			$this->_kSalt = $passPhrase;
		}

		/**
		 * @param $passPhrase
		 * @return mixed|string
		 */
		public function regeneratePzCryptPoisonConstraints($passPhrase)
		{
			if($passPhrase === $this->_passPhrase)
			{
				$newConstraints = array(
					array(mt_rand(0,5),mt_rand(1,2)),
					array(mt_rand(8,12),mt_rand(1,2)),
					array(mt_rand(13,20),mt_rand(1,2)),
					array(mt_rand(22,34),mt_rand(1,2)),
					array(mt_rand(35,48),mt_rand(1,2)),
					array(mt_rand(49,65),mt_rand(1,2)),
					array(mt_rand(68,80),mt_rand(1,2)),
					array(mt_rand(85,124),mt_rand(1,2)),
					array(mt_rand(135,287),mt_rand(1,2)),
					array(mt_rand(289,555),mt_rand(1,2)),
					array(mt_rand(580,987),mt_rand(1,2)),
					array(mt_rand(999,8754),mt_rand(1,2)),
					array(mt_rand(9000,89547),mt_rand(1,3)),
					array(mt_rand(99853,985412),mt_rand(1,2)),
					array(mt_rand(998541,1245551),mt_rand(1,3))
				);

				return $newConstraints;
			}

			return 'Incorrect passphrase supplied.';
		}

		/**
		 * @param array $newConstraints
		 */
		public function replacePzCryptPoisonConstraints(array $newConstraints)
		{
			$this->_kPoisonConstraints = $newConstraints;
		}

		/**
		 * @param $newDepth
		 */
		public function replacePzCryptRehashDepth($newDepth)
		{
			$this->_kSaltDepth = (int)$newDepth;
		}
	}
