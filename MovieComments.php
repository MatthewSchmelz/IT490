<?php
//Rabbit Libraries
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$host = 'localhost'; 
$dbname = 'testdb'; 
$user = 'testuser'; 
$password = 'Arashi!6288'; 



//Connecting to MySQL database
//NO REMOTE CONNECTIONS!
/*
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
*/

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");


//Handle the Request from the HTML Request
//In the actual Implementation we don't need them to fill out comments, we just get it from their session.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $comment = htmlspecialchars($_POST['comment']);
    
    //Ensure that theyre not angry
    if (!empty($username) && !empty($comment)) {
        // Prepare SQL insert statement
        //THIS IS JUST A TEST, IN THE ACTUAL FILE THIS NEEDS TO BE A RABBITMQ REQUEST, Working on changing that
        //$stmt = $db->prepare("INSERT INTO comments (username, comment) VALUES (:username, :comment)");
        //$stmt->execute(['username' => $username, 'comment' => $comment]);
        $request = array();
		$request['type'] = "comment";
		$request['username'] = $username;
		$request['comment'] = "$comment;
	$client->send_request($request); //We don't care about the reply honestly

		
    } else {
        echo "Please fill in all fields.";
    }
}

//Retrieving all comments from the database, also needs to be a rabbitmq request
//This will have to all be done by mark on the DB
//$query = $db->prepare("SELECT username, comment, created_at FROM comments ORDER BY created_at DESC");
//$query->execute();
//$comments = $query->fetchAll(PDO::FETCH_ASSOC);

$request = array();
		$request['type'] = "get_comments";
$comments = $client->send_request($request);
$comments = json_decode($comments, true);

?>


//Shittest HTML youve ever seen
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .comment-box {
            margin-bottom: 20px;
        }
        .comment-form {
            margin-bottom: 40px;
        }
        .comment {
            background-color: #f4f4f4;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .comment .username {
            font-weight: bold;
        }
        .comment .created_at {
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>Leave a Comment</h1>
    
    <div class="comment-form">
        <form action="" method="POST">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            
            <label for="comment">Comment:</label><br>
            <textarea id="comment" name="comment" rows="4" required></textarea><br><br>
            
            <input type="submit" value="Submit Comment">
        </form>
    </div>

    <h2>Comments</h2>

    <div class="comment-box">
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <span class="username"><?php echo htmlspecialchars($comment['username']); ?></span> 
                    <span class="created_at">(<?php echo $comment['created_at']; ?>)</span>
                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>
    </div>

</body>
</html>

