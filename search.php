<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Reset the cookie timer to 1 hour (3600 seconds) every time user visits the search page
setcookie("sessionId", $_COOKIE['sessionId'], time() + 360 , "/");
// Check if sessionID is set in the cookie
if (!isset($_COOKIE['sessionId'])) {
    echo "<h1>No sessionID found. Please log in.</h1>";
    echo "<a href='login.html'><button class='back-button'>Log in again</button></a>";
    exit();
}



try {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

} catch (Exception $e) {
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    exit();
}

// If the form is submitted (user searches for a movie)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movie_name = $_POST['movie_name'];

    // Prepare the search request
    $request = array();
    $request['type'] = "search_movie";
    $request['title'] = $movie_name;

    try {
        // Send the search request and receive a response
        $response = $client->send_request($request);

        if ($response['status1'] === true) {
            // Movie found in the database
            echo "<h1>Yahoo! Found the movie in the database.</h1>";
        } else if ($response['status2'] === false){
            // Movie not found in the database
            echo "<h1>Didn't find the movie in the database.</h1>";
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin-top: 100px;
        }
        .header {
            background-color: #333;
            padding: 10px;
            text-align: right;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
        }
        .header a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-weight: bold;
        }
        h1 {
            color: green;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"] {
            width: 300px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<!-- Header with Logout and Profile links -->
<div class="header">
    <a href="login.html">Logout</a>
    <a href="profile.php">Profile</a>
</div>

<h1>Search for a Movie</h1>

<!-- Search bar and button -->
<form method="post" action="search.php">
    <input type="text" name="movie_name" placeholder="Enter movie name" required>
    <button type="submit">Search</button>
</form>



</body>
</html>
