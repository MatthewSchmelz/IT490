<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Create a RabbitMQ client
        $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
        echo "Connected to RabbitMQ successfully!<br>";
    } catch (Exception $e) {
        // Catch and display any connection error
        echo "Error connecting to RabbitMQ: " . $e->getMessage();
        exit();
    }

    // Prepare the registration request
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;

    // Send the registration request and receive a response (status and sessionID)
    $response = $client->send_request($request);

    if ($response === true) {
        header("Location: login.html");
        exit();
    } else if ($response === false) {
        echo "<h1>Registration failed. USER ALREADY REGISTERED.</h1>";
    } else {
        echo "An unexpected error occurred: " . print_r($response, true) . PHP_EOL;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin-top: 100px;
        }
        h1 {
            color: green;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
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
    </style>
</head>
<body>

<h1>Register</h1>
<form method="post" action="">
    <input type="text" name="username" placeholder="Enter Username" required>
    <input type="password" name="password" placeholder="Enter Password" required>
    <button type="submit">Register</button>
</form>

<!-- Login button -->
<button class="register-button" onclick="window.location.href='testRabbitMQClient.php';">Login</button>

</body>
</html>

