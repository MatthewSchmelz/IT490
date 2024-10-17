#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "validate";
$request['username'] = "IT490";
$request['password'] = "IT490";
$request['sessionId'] = "IT490";
$response = $client->send_request($request);
//$response = $client->publish($request);

//echo "client received response: ".PHP_EOL;
//Testing if Response is true or false
if($response === true){
	echo "Response was true!".PHP_EOL;
	print_r($response);
} else {
	echo "Response was false!".PHP_EOL;
}

echo "\n\n";

echo $argv[0]." END".PHP_EOL;

