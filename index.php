<?php
	/**
	 * Temperature Rest Service
	 *
	 */
	
	# is in debug mode?
	define('DEBUG', true);
	
	# list of sensors, in a form of prettyName => sensorId
	$config['sensors'] = array(
		'main' => '0000054d332a'
	);
	
	# get not only by names, but also by ids?
	$config['loose'] = true;
	
	/*
	 * Reads temperature from a sensor of a given id
	 *
	 * @param string $sensorId
	 * @returns mixed Temperature in Celsius as a float or NULL if error occured
	 */
	function readTemperature($sensorId)
	{
		if (DEBUG)
			$f = "74 01 4b 46 7f ff 0c 10 55 : crc=55 YES
74 01 4b 46 7f ff 0c 10 55 t=23250
";
		else
			$f = file_get_contents("/sys/bus/w1/devices/28-$sensorId/w1_slave");
			
		if ($f === false)
			return false;
			
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
		header('Content-type: application/json');
		header('X-Powered-By: TemperatureRestService');
		echo json_encode(array('code' => $httpCode, 'message' => $httpMessage, 'data' => $data), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
		die();
	}
		
	if (array_key_exists('sensor', $_GET) && (!empty($_GET['sensor'])))
	{
		$requested = $_GET['sensor'];		
		
		if (preg_match('/^[a-z0-9]*$/', $requested) == 1)
		{		
			if (array_key_exists($requested, $config['sensors']))
			{
				sendResponse(200, 'OK', readTemperature($requested));
			}
			else
			{									
				if ($config['loose'] === true)
				{
					$s = null;
					foreach ($config['sensors'] as $k => $v)
						if ($v == $requested)
							$s = $k;
				
					if ($s !== null)
					{
						sendResponse(200, 'OK', readTemperature($s));
					}
				}
				
				sendResponse(404, 'Not Found');
			}
		}		
	}
	sendResponse(400, 'Bad Request');
	
	

?>