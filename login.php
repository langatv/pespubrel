<?php
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

    // Start session to store error messages and user info
    session_start();
    
      // Function to log messages
      function logMessage($userId, $message, $type = 'info') {
        global $pdo; // Ensure $pdo is defined in the file
        $stmt = $pdo->prepare("INSERT INTO users_messages (user_id, message, type) VALUES (:user_id, :message, :type)");
        $stmt->execute([
            ':user_id' => $userId,
            ':message' => $message,
            ':type' => $type,
        ]);
    }



    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form inputs
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Input validation
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: loginy.php");
            exit();
        }

        // Query to fetch user by username
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Verify user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            $_SESSION['username'] = $user['username']; // Store username in session
            $_SESSION['email'] = $user['email']; // Optionally store email
            $_SESSION['referred_by'] = $user['referred_by'];
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            // Invalid credentials
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: loginy.php");
            exit();
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


