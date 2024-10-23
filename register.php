<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

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
        // Catch and display any connection error
        echo "Error connecting to RabbitMQ: " . $e->getMessage();
        exit();
    }

    // Prepare the registration request
    $request = array();
    $request['type'] = "register";
    $request['username'] = $username;
    $request['password'] = $password;
    $request['userEmail'] = $email;
    $request['rating_table'] = $username . "_rating";
    $request['watchlist_table'] = $username . "_watchlist";
    

    // Send the registration request and receive a response (status and sessionId)
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
        /* Background styles */
        body {
            font-family: Arial, sans-serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            margin-top: 0;
            padding: 0;
            position: center;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Black blur overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Semi-transparent black */
            backdrop-filter: blur(2px); /* Blur effect */
            z-index: 1;
        }
        
        /* Content styling */
        .content {
            position: relative;
            z-index: 2;
            color: white;
        }

        h1 {
            color: white;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"], input[type="password"], input[type = "email"]{
            width: 70%;
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

<div class="content">
    <h1>Create a MoviCritics Account</h1>
    <form method="post" action="register.php">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <br>
        <button type="submit">Register</button>
    </form>
    <br>
    <!-- Login button -->
    <button class="register-button" onclick="window.location.href='login.html';">Login</button>
</div>

</body>
</html>

