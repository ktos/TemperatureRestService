<?php
	class Sensor
	{
		/*
		 * Reads temperature from a sensor of a given id
		 *
		 * @param string $sensorName
		 * @returns mixed Temperature in Celsius as a float or NULL if error occured
		 */
		public static function readTemperature($sensorName)
		{
			if ($sensorName === NULL)
				return NULL;
			
			if (DEBUG && $sensorName == "test")
				$f = "74 01 4b 46 7f ff 0c 10 55 : crc=55 YES
	74 01 4b 46 7f ff 0c 10 55 t=23250
	";
			else
				$f = file_get_contents("/sys/bus/w1/devices/28-$sensorName/w1_slave");
				
			if ($f === false)
				return NULL;
				
			$matches = array();
			preg_match('/t=([0-9]+)/', $f, $matches);
			
			return (int)$matches[1] / 1000;
		}
		
		/**
		 * Finds sensor name based on id
		 *
		 * @param int $sensorId
		 * @returns mixed
		 */
		public static function findSensorName($sensorId)
		{
			global $config;
			
			if (array_key_exists($sensorId, $config['sensors'])) {
				return $config['sensors'][$sensorId];
			} else if ($config['loose'] === TRUE) {
				foreach ($config['sensors'] as $k => $v)
					if ($v == $sensorId)
						return $k;
			}
			
			return NULL;
		}
		
		public static function isSensorValid($sensorName)
		{
			global $config;
			return (preg_match('/^[a-z0-9]*$/', $sensorName) == 1);
		}
	}

?>