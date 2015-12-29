<?php
	/**
	 * Plugins class, helper class for running plugins in different scenarios	 	 
	 */	 	 
	class Plugins {
	 
	 	/**
		 * Runs plugin for defined event
		 * 
		 * @param string $event
		 * @param mixed $data Any data to be put to script
         * @param mixed $data2 Any data to be put to script
		 */
	 	public static function run($event, $data = NULL, $data2 = NULL) {
	 		if (file_exists('plugins/' . $event . '.php'))
                include 'plugins/' . $event . '.php';
	 	}

    }
?>