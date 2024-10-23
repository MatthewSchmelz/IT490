<?php
//Consumer.php Page
require_once __DIR__ . '/vendor/autoload.php';
require_once 'testRabbitMQ.ini';
require_once 'rabbitMQLib.inc';
require_once 'testRabbitMQ2.ini';
require_once 'path.inc';
require_once 'get_host_info.inc';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class testRabbitMQServer {
	private $host;
	private $port;
	private $username;
	private $password;
	private $vhost;
	private $server_name;

	public function __construct($config_file, $server_name) {
    	$config = parse_ini_file($config_file, true);
    	$this->host = $config[$server_name]['BROKER_HOST'];
    	$this->port = $config[$server_name]['BROKER_PORT'];
    	$this->username = $config[$server_name]['USER'];
    	$this->password = $config[$server_name]['PASSWORD'];
    	$this->vhost = $config[$server_name]['VHOST'];
    	$this->server_name = $server_name;
	}

	public function process_requests($callback) {
    	$connection = new AMQPStreamConnection(
        	$this->host,
        	$this->port,
        	$this->username,
        	$this->password,
        	$this->vhost
    	);

    	$channel = $connection->channel();

    	$exchange = 'testExchange';
    	$queue = 'testQueue';

    	$channel->exchange_declare($exchange, 'topic', false, true, false);
    	$channel->queue_declare($queue, false, true, false, false);
    	$channel->queue_bind($queue, $exchange);

    	$channel->basic_consume($queue, '', false, false, false, false, function($msg) use ($callback, $channel) {
        	$response = call_user_func($callback, $msg);
        	$channel->basic_ack($msg->delivery_info['delivery_tag']);	 
        	$channel->wait();
    	});
   	 
    	$channel->close();
    	$connection->close();
	}
}

function doValidate($sessionId) {
    $mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

    if ($mysqli->connect_error) {
        echo ' [x] Connection failed for validation', "\n";
        die("Connection failed: " . $mysqli->connect_error);
    }

    $username = $_COOKIE['username'];

    $query = "SELECT time FROM users WHERE sessionId = '$sessionId'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dbTime = $row['time'];
        $currentTime = time();

        echo ' [x] Validation TimeStamp: ', $currentTime , "\n";

        if (($currentTime - $dbTime) >= 36000) {
            echo ' [x] Session expired (more than 1 hour since last validation)', "\n";
            return false;
        }

        $updateQuery = "UPDATE users SET time = UNIX_TIMESTAMP() WHERE sessionId = '$sessionId'";
        $mysqli->query($updateQuery);

        echo ' [x] Processing Validation', "\n";
        echo ' [x] Validation Worked: ', $sessionId, "\n";
        return true;
    } else {
        echo ' [x] Validation failed: Invalid sessionId', "\n";
        return false;
    }

    $result->free();
    $mysqli->close();
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
			return handleLogin($request['username'], $request['password']);
	    	case "validate":
			return doValidate($request['sessionId']);
	    	case "register":
			return handlereg($request['username'], $request['password'], $request['rating_table'], $request['watchlist_table']);
	    	case "search_movie":
			return handleTitle($request['title']);
		case "comment":
		    return handleComment($request['username'], $request['movie_name'], $request['comment']);
		case "fetch_comments":
		    return fetchComments($request['movie_name']);
	}
	return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$sessionId = null;
echo ' [x] Session ID is set to null: ', $sessionId, "\n";

function handlereg($username, $password, $rating_table, $watchlist_table) {
	$mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

	if ($mysqli->connect_error) {
    		echo ' [x] Connection failed for login', "\n";
    		die("Connection failed: " . $mysqli->connect_error);
	}
    
	$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
	$result2 = $mysqli->query($query);
    
	if ($result2->num_rows > 0) {
    		echo ' [x] User Failed, user already in system: ', $username, "\n";
    		return false;
	} else {
    		$query = "INSERT INTO users (username, password) VALUES ('$username' , '$password')";
    		$result = $mysqli->query($query);
    		echo ' [x] User created with username: ', $username, "\n";

    		$query1 = "CREATE TABLE `$rating_table` (Movies VARCHAR(255), Rating VARCHAR(255))";
    		if ($mysqli->query($query1) === TRUE) {
        	echo " [x] Rating table created successfully: $rating_table\n";
    	} else {
        	echo ' [x] Error creating rating table: ', $mysqli->error, "\n";
    	}

    	$query2 = "CREATE TABLE `$watchlist_table` (Movies VARCHAR(255))";
    	if ($mysqli->query($query2) === TRUE) {
        	echo " [x] Watchlist table created successfully: $watchlist_table\n";
    	} else {
        	echo ' [x] Error creating watchlist table: ', $mysqli->error, "\n";
    	}
    	return true;
	}
    
	$result2->free();
	$mysqli->close();
}

