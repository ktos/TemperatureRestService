<?php
	/*
	 * This file is being included if user requested WNS data format for Windows Notification Service
	 * 
	 * It prepares specific for WNS HTTP headers
	 */

	date_default_timezone_set('UTC');	
	header('X-WNS-Expires: ' . date(DATE_RFC850, strtotime("+30 min", $i['lastupdated'])));
	header('Content-type: application/xml;encoding=utf-8');
	date_default_timezone_set(config('timezone'));
?>