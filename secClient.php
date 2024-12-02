<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

try {
<<<<<<< HEAD
$client =null;
	// Create a RabbitMQ client
	if (!$client) {
    	$client = new rabbitMQClient("testRabbitMQ3.ini", "testServer");
    	echo "Connected to RabbitMQ successfully!<br> \n";
	} else {
    	echo "Already have client instance.<br>";
	}
} catch (Exception $e) {
	// Catch and display any connection error
	echo "Error connecting to RabbitMQ: " . $e->getMessage();
	header("Location: rabbitmq_error.php");
	exit(); // Stop execution if there's an error
}
/*
$remoteUser = "qadeer"; //sending it to qadeer database
$remoteHost = "172.24.1.2";
$remotePath = "/home/qadeer/Downloads";
$localDirectory = "/home/qadeer/git/IT490-Project";
$password = "Pakistan1200"; // Predefined password
$counterFile = "/tmp/rsync_counter.txt";
*/
=======
    // Create a RabbitMQ client
    if (!$client) {
        $client = new rabbitMQClient("testRabbitMQ3.ini", "testServer");
        echo "Connected to RabbitMQ successfully!<br> \n";
    } else {
        echo "Already have client instance.<br>";
    }
} catch (Exception $e) {
    // Catch and display any connection error
    echo "Error connecting to RabbitMQ: " . $e->getMessage();
    header("Location: rabbitmq_error.php");
    exit(); // Stop execution if there's an error
}

>>>>>>> bab08e1de733fd3925b2d8ca0b13ae57edbb2eb0
// Configuration
$remoteUser = "markcgv";
$remoteHost = "172.24.0.1";
$remotePath = "/home/markcgv/Downloads";
$localDirectory = "/home/qadeer/git/IT490-Project";
$password = "Qwe321qaZ~"; // Predefined password
$counterFile = "/tmp/rsync_counter.txt";

<<<<<<< HEAD
$counterFile = "/tmp/rsync_counter.txt";

if (file_exists($counterFile)) {
    // Read and increment the counter
    $counter = (int)file_get_contents($counterFile);
    $counter++;
} else {
    // Initialize the counter if the file doesn't exist
    $counter = 1;
}

// Write the updated counter back to the file
if (file_put_contents($counterFile, $counter) === false) {
    echo "Error: Unable to write to $counterFile<br>";
} else {
    echo "Counter updated successfully to $counter<br>";
}



// Rename directory
$tempDirectoryName = "{$localDirectory}{$counter}";
if (rename($localDirectory, $tempDirectoryName)) {
	echo "Renamed directory to $tempDirectoryName<br>";

	// Prepare the rsync command
	$rsyncCommand = "rsync -avz --exclude='.git/' --exclude='.ini/' $tempDirectoryName $remoteUser@$remoteHost:$remotePath";

	// Execute the rsync command using expect
	$expectScriptPath = "/tmp/rsync_with_password.exp";
	$expectScript = <<<EOD
=======
if (file_exists($counterFile)) {
    $counter = (int)file_get_contents($counterFile) + 1; 
    file_put_contents($counterFile, $counter);
} else {
    $counter = 1; 
}

// Rename directory
$tempDirectoryName = "{$localDirectory}{$counter}"; 
if (rename($localDirectory, $tempDirectoryName)) {
    echo "Renamed directory to $tempDirectoryName<br>";

    // Prepare the rsync command
    $rsyncCommand = "rsync -avz --exclude='.git/' --exclude='vendor/' $tempDirectoryName $remoteUser@$remoteHost:$remotePath";

    // Execute the rsync command using expect
    $expectScriptPath = "/tmp/rsync_with_password.exp";
    $expectScript = <<<EOD
>>>>>>> bab08e1de733fd3925b2d8ca0b13ae57edbb2eb0
#!/usr/bin/expect -f
log_file /tmp/expect_log.txt
exp_internal 1
set timeout -1
set password "$password"
spawn bash -c "$rsyncCommand"
expect {
<<<<<<< HEAD
	"password:" {
    	send "\$password\r"
    	exp_continue
	}
	eof {
    	# End of file or completion
	}
	timeout {
    	puts "Connection timed out or unexpected prompt format."
    	exit 1
	}
}
EOD;

	// Write the Expect script to a file
	file_put_contents($expectScriptPath, $expectScript);
	chmod($expectScriptPath, 0700);

	// Execute the Expect script
	$output = shell_exec("expect $expectScriptPath 2>&1"); // Capture standard and error output
	echo "Rsync command output:<br>$output<br>";

	// Clean up the Expect script
	unlink($expectScriptPath);
	echo "Temporary Expect script removed.<br>";

	// Prepare substring for RabbitMQ message
	$substring = substr($tempDirectoryName, strpos($tempDirectoryName, "git/") + strlen("git/"));

	// Send request to RabbitMQ
	$request = array();
	$request['type'] = "build";
	$request['build_name'] = $substring;
	$response = $client->send_request($request);
	echo "Sending Build: $substring<br>";

	// Revert the directory name
	if (rename($tempDirectoryName, $localDirectory)) {
    	echo "Renamed directory back to $localDirectory<br>";
	} else {
    	echo "Error: Could not rename $tempDirectoryName back to $localDirectory<br>";
	}
} else {
	echo "Error: Could not rename $localDirectory to $tempDirectoryName<br>";
=======
    "password:" {
        send "\$password\r"
        exp_continue
    }
    eof {
        # End of file or completion
    }
    timeout {
        puts "Connection timed out or unexpected prompt format."
        exit 1
    }
}
EOD;

    // Write the Expect script to a file
    file_put_contents($expectScriptPath, $expectScript);
    chmod($expectScriptPath, 0700);

    // Execute the Expect script
    $output = shell_exec("expect $expectScriptPath 2>&1"); // Capture standard and error output
    echo "Rsync command output:<br>$output<br>";

    // Clean up the Expect script
    unlink($expectScriptPath);
    echo "Temporary Expect script removed.<br>";

    // Prepare substring for RabbitMQ message
    $substring = substr($tempDirectoryName, strpos($tempDirectoryName, "git/") + strlen("git/"));

    // Send request to RabbitMQ
    $request = array();
    $request['type'] = "build";
    $request['build_name'] = $substring;
    $response = $client->send_request($request);
    echo "Sending Build: $substring<br>";

    // Revert the directory name
    if (rename($tempDirectoryName, $localDirectory)) {
        echo "Renamed directory back to $localDirectory<br>";
    } else {
        echo "Error: Could not rename $tempDirectoryName back to $localDirectory<br>";
    }
} else {
    echo "Error: Could not rename $localDirectory to $tempDirectoryName<br>";
>>>>>>> bab08e1de733fd3925b2d8ca0b13ae57edbb2eb0
}

echo "Rsync process completed.<br>";
?>

<<<<<<< HEAD



=======
>>>>>>> bab08e1de733fd3925b2d8ca0b13ae57edbb2eb0
