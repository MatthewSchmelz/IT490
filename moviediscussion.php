<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials for Flarum on VM3
$host = 'localhost'; // or VM3 IP address if it's different
$dbname = 'flarum';
$user = 'flarumuser'; // Replace with actual database user
$password = 'password'; // Replace with actual password

// Hardcode the user ID for testing
$userId = 1; // Admin user ID

// Movie information (this would be dynamic, for example, from a form submission)
$movieTitle = 'Batman'; // Example movie title
$movieSlug = 'batman';  // URL-friendly version of the movie title

// Step 1: Connect to the Flarum database
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Step 2: Check if the discussion already exists for this movie
$query = $db->prepare("SELECT id FROM flarum_discussions WHERE slug = :slug LIMIT 1");
$query->execute(['slug' => $movieSlug]);
$discussion = $query->fetch(PDO::FETCH_ASSOC);

if ($discussion) {
    // Step 3: If the discussion exists, redirect to the existing discussion
    $discussionId = $discussion['id'];
} else {
    // Step 4: If the discussion doesn't exist, create a new one

    // Prepare an SQL insert statement to create a new discussion
    $stmt = $db->prepare("
        INSERT INTO flarum_discussions (title, slug, created_at, user_id)
        VALUES (:title, :slug, NOW(), :user_id)
    ");

    // Execute the insertion to create the discussion
    if (!$stmt->execute([
        'title' => $movieTitle,
        'slug' => $movieSlug,
        'user_id' => $userId // Hardcoded user ID
    ])) {
        // If execution fails, fetch error information
        $errorInfo = $stmt->errorInfo();
        die("Error inserting discussion: " . implode(", ", $errorInfo));
    }

    // Get the ID of the newly created discussion
    $discussionId = $db->lastInsertId();
}

// Step 5: Redirect the user to the discussion
$flarumUrl = "http://172.24.71.42/IT490/Flarum/public/d/" . $discussionId . '-' . $movieSlug; // VM3 IP
header("Location: $flarumUrl");
exit;
?>

