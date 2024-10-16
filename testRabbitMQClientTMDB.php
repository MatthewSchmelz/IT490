#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$rabbitClient = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
$msg = "test message";	
}
//TMDB invocation
	include('tmdbRequest.php');
	$msg1 = $invokedResponse;
	//echo $testVar;
$request = array();
$request['type'] = "TMDB";
$request['message'] = $msg1;
$response = $rabbitClient->send_request($request);
//$response = $client->publish($request);

//echo "client received response: ".PHP_EOL;
//print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

