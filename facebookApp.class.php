<?php
require_once 'vendors/facebook-php-sdk/src/facebook.php';

class facebookapp {
	
	private $settings;
	private $fb = null;
	
	function __construct() {
		$this->settings = parse_ini_file('config.ini', false);
	
	}
	
	function initCplusApp(){
		$config = array();
		$config['appId'] = $this->settings['FB_CPLUS_APPID'];
		$config['secret'] = $this->settings['FB_CPLUS_SECRET'];
		$config['fileUpload'] = false; // optional
		
		$this->fb = new Facebook($config);
	}
	
	function getApiObjectCreateByHour (){
		$since = strtotime("8 days ago");
		$until = strtotime("tomorrow");
		$api = "/insights/application_opengraph_object_create?since=".$since."&until=".$until;
		$result = $this->fb->api('/'.$this->settings['FB_CPLUS_APPID'].$api);
		return $result;
	}
	
	function getApiErrorsByHour (){
		$since = strtotime("8 days ago");
		$until = strtotime("tomorrow");
		$api = "/insights/application_api_errors?since=".$since."&until=".$until;
		$result = $this->fb->api('/'.$this->settings['FB_CPLUS_APPID'].$api);
		return $result;
	}
}
