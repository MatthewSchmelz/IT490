#!usr/bin/php
<?php
require_once('vendor/autoload.php');

$client = new \GuzzleHttp\Client();
$testVar = 'I see this variable';

// Making API request to TMDB
$invokedResponse = $client->request('GET', 'https://api.themoviedb.org/3/discover/movie?include_adult=false&include_video=false&language=en-US&page=1&sort_by=popularity.desc', [
  'headers' => [
    'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI4ZmY3ZTEwZjQ3YjVhMzgxNDk2OTRmNDc5OGQ5MTc2ZCIsIm5iZiI6MTcyOTA5NTI4Mi4yNjQzMTQsInN1YiI6IjY3MGZkY2U0NmY3NzA3YWY0MGZhMzc5NiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.EM-KT-_d97BR3O1WcVEYAk51FSpoZAR_EbZAlnVAfoY',
    'accept' => 'application/json',
  ],
]);

// Decode API response to JSON format
$invokedResponse = $invokedResponse->getBody()->getContents();
$invokedResponse = json_decode($invokedResponse, true);
print_r($invokedResponse);
// Ensure response is returned as a JSON string for RabbitMQ
$invokedResponse = json_encode($invokedResponse);

// This variable is for testing purpose, printing it for debugging
//	echo $testVar;

