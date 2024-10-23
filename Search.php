<?php
//Search.php Page
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Reset the cookie timer to 1 hour (3600 seconds) every time user visits the search page
setcookie("sessionId", $_COOKIE['sessionId'], time() + 3600, "/"); // Fixed timer to 3600 seconds (1 hour)
setcookie("username", $_COOKIE['username'], time() + 3600, "/");

// Check if sessionID is set in the cookie
if (!isset($_COOKIE['sessionId'])) {
    echo "<h1>No sessionID found. Please log in.</h1>";
    echo "<a href='login.html'><button class='back-button'>Log in again</button></a>";
    exit();
}

try {
    // Create a RabbitMQ client
    if(!$client){
    	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    //echo "Connected to RabbitMQ successfully!<br>";
    }
    else{
    	echo "already have client instance";
    	}
} catch (Exception $e) {
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    exit();
}

// If the form is submitted (user searches for a movie)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movie_name = trim($_POST['movie_name']); // Trim whitespace

    // Prepare the search request
    $request = array();
    $request['type'] = "search_movie";
    $request['title'] = $movie_name;

    try {
        // Send the search request and receive a response
        $response = $client->send_request($request);

        if ($response['name'] === $movie_name) {
            // Redirect to movie_profile.php with movie details
            $name = urlencode($response['name']); 
            $overview = urlencode($response['overview']);
            $poster_path = urlencode($response['poster_path']);
            $tagline = urlencode($response['tagline']);
            
            header("Location: movie_profile.php?name=$name&overview=$overview&poster_path=$poster_path&tagline=$tagline");
            exit();
        } else {
            // Movie not found in the database
            echo "<h1>Movie not found.</h1>";
        }

    } catch (Exception $e) {
        echo "Error sending search request to RabbitMQ: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Movies</title>
    <style>
        /* Background styles */
        body {
            font-family: Arial, sans-serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        /* Black blur overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Semi-transparent black */
            backdrop-filter: blur(2px); /* Blur effect */
            z-index: 0;
        }
        .header {
            background-color: #333;
            padding: 10px;
            text-align: right;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 2;
        }
        .header a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-weight: bold;
            z-index: 2;
        }
        .container {
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	    z-index: 3;
	    }
        h1 {
            color: white;
            z-index: 3;
            margin-bottom: 20px;
            position: auto;
        }
        form {
            display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
        }
        input[type="text"] {
            width: 300px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            z-index: 2;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 2;
        }
        button:hover {
            background-color: #218838;
            z-index: 2;
        }
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 2;
        }
        .back-button:hover {
            background-color: #0056b3;
            z-index: 2;
        }
    </style>
</head>
<body>

<!-- Header with Logout and Profile links -->
<div class="header">
    <a href="login.html">Logout</a>
    <a href="profile.php">Profile</a>
</div>
<div class = "container">
<h1>Search for a Movie</h1>
<br>
<!-- Search bar and button -->
<form method="post" action="Search.php">
    <input type="text" name="movie_name" placeholder="Enter movie name" required>
    <button type="submit">Search</button>
</form>
</div>

</body>
</html>
