<?php

$test[] = array("test"=>"Blubb");
$test[] = array("test"=>"Blubb");

print_r($test);
$serial = serialize($test);

print_r(unserialize($serial));
die();

$url = "https://pirati.ca/profile/test1";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
 
$page = curl_exec($ch);
 
curl_close($ch);

if (strpos($page, '<meta name="friendika.community" content="true" />'))
	echo "Ping";

?>
