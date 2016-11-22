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
		 * Gets all sensor information
		 * 
		 * @param string $sensorName
		 * @return array
		 */
		public function getSensorData($sensorName) {
            Plugins::run('pre-getsensordata', $sensorName);
            
			$s = $this->readSensor($sensorName);
			if ($s !== FALSE) {
				// if there is "pull" parameter specified, run pull logic
				if (array_key_exists('pull', $s)) {					
					if (file_exists('plugins/pullable.php'))
                        include 'plugins/pullable.php';
				}
				
				// if there is datatype specified, convert data to proper data type
				if (array_key_exists('datatype', $s)) {					
					switch ($s['datatype']) {
						case 'float': { $s['data'] = (float)$s['data']; break; }
						case 'int': { $s['data'] = (int)$s['data']; break; }
						default: { $s['data'] = (string)$s['data']; break; }			
					}
				}
				
                Plugins::run('post-getsensordata', $s);
				return $s;
			} else {
                Plugins::run('post-getsensordata-error');
				return FALSE;
			}
		}	
		
		/**
		 * Writes data for a sensor into file
		 * 
		 * @param array $sensorData
		 * @return bool Returns if write succeeded
		 */
		private function writeSensorData($sensorData) {
            Plugins::run('pre-writesensordata', $sensorData);
			unset($sensorData['apikey']);
				
			$file = json_encode($sensorData, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
			
            Plugins::run('post-writesensordata', $sensorData);
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
            Plugins::run('pre-createsensor', $sensorData);
			if ($this->findSensor($sensorData['name']))
				return FALSE;
			
            Plugins::run('post-createsensor', $sensorData);
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
            Plugins::run('pre-updatesensor', $sensorName, $sensorData);
			$sensorDataOld = $this->readSensor($sensorName);
			if ($sensorDataOld === FALSE)
				return FALSE;
			
			$result = array();
			
			foreach ($sensorData as $key => $value) {
				$sensorDataOld[$key] = $value;
			}
			
			$sensorDataOld['name'] = $sensorName;
			$sensorDataOld['lastupdated'] = time();
			
            Plugins::run('post-updatesensor', $sensorName, $sensorData);
			return $this->writeSensorData($sensorDataOld);
		}
	
	}