<?php
	/**
	 * Temperature Rest Service
	 *
	 */			
	
	error_reporting(E_ALL);
		ini_set('display_errors', 1);
	
	require '/vendor/autoload.php';	
	//require 'config.php';	
	config('dispatch.url', 'http://localhost/TemperatureApp/TemperatureRestService');	
	
	on("GET", "/:sensor/:format", function($sensor, $format) {
		echo 'test1';
		echo $sensor;
		echo "<br>$format";
	});
	
	on("GET", "/:sensor", function($sensor) {		
		$format = substr($sensor, strrpos($sensor, '.') +1);
		$sensor = substr($sensor, 0, strrpos($sensor, '.'));
		
		echo "$sensor<br>$format";
	});
	
	// informational page
	on('GET', '/', function() {
		echo 'Index';
	});
	
	// handling everything, showing 404
	on('*', '', function() {
		error(404, "Not Found");
	});
	
	dispatch();
?>