function handleLogin($username, $password) {
	$mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

	if ($mysqli->connect_error) {
    		echo ' [x] Connection failed for login',"\n";
    		die("Connection failed: " . $mysqli->connect_error);
	}

	$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
	$result = $mysqli->query($query);
    
	$updateQuery = "UPDATE users SET time = UNIX_TIMESTAMP() WHERE username = '$username'";
	echo ' [x] Updating TimeStamp: ', time() , "\n";
    
	if ($result->num_rows > 0) {
	    	echo ' [x] Processing login for ', $username, "\n";
		$sessionId = bin2hex(random_bytes(16));
		
		setcookie("username", $username, time() + 3600, "/");
		
	    	$updateQuery = "UPDATE users SET sessionId = '$sessionId' WHERE username = '$username'";
	    	echo ' [x] Updated session table ', "\n";
	    	$mysqli->query($updateQuery);
	   	 
	    	$request = array();
	    	$request['status'] = true;
	    	$request['sessionId'] = $sessionId;

	    	echo ' [x] Session created for ', $username, "\n";
	    	echo ' [x] Session ID is set to ', $sessionId, "\n";
	    	return $request;
	} else {
    		echo ' [x] Login failed for ', $username, "\n";
    		return false;
	}
    
	$result->free();
	$mysqli->close();
}

function handleTitle($title) {
    $mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

    if ($mysqli->connect_error) {
    	echo ' [x] Connection failed for login', "\n";
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    $stmt = $mysqli->prepare("SELECT * FROM movies WHERE title = ?");
    if (!$stmt) {
        echo ' [x] Prepare failed: ', $mysqli->error, "\n";
        return null;
    }

    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result3 = $stmt->get_result();

    if ($result3 && $result3->num_rows > 0) {
    	echo ' [x] Movie Already in Table: ', $title, "\n";
        $response = "";

        $row = $result3->fetch_assoc();
        if ($row) {
            $movieResult = array(
                'status' => true,
                'name' => $row['title'],
                'overview' => $row['overview'],
                'poster_path' => $row['poster_path'],
                'tagline' => $row['tagline'] ?? 'Tagline Not Available'
            );
            echo ' [x] Response: ', print_r($movieResult, true), "\n";
            $response = $movieResult;
            return $response;
        } else {
            echo ' [x] Error fetching movie data: ', $mysqli->error, "\n";
            return null;
        }
    } else {
        $response = [];
        include 'testRabbitMQClient2.php';

        if (!empty($response) && isset($response['name'], $response['overview'], $response['poster_path'], $response['tagline'])) {
            $insertStmt = $mysqli->prepare("INSERT INTO movies (title, overview, poster_path, tagline, name) VALUES (?, ?, ?, ?, ?)");
            if (!$insertStmt) {
                echo ' [x] Insert prepare failed: ', $mysqli->error, "\n";
                return null;
            }

            $insertStmt->bind_param("sssss", $title, $response['overview'], $response['poster_path'], $response['tagline'], $response['name']);
    		if ($insertStmt->execute()) {
     	   echo ' [x] Movie successfully added to the table: ', $response['name'], "\n";
    	} else {
        echo ' [x] Insert failed: ', $mysqli->error, "\n";
    	}

            $insertStmt->close();
        } else {
            echo ' [x] No movie data available to add to the table.', "\n";
        }

        echo ' [x] Movie Not Found: ', $title, "\n";
        echo ' [x] Response: ', print_r($response, true), "\n";
        return $response;
    }
    $stmt->close();
    $mysqli->close();
}

//TODO
function handleComment($username, $movie_name, $comment) {
    $mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");
    if ($mysqli->connect_error) {
        return ['status' => false, 'message' => 'Database connection failed'];
    }

    $stmt = $mysqli->prepare("INSERT INTO comments (movie_name, username, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $movie_name, $username, $comment);
    
    if ($stmt->execute()) {
    	echo ' [x] Comment Successfully Created: ', print_r($status, true), "\n";
        $status = true;
    } else {
    	echo ' [x] Comment Failed to Create: ', "\n";
        $status = false;
    }
    $stmt->close();
    $mysqli->close();
    return ['status' => $status];
    //return NULL;
}

//TODO
function fetchComments($movie_name) {
    $mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

    if ($mysqli->connect_error) {
        return ['status' => false, 'message' => 'Database connection failed'];
    }

    $stmt = $mysqli->prepare("SELECT username, comment, created_at FROM comments WHERE movie_name = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $movie_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    //$data = [];
    //$data = $comments;

    $stmt->close();
    $mysqli->close();
    echo ' [x] Fetch: ', print_r($status, true), print_r($comments, true), "\n";
    return ['status' => true, 'comments' => $comments];
    //return $data;
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
echo "testRabbitMQServer BEGIN", "\n";
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END";
?>



