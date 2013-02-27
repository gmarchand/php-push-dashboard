<?php
class ducksboard {
	public $apikey = ''; //put your api key here
	public $widget = '';
	public $postfield = '';
	
	function __construct($apikey) {
		$this->apikey = $apikey;
	}
	function call($widget, $information) {
		$this->widget = $widget;
		$this->postfield = json_encode($information);
		


		$ch = curl_init('https://push.ducksboard.com/v/' . $this->widget);
		curl_setopt($ch, CURLOPT_USERPWD, $this->apikey . ":ignored");
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->postfield );
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		echo "DucksBoard Widget (".$this->widget.") - Request : ".$this->postfield." - Result : ";
		echo curl_exec ($ch);
		echo "\n";

		curl_close ($ch);

	}

}