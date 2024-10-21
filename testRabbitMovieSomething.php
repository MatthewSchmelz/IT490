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


$request['movie1'];
$response = $rabbitClient->send_request($request);
//$response = $client->publish($request);
// TODO Movie Search:
function handleSearch($title) {
    $apiKey = '13db636387987b24f85de0b1b7b2f8e2'; // Replace with your actual TMDB API key
    $baseUrl = 'https://api.themoviedb.org/3/search/movie';

    // Prepare the API request URL
    $query = urlencode($title); // Encode the movie title for URL compatibility
    $url = "{$baseUrl}?api_key={$apiKey}&query={$query}";

    // Initialize a cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string

    // Execute the cURL session and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo ' [x] cURL error: ' . curl_error($ch), "\n";
        return array("status" => false, "message" => "Error connecting to the movie database.");
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $result = json_decode($response, true);

    // Check if the API returned any results
    if (isset($result['results']) && count($result['results']) > 0) {
        // Return the first movie in the search results
        $movie = $result['results'][0];
        echo ' [x] Movie found: ', $movie['title'], "\n";

        // Construct and return movie details
        return array(
            "status" => true,
            "name" => $movie['title'],
            "overview" => $movie['overview'],
            "poster_path" => $movie['poster_path'],
            "tagline" => $movie['tagline'] ?? 'Tagline Not Available' // Handle optional tagline
        );
    } else {
        echo ' [x] Movie not found: ', $title, "\n";
        return array("status" => false, "message" => "Movie not found.");
    }
}


//echo "client received response: ".PHP_EOL;
//print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

