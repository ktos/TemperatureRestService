<?php
	class Service
	{
		public static function putSensor($sensorName)
		{
			$s = new Sensors();
			
			$sensorData = json_decode(file_get_contents('php://input'), TRUE);
			if (($sensorData === null) || (!array_key_exists('data', $sensorData)))
				error(400, 'Bad request');						
			
			if (!array_key_exists("HTTP_X_APIKEY", $_SERVER)) {
				if ((!array_key_exists('apikey', $sensorData)) || ($sensorData['apikey'] != config('sensors.apikey')))
					error(401, 'Unauthorized');
			} else {
				if (config('sensors.apikey') != $_SERVER['HTTP_X_APIKEY'])
					error(401, 'Unauthorized');
			}
			
			$sensorData['name'] = $sensorName;
			
			if ($s->findSensor($sensorName))
				$r = $s->updateSensor($sensorName, $sensorData);
			else
				$r = $s->createSensor($sensorData);
			
			if (!$r)
				error(500, 'Internal Server Error');
			else
			{
				$negotiator = new \Negotiation\FormatNegotiator();
			
				$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], Service::avaliableFormats());
				$format = $format->getValue();
				$formatExt = Service::formatToExtension($format);
				header('HTTP/1.1 201 Created');
				render("error-$formatExt", array('code' => 201, 'message' => 'Created', 'message2' => 'Sensor data has been created (or updated) sucessfully.'), $format === 'text/html'? null : FALSE );
				
			}
			
		}		
						
		public static function getSensorData($sensorName, $format)
		{
			$s = new Sensors();
			$i = $s->getSensorInfo($sensorName);
			
			if ($i !== FALSE) {
				if (array_key_exists('status', $i)) {
					if ((bool)$i['status'] === FALSE) {
						error(500, 'Internal Server Error');
					}
				}
				
				header('Content-Type: ' . self::extensionToFormat($format));
				if ($format === 'html')
					render("data-$format", $i);
				else {
					render("data-$format", $i, FALSE);
				}
			} else {
				error(404, 'Not found');
			}
		}
		
		public static function showError($errorCode) {
			$negotiator = new \Negotiation\FormatNegotiator();
			
			$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], Service::avaliableFormats());
			$formatExt = Service::formatToExtension($format->getValue());
			
			switch ($errorCode) {
				case 500: { $data = array('code' => 500, 'message' => 'Internal Server Error', 'message2' => 'Something went wrong: there is application error or sensor data is marked as wrong.'); break; }
				case 404: { $data = array('code' => 404, 'message' => 'File Not Found', 'message2' => 'Specified sensor cannot be found (or wrong path)!'); break; }
				case 403: { $data = array('code' => 403, 'message' => 'Unathorized', 'message2' => 'You need to send proper APIKEY header to use this resource'); break; }
				case 400: { $data = array('code' => 400, 'message' => 'Bad request', 'message2' => 'Only JSON-encoded data is supported, name and data are required parameters.'); break; }
				case 401: { $data = array('code' => 401, 'message' => 'Unauthorized', 'message2' => 'You haven\'t sent apikey variable in your JSON data or the APIKEY is not valid.'); break; }
				
				default: { error(500, 'Internal Server Error'); }
			}
			
			header('Content-Type: ' . $format->getValue());
			if ($formatExt === 'html')
				render("error-$formatExt", $data);
			else {
				render("error-$formatExt", $data, FALSE);
			}
		}
		
		public static $formatMap;
		
		public static function extensionToFormat($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return $key;
			}
			
			return FALSE;
		}
		
		public static function isExtension($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return TRUE;
			}
			
			return FALSE;
		}
		
		public static function formatToExtension($format) {
			return Service::$formatMap[$format];
		}
		
		public static function avaliableFormats() {			
			return array_keys(Service::$formatMap);
		}
	}

	Service::$formatMap = array(
		'text/html' => 'html',
		'text/plain' => 'txt',
		'application/json' => 'json'
	);