<?php
// Recommendations.php Page
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Reset the cookie timer to 1 hour (3600 seconds) every time user visits the search page
setcookie("sessionId", $_COOKIE['sessionId'], time() + 3600, "/"); // Fixed timer to 3600 seconds (1 hour)
setcookie("username", $_COOKIE['username'], time() + 3600, "/");

// Check if sessionID and username are set in the cookies
if (!isset($_COOKIE['sessionId']) || !isset($_COOKIE['username'])) {
    echo "<h1>No session found. Please log in.</h1>";
    echo "<a href='login.html'><button class='back-button'>Log in again</button></a>";
    exit();
}

// Get the username from the cookie
$username = $_COOKIE['username'];

// Create a RabbitMQ client
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

// Request for movie recommendations
$request = array();
$request['type'] = "get_recommendations";
$request['username'] = $username;
$response = $client->send_request($request);
$data = $response;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Recommendations</title>
    <style>
        /* Background styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin-top: 60px;
        }
        .header {
            background-color: #333;
            padding: 10px;
            text-align: right;
            position: fixed;
            width: 100%;
            top:0;
            left: 0;
        }
        .header a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-weight: bold;
        }
        .content-container {
            margin-top: 100px;
            width: 80%;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        .recommendations-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            text-align: left;
        }
        .recommendations-section h3 {
            text-align: center;
            color: #333;
        }
        .movie-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .movie-card:last-child {
            border-bottom: none;
        }
        .movie-details {
            flex-grow: 1;
            text-align: left;
        }
        .movie-title {
            font-weight: bold;
            color: #333;
        }
        .movie-description {
            color: #555;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<!-- Header with Navigation Links -->
<div class="header">
    <a href="login.html">Logout</a>
    <a href="profile.php">Profile</a>
    <a href="movie_profile.php">Movie Profile</a>
    <a href="recommendations.php">Recommendations</a>
</div>

<div class="content-container">
    <h1>Movie Recommendations for <?php echo htmlspecialchars($username); ?></h1>

    <!-- Recommendations Section -->
    <div class="recommendations-section">
        <h3>Recommended Movies</h3>
        <?php if (!empty($data)): ?>
            <?php foreach ($data as $movie): ?>
                <div class="movie-card">
                    <div class="movie-details">
                        <div class="movie-title"><?php echo htmlspecialchars($movie['name']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movie recommendations available.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
