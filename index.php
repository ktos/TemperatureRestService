<?php
	/**
	 * Temperature Rest Service
	 *
	 */			
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	require '/vendor/autoload.php';	
	require '/system/Sensors.php';
	require '/system/Service.php';
	config('source', 'config.ini');	
	
	header('X-Powered-By: TemperatureRestService/2.0');
	
	error(500, function() {
		$s = Service::showError(500);
	});
	
	error(404, function() {
		$s = Service::showError(404);
	});
	
	on("GET", "/:sensor/:format", function($sensor, $format) {				
		Service::getSensorData($sensor, $format);		
	});
	
	on("GET", "/:sensor", function($sensor) {
		$format = 'json';
		if (strrpos($sensor, '.') !== FALSE)
		{		
			$format = substr($sensor, strrpos($sensor, '.') + 1);
			$sensor = substr($sensor, 0, strrpos($sensor, '.'));
		}
		
		Service::getSensorData($sensor, $format);
	});
	
	// informational page
	on('GET', '/', function() {
		render('index');
	});
	
	// handling everything, showing 404
	on('*', '', function() {
		error(404, "Not Found");
	});
	
	dispatch();
		
	/*$x = $s->createSensor(array(
		'name' => 'temperature.external',
		'data' => '0.0',
		'type' => 'temperature',
		'datatype' => 'float',
		'id' => '000ff',
		'description' => 'External temperature sensor',
		'lastupdated' => time(),
		'status' => TRUE
	));*/
		
?>