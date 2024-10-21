<?php
require_once __DIR__ . '/vendor/autoload.php';  // RabbitMQ PHP library

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = '172.24.71.42';  // IP of the RabbitMQ server
$port = 5672;  // Default RabbitMQ port
$username = 'guest';  // Replace with your RabbitMQ username
$password = 'guest';  // Replace with your RabbitMQ password
$vhost = '/';  // Default vhost or replace if different
$queue_name = 'your_queue_name';  // Replace with the name of your queue

// Create a connection to the RabbitMQ server
try {
    $connection = new AMQPStreamConnection($host, $port, $username, $password, $vhost);
    $channel = $connection->channel();

    // Declare the queue (ensure the queue is created, if it doesn't exist already)
    $channel->queue_declare($queue_name, false, true, false, false);

    echo " [*] Waiting for messages from $host. To exit, press CTRL+C\n";

    // Callback function to process the message
    $callback = function ($msg) {
        echo " [x] Received message: ", $msg->body, "\n";

        // You can decode JSON or process the message here as needed
        $data = json_decode($msg->body, true);
        print_r($data);

        // Acknowledge the message once processed
        $msg->ack();
    };

    // Start consuming messages
    $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

    // Keep the script running and waiting for incoming messages
    while ($channel->is_consuming()) {
        $channel->wait();
    }

    // Close the channel and connection when done
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
