<?php
	/**
	 * Temperature Rest Service
	 *
	 */
	 	 
	class Sensors {
	 
	 	public function findSensor($sensorName) {
	 		$dir = config('sensors.data');	 			
	 		if (file_exists("$dir/$sensorName") && is_file("$dir/$sensorName")) {			
				return "$dir/$sensorName";
			} else {
				return FALSE;
			}
	 	}
	 
	 	private function readSensor($sensorName) {
	 		$fname = $this->findSensor($sensorName);
	 		if ($fname !== FALSE) {
	 			return file($fname);				
	 		} else {
				return FALSE;
			}
	 	}
		
		private function readSection($section, $sensorData) {
			$matches = array();
			for ($i = 0; $i < count($sensorData); $i++) {
				if (preg_match("/$section: (.*)/", $sensorData[$i], $matches) === 1) {					
					return $matches[1];
				}
			}
			
			return FALSE;
		}
	 
		public function getSensorData($sensorName) {
			$s = $this->readSensor($sensorName);
			if ($s !== FALSE) {
				$data = $this->readSection("data", $s);
				$datatype = $this->readSection('datatype', $s);
				
				switch ($datatype) {
					case 'float': { $data = (float)$data; break; }
					case 'int': { $data = (int)$data; break; }
					default: { $data = (string)$data; break; }			
				}
				
				return $data;
			} else {
				return FALSE;
			}
		}
		
		public function getSensorInfo($sensorName) {
			$sensorData = $this->readSensor($sensorName);
			if ($sensorData === FALSE)
				return FALSE;
			
			$matches = array();
			for ($i = 0; $i < count($sensorData); $i++) {
				if (preg_match("/([a-z]+)\: (.*)/", $sensorData[$i], $matches) === 1) {
					$result[$matches[1]] = $matches[2];
				}
			}
			
			$result['name'] = $sensorName;
			
			/*
			$result['name'] = $this->;
			$result['data'] = '';
			$result['type'] = '';
			$result['id'] = '';
			$result['description'] = '';
			$result['lastupdated'] = '';
			$result['status'] = '';*/
			
			return $result;
		}
		
		private function writeSensorData($sensorData) {
			$file = '';
			foreach ($sensorData as $key => $value) {
				$file .= "$key: $value\n";				
			}
			
			return (file_put_contents(config('sensors.data') . "/$sensorData[name]", $file) !== FALSE);
		}
		
		public function createSensor($sensorData) {
			if ($this->findSensor($sensorData['name']))
				return FALSE;
				
			return $this->writeSensorData($sensorData);
		}
		
		public function updateSensor($sensorName, $sensorData) {
			$sensorDataOld = $this->readSensor($sensorName);
			if ($sensorDataOld === FALSE)
				return FALSE;
			
			$result = array();
			
			$matches = array();
			for ($i = 0; $i < count($sensorDataOld); $i++) {
				if (preg_match("/([a-z]+)\: (.*)/", $sensorDataOld[$i], $matches) === 1) {
					if (array_key_exists($matches[1], $sensorData)) {
						$result[$matches[1]] = $sensorData[$matches[1]];
					} else {
						$result[$matches[1]] = $matches[2];
					}
				}
			}			
			$result['name'] = $sensorName;
			
			return $this->writeSensorData($result);
		}
	
	}