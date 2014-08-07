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
	 		if (file_exists("$dir/$sensorName.json") && is_file("$dir/$sensorName.json")) {			
				return "$dir/$sensorName.json";
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
	 			$j = json_decode(file_get_contents($fname), TRUE);
				return ($j === null)? FALSE : $j;
	 		} else {
				return FALSE;
			}
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
				$data = $s['data'];
				
				if (array_key_exists('datatype', $s)) {
					$datatype = $this->readSection('datatype', $s);
					
					switch ($datatype) {
						case 'float': { $data = (float)$data; break; }
						case 'int': { $data = (int)$data; break; }
						default: { $data = (string)$data; break; }			
					}
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
			$result = $this->readSensor($sensorName);
			if ($result === FALSE)
				return FALSE;

			$result['name'] = $sensorName;
			
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
				
			$file = json_encode($sensorData, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
			
			return (file_put_contents(config('sensors.data') . "/$sensorData[name].json", $file) !== FALSE);
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
			
			foreach ($sensorData as $key => $value) {
				$sensorDataOld[$key] = $value;
			}
			
			$sensorDataOld['name'] = $sensorName;
			$sensorDataOld['lastupdated'] = time();
			
			return $this->writeSensorData($sensorDataOld);
		}
	
	}