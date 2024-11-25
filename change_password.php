<?php
session_start();


// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: loginy.php");
    exit();
}

// Database connection parameters
$host = "localhost";
$dbname = "REG1";
$user = "postgres";
$password = "1234";

try {
    // Database connection
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

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


    // Handle Change Password Request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Fetch current password hash from database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['password_error'] = "User not found.";
            header("Location: dashboard.php");
            exit();
        }

        // Verify the current password
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['password_error'] = "Current password is incorrect.";
            logMessage($_SESSION['user_id'], "Your password was successfully changed.", 'error');
            header("Location: dashboard.php");
            exit();
        }

        // Check if new password and confirm password match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['password_error'] = "New passwords do not match.";
            header("Location: dashboard.php");
            exit();
        }

        // Hash the new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
        $stmt->execute([':new_password' => $newPasswordHash, ':user_id' => $userId]);

        $_SESSION['password_success'] = "Password changed successfully.";
        logMessage($_SESSION['user_id'], "Your password was successfully changed.", 'success');
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
