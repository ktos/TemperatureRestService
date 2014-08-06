<?php
	/*
	 * This file is being included if user requested WNS data format for Windows Notification Service
	 * 
	 * It prepares specific for WNS HTTP headers
	 */

	header('X-WNS-Expires: ' . date(DATE_RFC850, strtotime("+30 min")));
	header('Content-type: application/xml;encoding=utf-8');
?>