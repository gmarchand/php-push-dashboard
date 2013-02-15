<?php
require_once 'lib/facebook-php-sdk/src/facebook.php';

class facebook {
	
	private $settings;
	private $facebook = null;
	
	function __construct() {
		$this->settings = parse_ini_file('config.ini', false);
	
	}
	
	function initCplusApp(){
		$config = array();
		$config[‘appId’] = $this->settings['FB_CPLUS_APPID'];
		$config[‘secret’] = $this->settings['FB_CPLUS_SECRET'];
		$config[‘fileUpload’] = false; // optional
		
		$this->facebook = new Facebook($config);
	}
	
	function getApiErrorsByHour (){
		
	}
}
