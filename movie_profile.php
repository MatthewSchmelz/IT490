<?php
// Movie_profile.php Page
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Reset the cookie timer to 1 hour (3600 seconds) every time user visits the search page
setcookie("sessionId", $_COOKIE['sessionId'], time() + 3600, "/");
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
    echo "Connected to RabbitMQ successfully!<br>";
    }
    else{
    	echo "already have client instance";
    	}
} catch (Exception $e) {
	echo "Error connecting to RabbitMQ: " . $e->getMessage();
	exit();
}

// Decode the URL-encoded parameters
$name = urldecode($_GET['name']);
$overview = urldecode($_GET['overview']);
$poster_path = urldecode($_GET['poster_path']);
$tagline = urldecode($_GET['tagline']);

$username1 = $_COOKIE['username'];

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
	$comment = trim($_POST['comment']);

	if (!empty($comment)) {
    	// Prepare the comment array
    	$request = array();
    	$request['type'] = "comment";
    	$request['username'] = $username1;
    	$request['movie_name'] = $name;
    	$request['comment'] = $comment;
    	$response = $client->send_request($request);
    	//header("Location: movie_profile.php");
    	//exit();
    	if ($response['status']) {
            	echo "Comment successfully submitted.";
        	} else {
            	echo "Failed to submit comment.";
        	}
    	} else {
        	echo "Comment cannot be empty.";
    	}
	}

// Handle rating submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_rating'])) {
	$movie_rating = $_POST['movie_rating'];
	// Prepare the rating request
	$request = array();
	$request['type'] = "rating";
	$request['rating_table'] = $username1 . "_rating";
	$request['movie_name'] = $name;
	$request['movie_rating'] = $movie_rating;
	$response = $client->send_request($request);
    
	if ($response === true) {
    	//header("Location: movie_profile.php");
    	//exit();
	} else if ($response === false) {
    	header("Location: rating_done.php");
    	exit();
	}
}

// Handle watchlist addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_watchlist'])) {
	$watchlist = $_POST['add_to_watchlist'];
	// Prepare the watchlist request
	$request = array();
	$request['type'] = "watchlist";
	$request['watchlist_table'] = $username1 . "_watchlist";
	$request['movie_name'] = $name;
	$response = $client->send_request($request);
	if ($response === true) {
    	//header("Location: movie_profile.php");
    	//exit();
	} else if ($response === false) {
    	header("Location: watchlist_done.php");
    	exit();
	}
}



