<?php
/**
 *
 */
class PzPHP_Helper_IO
{
	/**
	 * @var int
	 */
	const ABSOLUTE = 1;

	/**
	 * @var int
	 */
	const RELATIVE = 2;

	/**
	 * @var int
	 */
	const URL = 3;

	/**
	 * @var int
	 */
	public static $defaultChmodLevelFiles = 0775;

	/**
	 * @var int
	 */
	public static $defaultChmodLevelFolders = 0775;

	/**
	 * @param $dir
	 * @return bool
	 */
	public static function isValidDir($dir)
	{
		return (is_dir($dir) && is_readable($dir) && is_executable($dir) && is_writable($dir));
	}

	/**
	 * @param $file
	 * @return bool
	 */
	public static function isValidFile($file)
	{
		return (file_exists($file) && is_readable($file) && is_writable($file));
	}

	/**
	 * @param $source
	 * @throws Exception
	 */
	public static function isValidSource($source)
	{
		if(is_dir($source))
		{
			$source = self::addTrailingSlash($source);

			chmod($source, self::$defaultChmodLevelFolders);

			if(self::isValidDir($source))
			{
				$scannedDir = scandir($source);

				if(!empty($scannedDir))
				{
					foreach($scannedDir as $fileName)
					{
						if($fileName !== '..' && $fileName !== '.')
						{
							self::isValidSource($source.$fileName);
						}
					}
				}
			}
			else
			{
				throw new Exception($source.' is not a fully accessible directory.', PzPHP_Helper_Codes::IO_DIR_ACCESS_ACTION_DENIED);
			}
		}
		else
		{
			chmod($source, self::$defaultChmodLevelFiles);

			if(!self::isValidFile($source))
			{
				throw new Exception($source.' is not a fully accessible file.', PzPHP_Helper_Codes::IO_FILE_ACCESS_ACTION_DENIED);
			}
		}
	}

	/**
	 * @param $dir
	 * @param null $permissions
	 * @param bool $recursive
	 * @return bool
	 */
	public static function createDir($dir, $permissions = NULL, $recursive = true)
	{
		if(!file_exists($dir))
		{
			return mkdir(
				$dir,
				($permissions===NULL?self::$defaultChmodLevelFolders:$permissions),
				$recursive
			);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $filename
	 * @return bool
	 */
	public static function removeFileFolder($filename)
	{
		if(file_exists($filename))
		{
			if(!is_dir($filename))
			{
				return unlink($filename);
			}
			else
			{
				return self::_recursiveRmdir($filename);
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param $filename
	 * @return bool|void
	 */
	public static function removeFileFolderEnforce($filename)
	{
		if(!self::removeFileFolder($filename))
		{
			chmod($filename, (!is_dir($filename)?self::$defaultChmodLevelFiles:self::$defaultChmodLevelFolders));

			return self::removeFileFolder($filename);
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param $sourcefoldername
	 * @param $targetfoldername
	 * @param $filename
	 * @return bool
	 */
	public static function moveFile($sourcefoldername, $targetfoldername, $filename)
	{
		if(self::recursiveCopy($sourcefoldername.$filename, $targetfoldername.$filename))
		{
			self::removeFileFolder($sourcefoldername.$filename);

			return true;
		}
		else
		{
			return false;
		}
	}

	public static function moveFileEnforce($sourcefoldername, $targetfoldername, $filename)
	{
		if(!self::moveFile($sourcefoldername, $targetfoldername, $filename))
		{
			chmod($sourcefoldername, self::$defaultChmodLevelFolders);
			chmod($sourcefoldername.$filename, self::$defaultChmodLevelFiles);
			chmod($targetfoldername, self::$defaultChmodLevelFolders);

			return self::moveFile($sourcefoldername, $targetfoldername, $filename);
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param $sourcefoldername
	 * @param $targetfoldername
	 * @param $filename
	 * @return bool
	 */
	public static function copyFile($sourcefoldername, $targetfoldername, $filename)
	{
		return self::recursiveCopy($sourcefoldername.$filename, $targetfoldername.$filename);
	}

	/**
	 * @param $filename
	 * @param $foldername
	 * @param $newfilename
	 * @return bool
	 */
	public static function renameFile($filename, $foldername, $newfilename)
	{
		return rename($foldername.$filename, $foldername.$newfilename);
	}

	/**
	 * @param $dir
	 * @return bool
	 */
	private static function _recursiveRmdir($dir)
	{
		if(is_dir($dir))
		{
			$files = scandir($dir);
			foreach ($files as $file)
			{
				if($file != "." && $file != "..")
				{
					self::_recursiveRmdir("$dir/$file");
				}
			}

			return rmdir($dir);
		}
		elseif(file_exists($dir))
		{
			return unlink($dir);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $src
	 * @param $dst
	 * @return bool
	 */
	public static function recursiveCopy($src, $dst)
	{
		if(is_dir($src))
		{
			if(!is_dir($dst))
			{
				mkdir($dst, self::$defaultChmodLevelFolders, true);
			}

			$files = scandir($src);
			foreach($files as $file)
			{
				if($file != "." && $file != "..")
				{
					self::recursiveCopy("$src/$file", "$dst/$file");
				}
			}

			return true;
		}
		elseif(file_exists($src))
		{
			return copy($src, $dst);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $path
	 * @return int
	 */
	public static function pathType($path)
	{
		if(substr($path,0,7)==='http://' || substr($path,0,8)==='https://' || substr($path,0,6)==='ftp://')
		{
			return self::URL;
		}
		else
		{
			return self::RELATIVE;
		}
	}

	/**
	 * @param $path
	 * @return string
	 */
	public static function addTrailingSlash($path)
	{
		if(substr($path, -1) !== DIRECTORY_SEPARATOR)
		{
			return $path.DIRECTORY_SEPARATOR;
		}

		return $path;
	}

	/**
	 * @param $fullPath
	 * @return string
	 */
	public static function extractPath($fullPath)
	{
		if(!is_dir($fullPath))
		{
			$explode = explode(DIRECTORY_SEPARATOR, $fullPath);
			array_pop($explode);

			return implode(DIRECTORY_SEPARATOR, $explode).DIRECTORY_SEPARATOR;
		}
		else
		{
			return self::addTrailingSlash($fullPath);
		}
	}

	/**
	 * @param $fullPath
	 * @return string
	 */
	public static function extractFilename($fullPath)
	{
		$explode = explode(DIRECTORY_SEPARATOR, $fullPath);

		return array_pop($explode);
	}
}
