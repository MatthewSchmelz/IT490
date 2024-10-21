<?php
// Example movie discussion ID (this could be dynamically set based on the movie the user is viewing)
$discussionId = 5; // Example discussion ID for "Batman"

// Flarum URL hosted on VM3 (adjust the IP and path accordingly)
$flarumUrl = "http://172.24.71.42/IT490/moviediscussion.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Discussion</title>
</head>
<body>
    <h1>Movie Page: Batman</h1>
    
    <!-- This button redirects the user to the Flarum discussion for the movie -->
    <form action="<?php echo $flarumUrl; ?>" method="GET">
        <button type="submit">Go to Movie Discussion</button>
    </form>

    <!-- Alternative way using a hyperlink -->
    <p>Or you can click <a href="<?php echo $flarumUrl; ?>">here</a> to go to the discussion directly.</p>
</body>
</html>

