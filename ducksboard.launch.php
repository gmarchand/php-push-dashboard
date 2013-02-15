<?php 
include 'zendesk.class.php';
include 'ducksboard.class.php';

require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_CalendarService.php';



class dashboard{
	private $settings;


	function __construct() {
		$this->settings = parse_ini_file('config.ini', false);

	}

	public function launchAll() {
	
		$arr = get_class_methods($this);
		foreach ($arr as $value) {
	
			if (strstr($value, 'push')) {
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
		$ducksboard->call($this->settings['DUCK_WDG_COUNT_INCIDENT'],array('value'=>$countIncident));
		$ducksboard->call($this->settings['DUCK_WDG_COUNT_PB'],array('value'=>$countProblem));
		$ducksboard->call($this->settings['DUCK_WDG_COUNT_OTHER'],array('value'=>$countOther));

	}


	function pushWidgetNonAssigne(){

		$zendesk = new zendesk($this->settings['ZENDESK_TOKEN'], $this->settings['ZENDESK_ACCOUNT'], $this->settings['ZENDESK_DOMAIN']);
		$res = $zendesk->call('/views/'.$this->settings['ZENDESK_VIEW_NONASSIGNE_ID'].'/execute', '', 'GET');


		$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
		$ducksboard->call($this->settings['DUCK_WDG_ZENDESK_VIEW_NON_ASSIGNE'],array('value'=>$res->count));
	}


	function pushWidgetResponsable(){

		// Load the key in PKCS 12 format (you need to download this from the
		// Google API Console when the service account was created.
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
		$today->setTime(0, 0);
		$tomorrow = new DateTime('today');
		$tomorrow->setTime(23, 59);
		$events = $cal->events->listEvents($this->settings['GOOGLE_CAL_CALID'],array('singleEvents'=> true,'orderBy' => 'startTime', 'fields' => 'items(end,start,summary),summary'
				,'timeMax'=>$tomorrow->format(DATE_RFC3339),'timeMin' =>$today->format(DATE_RFC3339)
		)
		);

		//var_dump($events);
		if(is_array($events['items'])) {
			$event = $events['items'][0];
			echo $resp_affectation = $event['summary'];

			$ducksboard = new ducksboard($this->settings['DUCK_APIKEY']);
			$ducksboard->call($this->settings['DUCK_WDG_RESPONSABLE_ZENDESK'],array('value'=>array("content"=>"Responsable Affectation : ".$resp_affectation)));

		}
	}
}

$dashboard = new dashboard();
$dashboard->launchAll();
?>