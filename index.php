<?php
	/**
	 * Temperature Rest Service
	 *
	 */	
	
	require 'config.php';
	
	$config['formats'] = array('json', 'plain', 'wns');
	
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
					if ($v == $requested)
						return $k;
			}
			
			return null;
		}
		
		public static function isSensorValid($sensorName)
		{
			global $config;
			return (preg_match('/^[a-z0-9]*$/', $sensorName) == 1);
		}
	}
	
	
	class Response
	{
		private $data;
		private $error;		
		private $format;
		
		public function __construct($format)
		{
			$this->format = $format;
		}
				
		private function plain()
		{
			header('Content-type: text/plain');
			echo ($this->data === null)? $this->error : $this->data;
		}
		
		private function json()
		{
			header('Content-type: application/json');
			echo json_encode(array('data' => $this->data), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
		}
		
		private function wns()
		{
			header('Content-type: application/xml');
			$fdata = ($this->data === null)? $this->error : round((float)$this->data, 1) . ' °C';
			$result = '<tile><visual><binding template="TileSquareText01"><text id="1">' . $fdata . '</text></binding></visual></tile>';
			echo $result;
		}
		
		public function send($httpCode, $httpMessage, $data = null)
		{
			$this->data = $data;
			$this->error = ($httpCode !== 200)? $httpMessage : null;
			
			header("HTTP/1.1 $httpCode $httpMessage");
			header('X-Powered-By: TemperatureRestService/1.0');
			
			$a = $this->format;
			$this->$a();
			
			die();
		}
	}

	if (!array_key_exists('format', $_GET) || (empty($_GET['format'])) || (!in_array($_GET['format'], $config['formats'])))
		$format = 'plain';
	else {
		$format = $_GET['format'];
	}
		
	$r = new Response($format);
		
	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
		$r->send(400, 'Bad Request');	
		
	if (!array_key_exists('sensor', $_GET) || (empty($_GET['sensor'])))
		$r->send(400, 'Bad Request');
	
	// id of the requested sensor
	$requested = $_GET['sensor'];

	if (Sensor::isSensorValid($requested)) {
		$s = Sensor::findSensorName($requested);
		
		if ($s !== null)
			$r->send(200, 'OK', Sensor::readTemperature($s));
		else {
			$r->send(404, 'Not Found');
		}
	} else {
		$r->send(400, 'Bad Request');
	}
?>