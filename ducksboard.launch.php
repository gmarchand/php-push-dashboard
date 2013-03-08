<?php 
include 'zendesk.class.php';
include 'ducksboard.class.php';
include_once 'facebookApp.class.php';

require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_CalendarService.php';

// Determine the environment

if ($env = getenv('APP_ENV')) {
	if (!defined('APP_ENV')) define('APP_ENV', $env);
} else {
	if (!defined('APP_ENV')) define('APP_ENV', 'local');
}

// Log Manager
if(APP_ENV == 'local'){
	Analog::handler (Analog\Handler\Threshold::init (Analog\Handler\stdout::init (),Analog::DEBUG));
} else {
	Analog::handler (Analog\Handler\Threshold::init (Analog\Handler\stdout::init (),Analog::NOTICE));
}

class dashboard{
	private $settings;


	function __construct() {
		$this->settings = parse_ini_file('config.ini', false);

	}

	public function launchAll() {

		$arr = get_class_methods($this);
		foreach ($arr as $value) {

			if (strstr($value, 'push')) {
				Analog::notice("Function : ".$value);
				$this -> $value();
			}
		}
	}
	function pushWidgetZendeskCount(){
		$zendesk = new zendesk($this->settings['ZENDESK_TOKEN'], $this->settings['ZENDESK_ACCOUNT'], $this->settings['ZENDESK_DOMAIN']);
		$res = $zendesk->call('/views/'.$this->settings['ZENDESK_VIEW_NONRESOLU'].'/execute', '', 'GET');

		$countIncident = 0;
		$countProblem = 0;
		$countOther = 0;
		foreach($res->rows as $value){
			switch($value->ticket->type){
				case 'incident' : $countIncident++;break;
				case 'problem' : $countProblem++;break;
				default : $countOther ++; break;

			}
		}

		$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
		$ducksboard->call($this->settings['DUCK_WDG_ZENDESK_COUNT_INCIDENT'],array('value'=>$countIncident));
		$ducksboard->call($this->settings['DUCK_WDG_ZENDESK_COUNT_PB'],array('value'=>$countProblem));
		$ducksboard->call($this->settings['DUCK_WDG_ZENDESK_COUNT_OTHER'],array('value'=>$countOther));
	}


	function pushWidgetNonAssigne(){

		$zendesk = new zendesk($this->settings['ZENDESK_TOKEN'], $this->settings['ZENDESK_ACCOUNT'], $this->settings['ZENDESK_DOMAIN']);
		$res = $zendesk->call('/views/'.$this->settings['ZENDESK_VIEW_NONASSIGNE_ID'].'/execute', '', 'GET');


		$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
		$ducksboard->call($this->settings['DUCK_WDG_ZENDESK_VIEW_NON_ASSIGNE'],array('value'=>$res->count));
	}


	function pushWidgetFbCreateObject() {
		$fbapp = new facebookapp();
		$fbapp->initCplusApp();
		$res = $fbapp->getApiObjectCreateByHour();
		$arr_res = $res['data'][0]['values'];

		foreach($arr_res as $day){
			// Boucle sur chaque date
			$date = $day['end_time'];
			$timestamp = strtotime($date);
			//print_r($day);
			$videoObj = 0;
			$profileObj = 0;
			$articleObj = 0;
			$websiteObj = 0;
			$other = 0;
			foreach($day['value'] as $value) {
				if (!strncmp($value['object_type_name'], 'video', strlen('video'))){
					$videoObj += $value['value'];
				} else if ($value['object_type_name'] == 'profile'){
					$profileObj += $value['value'];
				} else if ($value['object_type_name'] == 'article'){
					$articleObj += $value['value'];
				} else if ($value['object_type_name'] == 'website'){
					$websiteObj += $value['value'];
				} else {
					$other += $value['value'];
				}
			}
			$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
			$ducksboard->call($this->settings['DUCK_WDG_FBCR_VIDEO'],array('value'=>$videoObj,'timestamp' =>$timestamp));
			$ducksboard->call($this->settings['DUCK_WDG_FBCR_PROFILE'],array('value'=>$profileObj,'timestamp' =>$timestamp));
			$ducksboard->call($this->settings['DUCK_WDG_FBCR_ARTICLE'],array('value'=>$articleObj,'timestamp' =>$timestamp));
			$ducksboard->call($this->settings['DUCK_WDG_FBCR_WEBSITE'],array('value'=>$websiteObj,'timestamp' =>$timestamp));
				
				
		}

	}

	function pushWidgetFbApiError() {
		$fbapp = new facebookapp();
		$fbapp->initCplusApp();
		$res = $fbapp->getApiErrorsByHour();
		$arr_res = $res['data'][0]['values'];

		foreach($arr_res as $day){
			// Boucle sur chaque date
			$date = $day['end_time'];
			$timestamp = strtotime($date);
			$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
			$ducksboard->call($this->settings['DUCK_WDG_FBAPI_ERROR'],array('value'=> $day['value'],'timestamp' =>$timestamp));
				
		}

		$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);

	}

	function pushWidgetResponsable(){

		$client = new Google_Client();

		$key = file_get_contents($this->settings['GOOGLE_KEY_FILE']);
		$client->setClientId($this->settings['GOOGLE_CLIENT_ID']);
		$client->setAssertionCredentials(new Google_AssertionCredentials(
				$this->settings['GOOGLE_SERVICE_ACCOUNT_NAME'],
				array('https://www.googleapis.com/auth/calendar'),
				$key)
		);

		$cal = new Google_CalendarService($client);
		$today = new DateTime('today');
		$today->setTime(0, 5);
		$tomorrow = new DateTime('today');
		$tomorrow->setTime(23, 59);
		$params = array('singleEvents'=> true,'orderBy' => 'startTime'
				, 'fields' => 'items(end,start,summary),summary'
				,'timeMax'=>$tomorrow->format(DATE_RFC3339)
				,'timeMin' =>$today->format(DATE_RFC3339));
		
		$events = $cal->events->listEvents($this->settings['GOOGLE_CAL_CALID'],$params);
		
		foreach($events['items'] as $event){
			$date = strtotime($event['start']['date']);
			$today = new DateTime('today');
			$today->setTime(0, 0);
			if($today->getTimestamp() == $date){
				$resp_affectation = $event['summary'];
				break;
			}
		}
			
					
		$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
		$ducksboard->call($this->settings['DUCK_WDG_RESPONSABLE_ZENDESK'],array('value'=>array("content"=>"Responsable Affectation : ".$resp_affectation)));

	}
}


$dashboard = new dashboard();
$dashboard->launchAll();
?>