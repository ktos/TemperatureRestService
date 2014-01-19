<?php
	/**
	 * Temperature Rest Service
	 *
	 */
	
	define('DEBUG', true);
	
	$config['sensors'] = array(
		'main' => '0000054d332a'
	);
	
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
	
	$return = "Bad Request";
	$returnCode = 400;
	$returnObj = null;
	if (array_key_exists('sensor', $_GET) && (!empty($_GET['sensor'])))
	{
		$requested = $_GET['sensor'];
		
		if (preg_match('/^[a-z0-9]$/', $requested) == 1)
		{
			if (array_key_exists($requested, $config['sensors']))
			{
				$return = "OK";
				$returnCode = 200;
				$returnObj = readTemperature($requested);
			}
			else
			{
				$return = "Not Found";
				$returnCode = 404;
					
				if ($config['loose'] === true)
				{
					$s = null;
					foreach ($config['sensors'] as $k => $v)
						if ($v == $requested)
							$s = $k;
				
					if ($s !== null)
					{
						$return = "OK";
						$returnCode = 200;
						$returnObj = readTemperature($s);
					}
				}
			}
		}
	}
	
	header("$returnCode $return");
	header('Content-type: application/json');
	echo json_encode(array('code' => $returnCode, 'message' => $return, 'data' => $returnObj), JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);

?>