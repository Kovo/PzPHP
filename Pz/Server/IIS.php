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
	 * @package Pz_Server_IIS
	 */
	class Pz_Server_IIS
	{
		/**
		 * @param $path
		 * @param $comment
		 * @param $server_ip
		 * @param $port
		 * @param $host_name
		 * @param $rights
		 * @param $start_server
		 *
		 * @return mixed
		 */
		public static function addServer($path, $comment, $server_ip, $port, $host_name, $rights, $start_server)
		{
			return iis_add_server($path, $comment, $server_ip, $port, $host_name, $rights, $start_server);
		}

		/**
		 * @param $id
		 *
		 * @return mixed
		 */
		public static function removeServer($id)
		{
			return iis_remove_server($id);
		}

		/**
		 * @param $id
		 *
		 * @return mixed
		 */
		public static function startServer($id)
		{
			return iis_start_server($id);
		}

		/**
		 * @param $id
		 *
		 * @return mixed
		 */
		public static function stopServer($id)
		{
			return iis_stop_server($id);
		}

		/**
		 * @param $id
		 *
		 * @return mixed
		 */
		public static function startService($id)
		{
			return iis_start_service($id);
		}

		/**
		 * @param $id
		 *
		 * @return mixed
		 */
		public static function stopService($id)
		{
			return iis_stop_service($id);
		}

		/**
		 * @param $serverId
		 * @param $vertualDir
		 *
		 * @return mixed
		 */
		public static function getDirectorySecurity($serverId, $vertualDir)
		{
			return iis_get_dir_security($serverId, $vertualDir);
		}

		/**
		 * @param $server_instance
		 * @param $virtual_path
		 * @param $directory_flags
		 *
		 * @return mixed
		 */
		public static function setDirectorySecurity($server_instance, $virtual_path, $directory_flags)
		{
			return iis_set_dir_security($server_instance, $virtual_path, $directory_flags);
		}

		/**
		 * @param $serverId
		 * @param $vertualDir
		 * @param $scriptExtension
		 *
		 * @return mixed
		 */
		public static function getScriptMap($serverId, $vertualDir, $scriptExtension)
		{
			return iis_get_script_map($serverId, $vertualDir, $scriptExtension);
		}

		/**
		 * @param $server_instance
		 * @param $virtual_path
		 * @param $script_extension
		 * @param $engine_path
		 * @param $allow_scripting
		 *
		 * @return mixed
		 */
		public static function setScriptMap($server_instance, $virtual_path, $script_extension, $engine_path, $allow_scripting)
		{
			return iis_set_script_map($server_instance, $virtual_path, $script_extension, $engine_path, $allow_scripting);
		}

		/**
		 * @param $comment
		 *
		 * @return mixed
		 */
		public static function getServerbyComment($comment)
		{
			return iis_get_server_by_comment($comment);
		}

		/**
		 * @param $path
		 *
		 * @return mixed
		 */
		public static function getServerbyPath($path)
		{
			return iis_get_server_by_path($path);
		}

		/**
		 * @param $server_instance
		 * @param $virtual_path
		 *
		 * @return mixed
		 */
		public static function getServerRights($server_instance, $virtual_path)
		{
			return iis_get_server_rights($server_instance, $virtual_path);
		}

		/**
		 * @param $server_instance
		 * @param $virtual_path
		 * @param $directory_flags
		 *
		 * @return mixed
		 */
		public static function setServerRights($server_instance, $virtual_path, $directory_flags)
		{
			return iis_set_server_rights($server_instance, $virtual_path, $directory_flags);
		}

		/**
		 * @param $serviceId
		 *
		 * @return mixed
		 */
		public static function serviceState($serviceId)
		{
			return iis_get_service_state($serviceId);
		}

		/**
		 * @param $server_instance
		 * @param $virtual_path
		 * @param $application_scope
		 *
		 * @return mixed
		 */
		public static function setAppSettings($server_instance, $virtual_path, $application_scope)
		{
			return iis_set_app_settings ($server_instance, $virtual_path, $application_scope);
		}
	}
