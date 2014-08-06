<?php
	/**
	 * Sensors class, a model-type class for application
	 *
	 * It is responsible for saving sensor data, in flat files,
	 * as they are most simple solution.
	 */	 	 
	class Sensors {
	 
	 	/**
		 * Finds sensor file for a sensor of specified name (or FALSE)
		 * 
		 * @param string $sensorName
		 * @return string
		 */
	 	public function findSensor($sensorName) {
	 		$dir = config('sensors.data');	 			
	 		if (file_exists("$dir/$sensorName") && is_file("$dir/$sensorName")) {			
				return "$dir/$sensorName";
			} else {
				return FALSE;
			}
	 	}
	 
	 	/**
		 * Returns whole sensor file as an array
		 * 
		 * @param string $sensorName
		 * @return array
		 */
	 	private function readSensor($sensorName) {
	 		$fname = $this->findSensor($sensorName);
	 		if ($fname !== FALSE) {
	 			return file($fname);				
	 		} else {
				return FALSE;
			}
	 	}
		
		/**
		 * Returns value for only one field from a sensor file array
		 * 
		 * @param string $section
		 * @param array $sensorData
		 * @return string
		 */
		private function readSection($section, $sensorData) {
			$matches = array();
			for ($i = 0; $i < count($sensorData); $i++) {
				if (preg_match("/$section: (.*)/", $sensorData[$i], $matches) === 1) {					
					return $matches[1];
				}
			}
			
			return FALSE;
		}
	 
	 	/**
		 * Gets sensor data (reading) associated with a sensor
		 * 
		 * @param string $sensorName
		 * @return mixed
		 */
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
		
		/**
		 * Gets additional information about sensor as a associative arrray
		 * 
		 * @param string $sensorName
		 * @return array
		 */
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
		
		/**
		 * Writes data for a sensor into file
		 * 
		 * @param array $sensorData
		 * @return bool Returns if write succeeded
		 */
		private function writeSensorData($sensorData) {
			unset($sensorData['apikey']);
				
			$file = '';
			foreach ($sensorData as $key => $value) {
				$file .= "$key: $value\n";				
			}
			
			return (file_put_contents(config('sensors.data') . "/$sensorData[name]", $file) !== FALSE);
		}
		
		/**
		 * Creates a new sensor file and saves data to it. File name will
		 * be based on $sensorData['name'] field.
		 * 
		 * @param array $sensorData
		 * @return bool
		 */
		public function createSensor($sensorData) {
			if ($this->findSensor($sensorData['name']))
				return FALSE;
				
			return $this->writeSensorData($sensorData);
		}
		
		/**
		 * Updates a sensor file with data from a new sensorData array
		 * 
		 * @param string $sensorName
		 * @param array $sensorData
		 * @return bool
		 */
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
			$result['lastupdated'] = time();
			
			return $this->writeSensorData($result);
		}
	
	}