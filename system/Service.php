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
				
				render("data-$format", $i, FALSE);
			} else {
				error(404, 'Not found');
			}
		}
		
		public static function showError($errorCode) {
			$format = 'txt';
				
			switch ($errorCode) {
				case 500: { render("500-$format", null, FALSE); }
				case 404: { render("404-$format", null, FALSE); }
			}
		}
	}
