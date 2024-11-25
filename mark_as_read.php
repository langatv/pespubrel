<?php
// Database connection parameters
$host = "localhost";       
$dbname = "REG1"; 
$user = "postgres";       
$password = "1234";

try {
    // Create a new PDO instance
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Start the session to get the logged-in user's ID
    session_start();
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login if user is not authenticated
        header("Location: loginy.php");
        exit();
    }

    // Get the logged-in user's ID
    $userId = $_SESSION['user_id'];

    // Update query to mark messages as read
    $query = "UPDATE users_messages 
              SET is_read = TRUE 
              WHERE user_id = :user_id AND is_read = FALSE";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $userId]);

} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