// Fetch comments
$request = array();
$request['type'] = "fetch_comments";
$request['movie_name'] = $name;
$response = $client->send_request($request);
$comments_data = $response;
//echo $comments_data;
if ($comments_data === null) {
	$comments_data = []; // Handle the error gracefully
	echo "<p>Error fetching comments or invalid response format.</p>";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($name); ?> - Movie Profile</title>
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
    	.movie-container {
        	display: flex;
        	flex-direction: column;
        	align-items: center;
        	margin-top: 100px;
    	}
    	.movie-poster img {
        	width: 300px;
        	height: auto;
        	border-radius: 10px;
        	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    	}
    	.movie-details {
        	margin-top: 20px;
        	text-align: center;
        	width: 70%;
    	}
    	h1 {
        	color: green;
    	}
    	p.tagline {
        	font-style: italic;
        	color: #555;
    	}
    	p {
        	line-height: 1.6;
    	}
    	button.submit-comment{
    	margin-top: 20px;
        	padding: 10px 20px;
        	background-color: green;
        	color: white;
        	border: none;
        	border-radius: 5px;
        	cursor: pointer;}
   	 
    	.back-button, .submit-button, .watchlist-button {
        	margin-top: 20px;
        	padding: 10px 20px;
        	background-color: #007bff;
        	color: white;
        	border: none;
        	border-radius: 5px;
        	cursor: pointer;
        	display: inline-block;
    	}
    	.back-button:hover, .submit-button:hover, .watchlist-button:hover, .submit-comment:hover {
        	background-color: #0056b3;
    	}
    	form {
        	margin-top: 20px;
        	text-align: left;
        	display: inline-block;
        	width: 80%;
        	max-width: 600px;
    	}
    	textarea {
        	width: 100%;
        	height: 100px;
        	margin-top: 10px;
        	padding: 10px;
        	border: 1px solid #ddd;
        	border-radius: 5px;
    	}
    	.comments-section {
        	background-color: #fff;
        	padding: 20px;
        	border-radius: 10px;
        	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        	margin-top: 20px;
        	display: inline-block;
        	width: 80%;
        	max-width: 600px;
    	}
    	.comment-card {
        	display: flex;
        	align-items: flex-start;
        	margin-bottom: 15px;
        	padding: 10px;
        	border-bottom: 1px solid #ddd;
    	}
    	.comment-card:last-child {
        	border-bottom: none;
    	}
    	.rating-form {
        	margin-top: 30px;
    	}
    	.rating-input {
        	width: 100px;
        	padding: 10px;
        	margin: 10px 0;
        	border: 1px solid #ddd;
        	border-radius: 5px;
    	}
    	/* Additional styles for aligning comment time */
	.comment-time {
	    color: #999;
	    font-size: 0.9em;
	    margin-right: auto;
	    text-align: right;
	}

	</style>
</head>
<body>

<!-- Header with Logout and Profile links -->
<div class="header">
	<a href="login.html">Logout</a>
	<a href="profile.php">Profile</a>
</div>

<div class="movie-container">
	<div class="movie-poster">
    	<img src="https://image.tmdb.org/t/p/w500/<?php echo htmlspecialchars($poster_path); ?>" alt="Poster for <?php echo htmlspecialchars($name); ?>">
	</div>
    
	<div class="movie-details">
    	<h1><?php echo htmlspecialchars($name); ?></h1>
    	<p class="tagline"><?php echo htmlspecialchars($tagline); ?></p>
    	<h3>Description</h3>
    	<p><?php echo htmlspecialchars($overview); ?></p>
	</div>
</div>

<!-- Rating Form -->
<div class="rating-form">
	<form method="post" action="movie_profile.php?name=<?php echo urlencode($name); ?>&overview=<?php echo urlencode($overview); ?>&poster_path=<?php echo urlencode($poster_path); ?>&tagline=<?php echo urlencode($tagline); ?>">
    	<input type="number" step="0.1" max="5" min="0" name="movie_rating" class="rating-input" placeholder="Rate (0-5)" required>
    	<button type="submit" class="submit-button">Rate This Movie</button>
	</form>
</div>

<!-- Watchlist Button -->
<form action="movie_profile.php?name=<?php echo urlencode($name); ?>&overview=<?php echo urlencode($overview); ?>&poster_path=<?php echo urlencode($poster_path); ?>&tagline=<?php echo urlencode($tagline); ?>" method="post">
	<input type="hidden" name="add_to_watchlist" value="1">
	<button type="submit" class="watchlist-button">Add to Watchlist</button>
</form>

<!-- Comments Section -->
<div class="comments-section">
	<h3>Comments</h3>
	<?php if (!empty($comments_data)): ?>
    	<?php foreach ($comments_data as $comment): ?>
        	<div class="comment-card">
            	<div class="comment-avatar"></div>
            	<div class="comment-content">
                	<div>
                    	<span class="comment-username"><?php echo htmlspecialchars($comment['username']); ?></span>
                    	<span class="comment-time"><?php echo htmlspecialchars($comment['created_at']); ?></span>
                	</div>
                	<br>
                	<div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
            	</div>
        	</div>
    	<?php endforeach; ?>
	<?php else: ?>
    	<p>No comments available for this movie.</p>
	<?php endif; ?>
</div>

<!-- Comment Form -->
<form action="movie_profile.php?name=<?php echo urlencode($name); ?>&overview=<?php echo urlencode($overview); ?>&poster_path=<?php echo urlencode($poster_path); ?>&tagline=<?php echo urlencode($tagline); ?>" method="post">
	<h3>Add a Comment</h3>
	<textarea name="comment" placeholder="Write your comment here..." required></textarea>
	<button type="submit" class="submit-comment">Submit Comment</button>
</form>

<!-- Back button to return to the search page -->
<form action="Search.php" method="get">
	<button type="submit" class="back-button">Back to Search</button>
</form>

</body>
</html>


