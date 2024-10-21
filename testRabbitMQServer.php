#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;

function doLogin($username,$password)
{
    // lookup username in database
    // check password
    return true;
    //return false if not valid
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
    case "movie_title":
      return handleSearch($request['movie1']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}
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


exit();
?>

