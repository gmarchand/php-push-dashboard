<?php

namespace Analog\Handler;

/**
 * Send the output to echo
 *
 * Usage:
 *
 *     Analog::handler (Analog\Handler\stdout::init ());
 *
 *     Analog::log ('Log me');
 *
 * Note: Uses Analog::$format for the appending format.
 */
class stdout {
	public static function init () {
		return function ($info, $buffered = false) {
			echo $info['date'].' - ';
			print_r($info['message']);
			echo "\n";
		};
	}
}