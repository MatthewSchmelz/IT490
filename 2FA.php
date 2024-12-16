<?php
// 2FA Authentication Page
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$username = $_COOKIE['username'];
try {
    // Create a RabbitMQ client
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
} catch (Exception $e) {
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    exit();
}

// Initialize variables
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $code = trim($_POST['verification_code']); // Trim whitespace

    if (strlen($code) === 6 && ctype_digit($code)) {
        // Prepare the request to verify the 2FA code
        $request = array();
        $request['type'] = "verify";
        $request['username'] = $username;
        $request['code'] = $code;

        try {
            // Send the request to RabbitMQ and get the response
            $response = $client->send_request($request);

            if ($response === true) {
                // Redirect to the next page if 2FA is successful
                header("Location: success.php");
                exit();
            } else {
                // Display error message if the code is incorrect
                $error_message = "Invalid code. Please try again.";
            }
        } catch (Exception $e) {
            $error_message = "Error verifying code: " . $e->getMessage();
        }
    } else {
        $error_message = "Please enter a valid 6-digit code.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        input[type="number"] {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        button {
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Two-Factor Authentication</h1>
    <p>Please enter the 6-digit code sent to your email or phone.</p>
    <form method="post" action="2FA.php">
        <input type="number" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" min="100000" max="999999" required>
        <button type="submit">Verify</button>
    </form>
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
</div>

</body>
</html>

