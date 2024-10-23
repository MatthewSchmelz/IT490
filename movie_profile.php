<?php
// Movie_profile.php Page
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

$username = $_COOKIE['username'];

try {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
} catch (Exception $e) {
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    exit();
}
// Decode the URL-encoded parameters
$name = urldecode($_GET['name']);
$overview = urldecode($_GET['overview']);
$poster_path = urldecode($_GET['poster_path']);
$tagline = urldecode($_GET['tagline']);

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_COOKIE['username'])) {
        echo "User not logged in.";
    } else {
        $username = $_COOKIE['username']; // Get username from the cookie
        $comment = trim($_POST['comment']);
        
        if (!empty($comment)) {
            $request = [
                'type' => 'comment',
                'username' => $username,
                'movie_name' => $name,
                'comment' => $comment
            ];
            $response = $client->send_request($request);
            
            if ($response['status']) {
                echo "Comment successfully submitted.";
            } else {
                echo "Failed to submit comment.";
            }
        } else {
            echo "Comment cannot be empty.";
        }
    }
}

// Fetch comments
$request = [
    'type' => 'fetch_comments',
    'movie_name' => $name
];
$response = $client->send_request($request);

$comments = $response['comments'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - Movie Profile</title>
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
        .movie-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 120px;
        }
        .movie-poster img {
            width: 300px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .movie-details {
            margin-top: 20px;
            text-align: left;
            width: 70%;
        }
        h2 {
            color: green;
        }
        p.tagline {
            font-style: italic;
            color: #555;
        }
        p {
            line-height: 1.6;
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
        .comments-section {
            margin-top: 30px;
            width: 70%;
            text-align: left;
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .comment h4 {
            margin: 0;
            color: #007bff;
        }
        form {
            margin-top: 20px;
        }
        textarea {
            width: 100%;
            height: 100px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button.submit-comment {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button.submit-comment:hover {
            background-color: #218838;
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
        <h2><?php echo htmlspecialchars($name); ?></h2>
        <p class="tagline"><?php echo htmlspecialchars($tagline); ?></p>
        <p><?php echo htmlspecialchars($overview); ?></p>
    </div>
</div>

<!-- Comments Section -->
<div class="comments-section">
    <h3>Comments</h3>
    <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <h4><?php echo htmlspecialchars($comment['username']); ?> <small>(<?php echo htmlspecialchars($comment['created_at']); ?>)</small></h4>
            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
        </div>
    <?php endforeach; ?>
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
