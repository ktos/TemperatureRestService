<?php
	/**
	 * Temperature Rest Service
	 *
	 */			
	
	require 'config.php';
	
	if (DEBUG) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
	
	$config['formats'] = array('json', 'txt', 'wns');
	
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
	
	
	class Response
	{
		private $data;
		private $error;		
		private $format;
		
		public function __construct($format)
		{
			$this->format = $format;
		}
				
		private function txt()
		{
			header('Content-type: text/plain');
			echo ($this->data === NULL)? $this->error : $this->data;
		}
		
		private function json()
		{
			header('Content-type: application/json');
			echo json_encode(array('data' => $this->data, 'error' => $this->error), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
		}
		
		private function wns()
		{
			date_default_timezone_set('UTC');
			header('X-WNS-Expires: ' . date(DATE_RFC850, strtotime("+1 hour")));
			header('Content-type: application/xml;encoding=utf-8');
			$fdata = ($this->data === NULL)? $this->error : round((float)$this->data, 1) . ' °C';
			$fsen = ($this->sensor === NULL)? '' : '<text id="2">' . $this->sensor . '</text>';
			$result = '<tile><visual><binding template="TileSquareText01"><text id="1">' . $fdata . '</text>' . $fsen . '</binding></visual></tile>';
			echo $result;
		}
		
		public function send($httpCode, $httpMessage, $data = NULL, $sensor = null)
		{
			$this->data = $data;
			$this->sensor = $sensor;
			$this->error = ($httpCode !== 200)? $httpMessage : NULL;
			
			header("HTTP/1.1 $httpCode $httpMessage");
			header('X-Powered-By: TemperatureRestService/1.0');
			
			$a = $this->format;
			$this->$a();
			
			die();
		}
	}

	if (!array_key_exists('format', $_GET) || (empty($_GET['format'])))
		$format = 'txt';
	else {
		if (!in_array($_GET['format'], $config['formats'])) {
			$r = new Response('txt');
			$r->send(400, 'Bad Request');
		}
		
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
		
		if ($s !== NULL) {
			$t = Sensor::readTemperature($s);			
			
			if ($t !== NULL) {					
				$r->send(200, 'OK', $t, $requested);
				
			} else {
				$r->send(500, 'Internal Server Error');
			}
		} else {
			$r->send(404, 'Not Found');
		}
	} else {
		$r->send(400, 'Bad Request');
	}
?>