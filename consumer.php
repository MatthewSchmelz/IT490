<?php
require_once __DIR__ . '/vendor/autoload.php';  // Include RabbitMQ library
require_once 'testRabbitMQ.ini';  // Include the RabbitMQ host info file

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
        // Create connection to RabbitMQ
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

        // Declare an exchange
        $channel->exchange_declare($exchange, 'topic', false, true, false);

        // Declare a queue
        $channel->queue_declare($queue, false, true, false, false);

        // Bind the queue to the exchange
        $channel->queue_bind($queue, $exchange);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        // Start consuming messages from the queue
        $channel->basic_consume($queue, '', false, false, false, false, function($msg) use ($callback, $channel) {
            $response = call_user_func($callback, $msg);
            echo "Response: ", $response ? 'true' : 'false', "\n";

            // Create a new message to send back to RabbitMQ
            $responseMessage = new AMQPMessage(json_encode(['response' => $response]));

            // Publish the response message to the same exchange
            $channel->basic_publish($responseMessage, 'testExchange');

            // Acknowledge the message using the channel
            $channel->basic_ack($msg->delivery_info['delivery_tag']);
        });

        // Use a loop to wait for incoming messages
        while (true) {
            $channel->wait();
        }

        // Close the channel and connection (this line won't be reached unless the loop is broken)
        $channel->close();
        $connection->close();
    }
}

function requestProcessor($msg) {
    echo " [x] Received ", $msg->body, "\n";

    // Decode the JSON message
    $data = json_decode($msg->body, true);
    if (isset($data['username']) && isset($data['password'])) {
        $username = $data['username']; // Get username
        $password = $data['password']; // Get password
        return handleLogin($username, $password); // Pass username and password directly
    }
    return false; // Return false if the message format is not correct
}

function handleLogin($username, $password) {
    // Create a new MySQL connection
    $mysqli = new mysqli("localhost", "IT490", "IT490", "imdb_database");

    // Check for connection errors
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Escape the input data to prevent SQL injection
    $username = $mysqli->real_escape_string($username);
    $password = $mysqli->real_escape_string($password);

    // Check if the user exists in the users table
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        echo ' [x] Processing login for ', $username, "\n";
        return true; // Successful login
    } else {
        echo ' [x] Login failed for ', $username, "\n";
        return false; // Failed login
    }

    // Close the result set and database connection
    $result->free();
    $mysqli->close();
}

// Create server object and start processing requests
$server = new testRabbitMQServer("testRabbitMQ.ini", "testServer");
$server->process_requests('requestProcessor');
?>

