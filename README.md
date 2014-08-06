TemperatureRestService
======================

A TemperatureRestService/1.0 was a very simple PHP pseudo-REST service which could read data from DS18B20 thermometers
attached to Raspberry Pi and present them as plain text, JSON or Windows Notification Service data.

With version 2.0, it's now RESTful service for storing, updating and sending data from different types of sensors, 
mostly temperature sensors. A POST to a specific URL will create a sensor data, or update it, and such data may 
be requested in different forms (plain text, HTML, JSON, WNS) by GET request. Application is now a lot more flexible,
allowing storage of different sensor data with better customization of possible formats supported.

## Examples ##
Sending GET HTTP request to address:

    http://example.com/
	
Will result in showing information page about a service. If you go to:

    http://example.com/sensor
	
You'll get a data associated with sensor named "sensor" (if there is one) in a format most suitable for your device (usually - HTML).

If you go to:

    http://example.com/sensor/json
	
You'll get data of a "sensor" in JSON format. Alternatively, you may use:

	http://example.com/sensor.json
	
(or sensor.txt, sensor.html, sensor.wns and so on).

## Libraries ##
This application uses composer - required PHP 5.4 or greater and two more dependencies - dispatch, micro PHP framework, avaliable at http://noodlehaus.github.io/dispatch/ and http://williamdurand.fr/Negotiation/, a standalone library implementing content negotiation.

## Configuration ##
Configuration is bloody simple, everything is now in config.ini file (sample included in repo), allowing you only to change base URL, folders for data and views and APIKEY, which should be different than the standard one.

Have fun!
-- ktos