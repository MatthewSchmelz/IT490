<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function process_message($msg) {
    // Process the incoming message
    echo " [x] Received ", $msg->body, "\n";

    // Here you can perform any action with the message, e.g., decode JSON and process the data.
    $data = json_decode($msg->body, true);
    print_r($data); // Just to show decoded data for now

    // Acknowledge that the message has been processed
    $msg->ack();
}

try {
    // Establish connection with RabbitMQ
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // Declare the queue from which the listener will consume
    $channel->queue_declare('tmdb_queue', false, true, false, false);

    echo " [*] Waiting for messages. To exit press CTRL+C\n";

    // Listen for incoming messages
    $channel->basic_consume('tmdb_queue', '', false, false, false, false, 'process_message');

    // Keep the script running to listen for incoming messages
    while ($channel->is_consuming()) {
        $channel->wait();
    }

    // Close connection and channel when done
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

