<?php
	/**
	 * Temperature Rest Service
	 *
	 */
	
	require 'config.php';
	
	/*
	 * Reads temperature from a sensor of a given id
	 *
	 * @param string $sensorName
	 * @returns mixed Temperature in Celsius as a float or NULL if error occured
	 */
	function readTemperature($sensorName)
	{
		if (DEBUG)
			$f = "74 01 4b 46 7f ff 0c 10 55 : crc=55 YES
74 01 4b 46 7f ff 0c 10 55 t=23250
";
		else
			$f = file_get_contents("/sys/bus/w1/devices/28-$sensorName/w1_slave");
			
		if ($f === false)
			return null;
			
		$matches = array();
		preg_match('/t=([0-9]{5})/', $f, $matches);
		
		return (int)$matches[1] / 1000;
	}
	
	/**
	 * Sends response and finishes script
	 *
	 * @param int $httpCode
	 * @param string $httpMessage
	 * @param mixed $data
	 */
	function sendResponse($httpCode, $httpMessage, $data = null)
	{
		header("$httpCode $httpMessage");
		header('X-Powered-By: TemperatureRestService');
		
		if (strstr($_SERVER["HTTP_ACCEPT"], "application/json")) {			
			header('Content-type: application/json');
			echo json_encode(array('code' => $httpCode, 'message' => $httpMessage, 'data' => $data), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);			
		} else {
			header('Content-type: text/plain');
			echo ($data === null)? $httpMessage : $data;
		}
		
		die();
	}
	
	/**
	 * Finds sensor name based on id
	 *
	 * @param int $sensorId
	 * @returns mixed
	 */
	function findSensorName($sensorId)
	{
		foreach ($config['sensors'] as $k => $v)
			if ($v == $requested)
				return $k;
	
		return null;
	}
		
	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
		sendResponse(400, 'Bad Request');	
		
	if (!array_key_exists('sensor', $_GET) || (empty($_GET['sensor'])))
		sendResponse(400, 'Bad Request');
	
	// id of the requested sensor
	$requested = $_GET['sensor'];

	// check if sensor id is valid
	if (preg_match('/^[a-z0-9]*$/', $requested) == 1) {
		if (array_key_exists($requested, $config['sensors'])) {
			sendResponse(200, 'OK', readTemperature($requested));
		}
		else {									
			if ($config['loose'] === true) {
				$s = findSensorName($requested);

				if ($s !== null) {
					sendResponse(200, 'OK', readTemperature($s));
				}
			}
			
			sendResponse(404, 'Not Found');
		}
	} else {	
		sendResponse(400, 'Bad Request');
	}
?>