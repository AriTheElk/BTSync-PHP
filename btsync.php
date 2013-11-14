<?php

/** 
 * BTSync - PHP Wrapper for the BitTorrent Sync API
 * @author Garet McKinley <garetmckinley@me.com>
 */
class BTSync {

	/**
	 * IP address to access HTTP API
	 * @var string
	 */
	public $ip;

	/**
	 * Port to access HTTP API
	 * @var integer
	 */
	public $port;

	/**
	 * IP:Port for access HTTP API
	 * @var string
	 */
	private $baseURL;


	/**
	 * Initialize the class
	 *
	 * @param string $ip (Optional) IP address to access HTTP API (default is 127.0.0.1)
	 * @param string $port (Optional) Port to access HTTP API (default is 8888)
	 */
	public function __construct($ip = '127.0.0.1', $port = '8888')
	{
		$this->ip = $ip;
		$this->port = $port;
		$this->baseURL = sprintf('http://%s:%s/api', $this->ip, $this->port);
	}


	/**
	 * Makes an API call and returns the result
	 *
	 * @param string $params Parameters for the API call
	 *
	 * @return string API Result
	 */
	private function request($params) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, sprintf('%s?%s', $this->baseURL, $params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}


	/**
	 * Returns an array with folders info. If a secret is specified,
	 * will return info about the folder with this secret.
	 *
	 * @param string $secret (Optional) If a secret is specified, will return info about the folder with this secret
	 *
	 * @return array Result
	 */
	public function getFolders($secret = null)
	{
		if (!is_null($secret))
			$result = $this->request(sprintf('method=get_folders&secret=%s', $secret));
		else
			$result = $this->request('method=get_folders');
		$array = json_decode($result);
		if (empty($array))
			return false;
		return $array;
	}


	/**
	 * Adds a folder to Sync. If a secret is not specified, it will
	 * be generated automatically. The folder will have to pre-exist
	 * on the disk and Sync will add it into a list of syncing folders
	 *
	 * @param string $dir (Required) Path to the sync folder
	 * @param string $secret (Optional) Folder secret
	 * @param boolean $selective (Optional)
	 *
	 * @return object Result
	 */
	public function addFolder($dir, $secret = null, $selective = false)
	{
		if (!is_null($secret))
			$result = $this->request(sprintf('method=add_folder&dir=%s&secret=%s&selective_sync=%s', $dir, $secret, intval($selective)));
		else
			$result = $this->request(sprintf('method=add_folder&dir=%s&selective_sync=%s', $dir, intval($selective)));
		$array = json_decode($result);
		print_r(gettype($array));
		return $array;
	}


