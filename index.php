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

	// error handlers
	error(500, function() {
		$s = Service::showError(500);
	});
	
	error(404, function() {
		$s = Service::showError(404);
	});
	
	error(403, function() {
		$s = Service::showError(403);
	});
	
	// GET: /exampleSensor/json
	// Getting data from a sensor with a specified format
	on("GET", "/:sensor/:format", function($sensor, $format) {				
		Service::getSensorData($sensor, $format);		
	});
	
	// GET: /exampleSensor.json
	// Getting data from a sensor without a specified format or with format
	// specified like an extension
	on("GET", "/:sensor", function($sensor) {
		$format = 'html';
		if (strrpos($sensor, '.') !== FALSE)
		{		
			$format = substr($sensor, strrpos($sensor, '.') + 1);
			$sensor = substr($sensor, 0, strrpos($sensor, '.'));
		} else {
			$negotiator = new \Negotiation\FormatNegotiator();
			
			$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], Service::avaliableFormats());			
			$format = Service::formatToExtension($format->getValue());
		}		
		
		Service::getSensorData($sensor, $format);
	});
	
	// PUT: /exampleSensor
	// Creates (or updates) sensor data
	on("PUT", "/:sensor", function($sensor) {
		Service::putSensor($sensor);
	});
	
	// informational page, HTML format only
	on('GET', '/', function() {
		render('index');
	});
	
	// handling everything else, showing 404
	on('*', '', function() {
		error(404, "File Not Found");
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