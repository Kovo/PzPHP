<?php
	/**
	 * Contributions by:
	 *      Fayez Awad
	 *      Yann Madeleine (http://www.yann-madeleine.com)
	 *
	 * Licensed under The MIT License
	 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
	 *
	 * @author Kevork Aghazarian (http://www.kevorkaghazarian.com)
	 * @package Pz Library
	 */
	/**
	 * A collection of methods allowing you to interact directly with IIS.
	 */
	class Pz_Server_IIS
	{
		/**
		 * @static
		 * @access public
		 * @param string $path
		 * @param string $comment
		 * @param string $server_ip
		 * @param int $port
		 * @param string $host_name
		 * @param string $rights
		 * @param bool $start_server
		 * @return mixed
		 */
		public static function addServer($path, $comment, $server_ip, $port, $host_name, $rights, $start_server)
		{
			return iis_add_server($path, $comment, $server_ip, $port, $host_name, $rights, $start_server);
		}

		/**
		 * @static
		 * @access public
		 * @param int $id
		 * @return mixed
		 */
		public static function removeServer($id)
		{
			return iis_remove_server($id);
		}

		/**
		 * @static
		 * @access public
		 * @param int $id
		 * @return mixed
		 */
		public static function startServer($id)
		{
			return iis_start_server($id);
		}

		/**
		 * @static
		 * @access public
		 * @param int $id
		 * @return mixed
		 */
		public static function stopServer($id)
		{
			return iis_stop_server($id);
		}

		/**
		 * @static
		 * @access public
		 * @param int $id
		 * @return mixed
		 */
		public static function startService($id)
		{
			return iis_start_service($id);
		}

		/**
		 * @static
		 * @access public
		 * @param int $id
		 * @return mixed
		 */
		public static function stopService($id)
		{
			return iis_stop_service($id);
		}

		/**
		 * @static
		 * @access public
		 * @param int $serverId
		 * @param string $vertualDir
		 * @return mixed
		 */
		public static function getDirectorySecurity($serverId, $vertualDir)
		{
			return iis_get_dir_security($serverId, $vertualDir);
		}

		/**
		 * @static
		 * @access public
		 * @param int $server_instance
		 * @param string $virtual_path
		 * @param mixed $directory_flags
		 * @return mixed
		 */
		public static function setDirectorySecurity($server_instance, $virtual_path, $directory_flags)
		{
			return iis_set_dir_security($server_instance, $virtual_path, $directory_flags);
		}

		/**
		 * @static
		 * @access public
		 * @param int $serverId
		 * @param string $vertualDir
		 * @param string $scriptExtension
		 * @return mixed
		 */
		public static function getScriptMap($serverId, $vertualDir, $scriptExtension)
		{
			return iis_get_script_map($serverId, $vertualDir, $scriptExtension);
		}

		/**
		 * @static
		 * @access public
		 * @param int $server_instance
		 * @param string $virtual_path
		 * @param string $script_extension
		 * @param string $engine_path
		 * @param bool $allow_scripting
		 * @return mixed
		 */
		public static function setScriptMap($server_instance, $virtual_path, $script_extension, $engine_path, $allow_scripting)
		{
			return iis_set_script_map($server_instance, $virtual_path, $script_extension, $engine_path, $allow_scripting);
		}

		/**
		 * @static
		 * @access public
		 * @param string $comment
		 * @return mixed
		 */
		public static function getServerbyComment($comment)
		{
			return iis_get_server_by_comment($comment);
		}

		/**
		 * @static
		 * @access public
		 * @param string $path
		 * @return mixed
		 */
		public static function getServerbyPath($path)
		{
			return iis_get_server_by_path($path);
		}

		/**
		 * @static
		 * @access public
		 * @param int $server_instance
		 * @param string $virtual_path
		 * @return mixed
		 */
		public static function getServerRights($server_instance, $virtual_path)
		{
			return iis_get_server_rights($server_instance, $virtual_path);
		}

		/**
		 * @static
		 * @access public
		 * @param int $server_instance
		 * @param string $virtual_path
		 * @param string $directory_flags
		 * @return mixed
		 */
		public static function setServerRights($server_instance, $virtual_path, $directory_flags)
		{
			return iis_set_server_rights($server_instance, $virtual_path, $directory_flags);
		}

		/**
		 * @static
		 * @access public
		 * @param int $serviceId
		 * @return mixed
		 */
		public static function serviceState($serviceId)
		{
			return iis_get_service_state($serviceId);
		}

		/**
		 * @static
		 * @access public
		 * @param int $server_instance
		 * @param string $virtual_path
		 * @param string $application_scope
		 * @return mixed
		 */
		public static function setAppSettings($server_instance, $virtual_path, $application_scope)
		{
			return iis_set_app_settings($server_instance, $virtual_path, $application_scope);
		}
	}
