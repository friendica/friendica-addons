<?php

/*

Jappix - An open social platform
This is a PHP BOSH proxy

-------------------------------------------------

This file is dual-licensed under the MIT license (see MIT.txt) and the AGPL license (see jappix/COPYING).
Authors: Vanaryon, Leberwurscht

*/

// PHP base
define('JAPPIX_BASE', './jappix');

// Get the configuration
require_once('./jappix/php/functions.php');
require_once('./jappix/php/read-main.php');
require_once('./jappix/php/read-hosts.php');

// Optimize the page rendering
hideErrors();
compressThis();

// Not allowed?
if(!BOSHProxy()) {
	header('Status: 403 Forbidden', true, 403);
	exit('HTTP/1.1 403 Forbidden');
}

// custom BOSH host
$HOST_BOSH = HOST_BOSH;
if(isset($_GET['host_bosh']) && $_GET['host_bosh']) {
	$host_bosh = $_GET['host_bosh'];
	if (substr($host_bosh, 0, 7)==="http://" || substr($host_bosh, 0, 8)==="https://") {
		$HOST_BOSH = $host_bosh;
	}
}

// OPTIONS method?
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	// CORS headers
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type');
	header('Access-Control-Max-Age: 31536000');
	
	exit;
}

// Read POST content
$data = file_get_contents('php://input');

// POST method?
if($data) {
	// CORS headers
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Content-Type');
	
	$method = 'POST';
}

// GET method?
else if(isset($_GET['data']) && $_GET['data'] && isset($_GET['callback']) && $_GET['callback']) {
	$method = 'GET';
	$data = $_GET['data'];
	$callback = $_GET['callback'];
}

// Invalid method?
else {
	header('Status: 400 Bad Request', true, 400);
	exit('HTTP/1.1 400 Bad Request');
}

// HTTP headers
$headers = array('User-Agent: Jappix (BOSH PHP Proxy)', 'Connection: keep-alive', 'Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($data));

// CURL is better if available
if(function_exists('curl_init'))
	$use_curl = true;
else
	$use_curl = false;

// CURL caused problems for me
$use_curl = false;

// CURL stream functions
if($use_curl) {
	// Initialize CURL
	$connection = curl_init($HOST_BOSH);
	
	// Set the CURL settings
	curl_setopt($connection, CURLOPT_HEADER, 0);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $data);
	curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_VERBOSE, 0);
	curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($connection, CURLOPT_TIMEOUT, 30);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	
	// Get the CURL output
	$output = curl_exec($connection);
}

// Built-in stream functions
else {
	// HTTP parameters
	$parameters = array('http' => array(
					'method' => 'POST',
					'content' => $data
				      )
		      );

	$parameters['http']['header'] = $headers;

	// Change default timeout
	ini_set('default_socket_timeout', 30);

	// Create the connection
	$stream = @stream_context_create($parameters);
	$connection = @fopen($HOST_BOSH, 'rb', false, $stream);

	// Failed to connect!
	if($connection == false) {
		header('Status: 502 Proxy Error', true, 502);
		exit('HTTP/1.1 502 Proxy Error');
	}

	// Allow stream blocking to handle incoming BOSH data
	@stream_set_blocking($connection, true);

	// Get the output content
	$output = @stream_get_contents($connection);
}

// Cache headers
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// POST output
if($method == 'POST') {
	// XML header
	header('Content-Type: text/xml; charset=utf-8');
	
	if(!$output)
		echo('<body xmlns=\'http://jabber.org/protocol/httpbind\' type=\'terminate\'/>');
	else
		echo($output);
}

// GET output
if($method == 'GET') {
	// JSON header
	header('Content-type: application/json');
	
	// Encode output to JSON
	$json_output = json_encode($output);
	
	if(($output == false) || ($output == '') || ($json_output == 'null'))
		echo($callback.'({"reply":"<body xmlns=\'http:\/\/jabber.org\/protocol\/httpbind\' type=\'terminate\'\/>"});');
	else
		echo($callback.'({"reply":'.$json_output.'});');
}

// Close the connection
if($use_curl)
	curl_close($connection);
else
	@fclose($connection);

?>
