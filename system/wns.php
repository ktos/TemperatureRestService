<?php	
	header('X-WNS-Expires: ' . date(DATE_RFC850, strtotime("+30 min")));
	header('Content-type: application/xml;encoding=utf-8');
?>