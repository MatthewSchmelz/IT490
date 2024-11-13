<?php

// Configuration
$remoteUser = "markcgv";
$remoteHost = "172.24.138.28";
$remotePath = "/home/markcgv/Downloads";
$localDirectory = "home/Downloads/hello"; // Specify the local directory name
$password = "Qwe321qaZ~"; // Set the password here

// Step 1: Confirm configuration setup
echo "Starting SCP process...\n";
echo "Configuration:\n";
echo "Remote User: $remoteUser\n";
echo "Remote Host: $remoteHost\n";
echo "Remote Path: $remotePath\n";
echo "Local Directory: $localDirectory\n";

// Temporary file to store the expect script
$expectScriptPath = "/tmp/scp_with_password.exp";
echo "Creating expect script at $expectScriptPath...\n";

// Step 2: Create the expect script content
$expectScript = <<<EOD
#!/usr/bin/expect -f
set timeout -1
set password "$password"
set src "$localDirectory"
set dest "$remoteUser@$remoteHost:$remotePath"

spawn scp -r \$src \$dest
spawn scp -r \$src \$dest
expect "password:"
send "\$password\r"
expect eof
EOD;

// Step 3: Write the expect script to a temporary file
file_put_contents($expectScriptPath, $expectScript);
echo "Expect script created successfully.\n";

// Step 4: Make the expect script executable
chmod($expectScriptPath, 0700);
echo "Expect script permissions set to executable.\n";

// Step 5: Run the expect script and capture the output
echo "Running SCP command via expect script...\n";
echo $expectScriptPath; echo "\n";
$output = shell_exec("expect $expectScriptPath");

// Step 6: Output the result of the SCP command
echo "SCP command output:\n";
echo $output;

// Step 7: Clean up by removing the temporary expect script
unlink($expectScriptPath);
echo "Temporary expect script removed.\n";

echo "SCP process completed.\n";

?>

