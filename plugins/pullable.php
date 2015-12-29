<?php
$int = config('sensors.pull.interval');
					$lastup = array_key_exists('lastupdated', $s)? (int)$s['lastupdated'] : time();
					
					if (($int !== null) && (time() - $lastup > $int)) {						
						if (filter_var($s['pull'], FILTER_VALIDATE_URL) !== FALSE) {
							
							$curl = curl_init();
							curl_setopt_array($curl, array(
							    CURLOPT_TIMEOUT => 10,
							    CURLOPT_URL => $s['pull'],
							    CURLOPT_USERAGENT => TEMPERATURERESTSERVICE,
							    CURLOPT_RETURNTRANSFER => 1
							));
							$x = curl_exec($curl);
							curl_close($curl);
						} else {							
							// running whatever is in "pull" as shell command, if is this allowed in config
							if ((bool)config('sensors.pull.allowcmd') === TRUE)								
								shell_exec($s['pull']);
						}
						
						// if was "pull", update sensor data from file, but first wait
						// a bit
						sleep(config('sensors.pull.sleepafter') === null? 1 : config('sensors.pull.sleepafter'));
						$s = $this->readSensor($sensorName);
							if ($s === FALSE)
								return FALSE;	
					}
                    ?>