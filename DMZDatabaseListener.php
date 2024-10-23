<?php
//Consumer.php Page
require_once __DIR__ . '/vendor/autoload.php';
require_once 'testRabbitMQ2.ini';
require_once 'rabbitMQLib.inc';
require_once 'path.inc';
require_once 'get_host_info.inc';

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

    	$exchange = 'DMZDatabaseEx';
    	$queue = 'DMZDatabase';

    	// Declare an exchange
    	$channel->exchange_declare($exchange, 'topic', false, true, false);

    	// Declare a queue
    	$channel->queue_declare($queue, false, true, false, false);

    	// Bind the queue to the exchange
    	$channel->queue_bind($queue, $exchange);

    	// Start consuming messages from the queue
    	$channel->basic_consume($queue, '', false, false, false, false, function($msg) use ($callback, $channel) {
        	$response = call_user_func($callback, $msg);
        	$channel->basic_ack($msg->delivery_info['delivery_tag']);	 
        	$channel->wait();
    	});
   	 
    	$channel->close();
    	$connection->close();
	}
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
    	case "search_movie":
        	return handleTitle($request['title']);
	}
	return array("returnCode" => '0', 'message' => "Server received request and processed");
}

// Create server object and start processing requests
$server = new rabbitMQServer("testRabbitMQ2.ini","testServer2");
echo "testRabbitMQServer BEGIN", "\n";
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END";
?>

