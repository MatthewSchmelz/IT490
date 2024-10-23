<?php
// Profile.php Page
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

setcookie("sessionId", $_COOKIE['sessionId'], time() + 3600, "/"); 
setcookie("username", $_COOKIE['username'], time() + 3600, "/");
// Check if sessionID and username are set in the cookies
if (!isset($_COOKIE['sessionId'])) {
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
    echo "Connected to RabbitMQ successfully!<br>";
    }
    else{
    	echo "already have client instance";
    	}
} catch (Exception $e) {
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    exit();
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_movie'])) {
    $movie_to_delete = $_POST['delete_movie'];
    
    // Prepare the request to delete the movie from the watchlist
    $request = array();
    $request['type'] = "delete_watchlist";
    $request['watchlist_table'] = $username . "_watchlist";
    $request['movie_name'] = $movie_to_delete;
    
    // Send the delete request to RabbitMQ
    $response = $client->send_request($request);
    
    // Redirect back to profile.php
    //header("Location: profile.php");
    //exit();
}

// Request for the watchlist table
$request = array();
$request['type'] = "get_watchlist";
$request['watchlist_table'] = $username . "_watchlist";
$response1 = $client->send_request($request);
$watchlist_data = $response1;
//echo $response1;

// Request for the rating table
$request = array();
$request['type'] = "get_ratings";
$request['rating_table'] = $username . "_rating";
$response2 = $client->send_request($request);
$rating_data = $response2;
//echo $response2;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($username); ?>'s Profile</title>
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
        .table-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            text-align: left;
        }
        .table-section h3 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .delete-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<!-- Header with Logout link -->
<div class="header">
    <a href="login.html">Logout</a>
    <a href="Search.php">Back to Search</a>
</div>

<div class="content-container">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

    <!-- Watchlist Section -->
    <div class="table-section">
        <h3>Your Watchlist</h3>
        <?php if (!empty($watchlist_data)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Movie Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watchlist_data as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Movies']); ?></td>
                            <td>
                                <form method="post" action="profile.php" style="display:inline;">
                                    <input type="hidden" name="delete_movie" value="<?php echo htmlspecialchars($item['Movies']); ?>">
                                    <button type="submit" class="delete-button">Remove From Watchlist</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No movies in your watchlist.</p>
        <?php endif; ?>
    </div>

    <!-- Ratings Section -->
    <div class="table-section">
        <h3>Your Ratings</h3>
        <?php if (!empty($rating_data)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Movie Name</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rating_data as $rating): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rating['Movies']); ?></td>
                            <td><?php echo htmlspecialchars($rating['Rating']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No ratings available.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