	/**
	 * Removes folder from Sync while leaving actual folder and files
	 * on disk. It will remove a folder from the Sync list of folders
	 * and does not touch any files or folders on disk.
	 *
	 * @param string $secret (Required) Folder secret
	 *
	 * @return object Result
	 */
	public function removeFolder($secret)
	{
		$result = $this->request(sprintf('method=remove_folder&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns list of files within the specified directory. If a
	 * directory is not specified, will return list of files and
	 * folders within the root folder.
	 *
	 * @param string $secret (Required) Folder secret
	 * @param string $path (Optional) Path to a subfolder inside of the sync folder
	 *
	 * @return array Result
	 */
	public function getFiles($secret, $path = null)
	{
		if (!is_null($path))
			$result = $this->request(sprintf('method=get_files&secret=%s&path=%s', $secret, $path));
		else
			$result = $this->request(sprintf('method=get_files&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Selects file for download for selective sync folders.
	 * Returns file information with applied preferences.
	 *
	 * @param string $secret (Required) Folder secret
	 * @param string $path (Required) Path to a subfolder inside of the sync folder
	 * @param boolean $download (Required) Specify if the file should be downloaded
	 *
	 * @return array Result
	 */
	public function setFilePrefs($secret, $path, $download)
	{
		$result = $this->request(sprintf('method=set_file_prefs&secret=%s&path=%s&download=%s', $secret, $path, intval($download)));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns list of peers connected to the specified folder.
	 *
	 * @param string $secret (Required) Folder secret
	 *
	 * @return array Result
	 */
	public function getFolderPeers($secret)
	{
		$result = $this->request(sprintf('method=get_folder_peers&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Generates read-write, read-only and encryption read-only secrets.
	 * If ‘secret’ parameter is specified, will return secrets available 
	 * for sharing under this secret.
	 *
	 * @param string $secret (Required) Folder secret
	 * @param string $secret (Optional) If type=encrypted, generate secret with support of encrypted peer
	 *
	 * @return object Secrets
	 */
	public function getSecrets($secret, $type = null)
	{
		if ($type == 'encrypted')
			$result = $this->request(sprintf('method=get_secrets&secret=%s&type=%s', $secret, $type));
		else
			$result = $this->request(sprintf('method=get_secrets&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns preferences for the specified sync folder.
	 *
	 * @param string $secret (Required) Folder secret
	 *
	 * @return object Returns current settings.
	 */
	public function getFolderPrefs($secret)
	{
		$result = $this->request(sprintf('method=get_folder_prefs&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns preferences for the specified sync folder.
	 *
	 * @param string $secret (Required) Folder secret
	 * @param array $params (Required) { use_dht, use_hosts, search_lan, use_relay_server, use_tracker, use_sync_trash }
	 *
	 * @return object Returns current settings.
	 */
	public function setFolderPrefs($secret, $params)
	{
		foreach ($params as $pref => $value) {
			$prefs .= sprintf('%s=%s', $pref, intval($value));
			if ($value !== end($params))
				$prefs .= '&';
		}
		$result = $this->request(sprintf('method=set_folder_prefs&secret=%s&%s', $secret, $prefs));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns list of predefined hosts for the folder,
	 * or error code if a secret is not specified.
	 *
	 * @param string $secret (Required) Folder secret
	 *
	 * @return object Hosts
	 */
	public function getFolderHosts($secret)
	{
		$result = $this->request(sprintf('method=get_folder_hosts&secret=%s', $secret));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Sets one or several predefined hosts for the specified
	 * sync folder. Existing list of hosts will be replaced.
	 * Hosts should be added as values of the ‘host’ parameter
	 * and separated by commas. Returns current hosts if set
	 * successfully, error code otherwise.
	 *
	 * @param string $secret (Required) Folder secret
	 * @param array $hosts (Required) List of hosts. Host should be represented as “[address]:[port]”
	 *
	 * @return object Hosts
	 */
	public function setFolderHosts($secret, $hosts)
	{
		foreach ($hosts as $index => $host) {
			$hostString .= $host;
			if ($host !== end($hosts))
				$hostString .= ',';
		}
		$result = $this->request(sprintf('method=set_folder_hosts&secret=%s&hosts=%s', $secret, $hostString));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Returns BitTorrent Sync preferences. Contains dictionary
	 * with advanced preferences. Please see Sync user guide for
	 * description of each option.
	 *
	 * @return object Current settings
	 */
	public function getPrefs()
	{
		$result = $this->request('method=get_prefs');
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Sets BitTorrent Sync preferences. Parameters are the same
	 * as in ‘Get preferences’. Advanced preferences are set as 
	 * general settings. Returns current settings.
	 *
	 * @param array (Required) { device_name, download_limit, lang, listening_port, upload_limit, use_upnp }
	 *
	 * @return object Current settings
	 */
	public function setPrefs($params)
	{
		foreach ($params as $pref => $value) {
			$prefs .= sprintf('%s=%s', $pref, intval($value));
			if ($value !== end($params))
				$prefs .= '&';
		}
		return $prefs;
		$result = $this->request(sprintf('method=set_prefs&%s', $prefs));
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Gets the OS name where BitTorrent Sync is running.
	 *
	 * @return object OS
	 */
	public function getOS()
	{
		$result = $this->request('method=get_os');
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Gets the BitTorrent Sync version.
	 *
	 * @return object Version
	 */
	public function getVersion()
	{
		$result = $this->request('method=get_version');
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Gets the current upload and download speed.
	 *
	 * @return object Version
	 */
	public function getSpeed()
	{
		$result = $this->request('method=get_speed');
		$array = json_decode($result);
		return $array;
	}


	/**
	 * Gracefully stops Sync.
	 *
	 * @return object Result
	 */
	public function shutdown()
	{
		$result = $this->request('method=shutdown');
		$array = json_decode($result);
		return $array;
	}



	/**
	 * Destroys the class
	 */
	function __destruct() {
	}
}

?>