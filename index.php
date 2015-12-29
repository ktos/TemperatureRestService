<?php
	/**
	 * Temperature Rest Service
	 * 
	 * A RESTful service for storing, updating and sending data from different types of
	 * sensors, mostly temperature sensors. A POST to a specific URL will create a sensor
	 * data, or update it, and such data may be requested in different forms by GET requests.
	 * 
	 * @package TemperatureRestService
	 * @license https://raw.githubusercontent.com/ktos/TemperatureRestService/master/LICENSE
	 *  
	 * Copyright (c) 2014 Marcin Badurowicz
	 * 
	 * Permission is hereby granted, free of charge, to any person obtaining a copy of
	 * this software and associated documentation files (the "Software"), to deal in
	 * the Software without restriction, including without limitation the rights to
	 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
	 * the Software, and to permit persons to whom the Software is furnished to do so,
	 * subject to the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
	 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
	 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
	 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 * 
	 */	
	
	require 'vendor/autoload.php';	
	require 'system/Sensors.php';
	require 'system/Service.php';
    require 'system/Plugins.php';
	config('source', 'config.ini');
    
    date_default_timezone_set(config('timezone'));       
    
    if (config('debug') == 1) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
    }    
    
	define('TEMPERATURERESTSERVICE', 'TemperatureRestService/2.1');
	    
    if (config('expose') == 1)
	   header('X-Powered-By: ' . TEMPERATURERESTSERVICE);

    // build format map from config file
    Service::buildFormatMap();

	// error handlers
	error(500, function() { Service::showError(500); });
	error(404, function() { Service::showError(404); });
	error(403, function() { Service::showError(403); });
	error(400, function() { Service::showError(400); });
	error(401, function() { Service::showError(401); });
    error(406, function() { Service::showError(406); });
	
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
	// Creates (or updates) sensor data with a name specified
	on("PUT", "/:sensor", function($sensor) {
		Service::putSensor($sensor);
	});
	
	// POST: /exampleSensor
	// Creates (or updates) sensor data with a name specified
	on("POST", "/:sensor", function($sensor) {
		Service::putSensor($sensor);
	});
	
	// POST: /
	// Creates (or updates) sensor data with a name coming from a JSON array
	on("POST", "/", function() {
		$sensorData = json_decode(file_get_contents('php://input'), TRUE);
			if (($sensorData === null) || (!array_key_exists('name', $sensorData)))
				error(400, 'Bad request');		
		
		Service::putSensor($sensorData['name']);
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
?>