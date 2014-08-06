<?php
	class Service
	{				
		public static function getSensorData($sensorName, $format)
		{
			$s = new Sensors();
			$i = $s->getSensorInfo($sensorName);
			
			if ($i !== FALSE) {
				if ((bool)$i['status'] === FALSE) {
					error(500, 'Internal Server Error');
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
			
			$format = $negotiator->getBest($_SERVER['HTTP_ACCEPT'], array('application/json', 'text/html', 'text/plain'));
			$formatExt = Service::formatToExtension($format->getValue());
			
			switch ($errorCode) {
				case 500: { $data = array('code' => 500, 'message' => 'Internal Server Error', 'message2' => 'Something went wrong: there is application error or sensor data is marked as wrong.'); break; }
				case 404: { $data = array('code' => 404, 'message' => 'File Not Found', 'message2' => 'Specified sensor cannot be found (or wrong path)!'); break; }
				case 403: { $data = array('code' => 403, 'message' => 'Unathorized', 'message2' => 'You need to send proper APIKEY header to use this resource'); break; }
				
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
		
		private static function extensionToFormat($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return $key;
			}
			
			return FALSE;
		}
		
		private static function isExtension($ext) {
			foreach (Service::$formatMap as $key => $value) {
				if ($value === $ext)
					return TRUE;
			}
			
			return FALSE;
		}
		
		private static function formatToExtension($format) {
			return Service::$formatMap[$format];
		}
	}

	Service::$formatMap = array(
		'text/html' => 'html',
		'text/plain' => 'txt',
		'application/json' => 'json'
	);