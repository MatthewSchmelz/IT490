#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
//require_once('testRabbitMQ2.ini');

$client = new rabbitMQClient("testRabbitMQ2.ini","testServer2");
/*
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "IT490";
}
*/

$request = array();
$request['type'] = 'movie_title';
$request['movie1'] = $title;


//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

try{
	$response = $client->send_request($request);
}finally{
unset($rabbitMQClient);
}

//echo $argv[0]." END".PHP_EOL;

