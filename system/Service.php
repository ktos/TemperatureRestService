<?php

	/**
	 * Class for handling most requests
	 * 
	 * This class handles requests and responses in the system, routed from dispatcher framework
	 */
	class Service
	{
		/**
		 * Handles updating sensor information
		 * 
		 * This function checks if data sent to PUT/POST request is a valid JSON, if it have
		 * proper required data set, and if user is authorized by sending proper APIKEY along
		 * with request. If everything is true, it runs proper methods from Service class.
		 * 
		 * HTTP error 400 is returned if there is a problem with request, 401 if the APIKEY is
		 * not set or is invalid and 500 if there was a problem saving data. 201 is served
		 * if everything went fine.
		 * 
		 * @param string $sensorName Name of a sensor to be updated		 
		 */
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
			
				if (array_key_exists("HTTP_ACCEPT", $_SERVER))
				{
					$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], Service::avaliableFormats());
					$format = $format->getValue();
				} 
				else
				{
					$format = "text/plain";
				}
				
				$formatExt = Service::formatToExtension($format);
				header('HTTP/1.1 201 Created');
				render("error-$formatExt", array('code' => 201, 'message' => 'Created', 'message2' => 'Sensor data has been created (or updated) sucessfully.'), $format === 'text/html'? null : FALSE );
				
			}
			
		}		
						
		/**
		 * Handles requesting sensor data from a user
		 * 
		 * This function handles requesting data from a sensor in a format
		 * user requested (text, json, html...). It will return 500 error
		 * if sensor data is marked as invalid (status is false), 404 if
		 * $sensorName was not found and 200 if everything was fine.
		 * 
		 * @param string $sensorName Name of a sensor to recover data
		 * @param string $format A requested format of data. Not checked, must be in @see formatMap!
		 */
		public static function getSensorData($sensorName, $format)
		{
			$s = new Sensors();
			$i = $s->getSensorData($sensorName);
			
			if ($i !== FALSE) {
				if (array_key_exists('status', $i)) {
					if ((bool)$i['status'] === FALSE) {
						error(500, 'Internal Server Error');
					}
				}
				
				header('Content-Type: ' . self::extensionToFormat($format));
				
				// additional code fragments for specific response formats
				// like WNS which requires additional headers sent
				if (file_exists("./system/$format.php"))
					include "./system/$format.php";							
				
				// handling sensortype-specific view formats
				$view = "data-$format";								
				if (array_key_exists("sensortype", $i)) {
					$st = $i['sensortype'];
					
					if (file_exists(config('dispatch.views') . "$st-$format.html.php"))
						$view = "$st-$format";	
				}				
				
				if ($format === 'html')
					render($view, $i);
				else {
					render($view, $i, FALSE);
				}
			} else {
				error(404, 'Not found');
			}
		}
		
		/**
		 * Handles showing different kind of errors
		 * 
		 * This functions shows codes and messages if HTTP error must be served
		 * 
		 * @param int $errorCode Code error. Supported are 400, 401, 404 and 500.
		 */
		public static function showError($errorCode) {
			$negotiator = new \Negotiation\FormatNegotiator();
			
			if (array_key_exists("HTTP_ACCEPT", $_SERVER))
			{
				$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], Service::avaliableFormats());
				$format = $format->getValue();
			} 
			else
			{
				$format = "text/plain";
			}
			
			$formatExt = Service::formatToExtension($format);
			
			switch ($errorCode) {
				case 500: { $data = array('code' => 500, 'message' => 'Internal Server Error', 'message2' => 'Something went wrong: there is application error or sensor data is marked as wrong.'); break; }
				case 404: { $data = array('code' => 404, 'message' => 'File Not Found', 'message2' => 'Specified sensor cannot be found (or wrong path)!'); break; }				
				case 400: { $data = array('code' => 400, 'message' => 'Bad request', 'message2' => 'Only JSON-encoded data is supported, name and data are required parameters.'); break; }
				case 401: { $data = array('code' => 401, 'message' => 'Unauthorized', 'message2' => 'You haven\'t sent APIKEY variable in your JSON data or the APIKEY is not valid.'); break; }
				
				default: { error(500, 'Internal Server Error'); }
			}
			
			header('Content-Type: ' . $format);
			if ($formatExt === 'html')
				render("error-$formatExt", $data);
			else {
				render("error-$formatExt", $data, FALSE);
			}
		}
		
		/**
		 * A map with every possible format supported in style of content-type/format extension
		 */
		public static $formatMap;
		
		/**
		 * Returns format Content-Type associated with extension or FALSE if there is no such format
		 * 
		 * @param string $ext
		 * @return string
		 */
		public static function extensionToFormat($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return $key;
			}
			
			return FALSE;
		}
		
		/**
		 * Checks if specified extension is in format map
		 * 
		 * @param string $ext
		 * @return bool
		 */
		public static function isExtension($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return TRUE;
			}
			
			return FALSE;
		}
		
		/**
		 * Returns extension associated with specified content type
		 * 
		 * @param string $format
		 * @return string
		 */
		public static function formatToExtension($format) {
			return Service::$formatMap[$format];
		}
		
		/**
		 * Returns a list of all avaliable content-types
		 * 
		 * @return array
		 */
		public static function avaliableFormats() {			
			return array_keys(Service::$formatMap);
		}
	}

	Service::$formatMap = array(
		'text/html' => 'html',
		'text/plain' => 'txt',
		'application/json' => 'json',
		'application/xml' => 'wns'
	);