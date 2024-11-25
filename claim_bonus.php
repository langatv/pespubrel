<?php 
session_start();

// Ensure the response is JSON
header('Content-Type: application/json');

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to claim the bonus.']);
    exit();
}

// Database connection
$host = "localhost";
$dbname = "REG1";
$user = "postgres";
$password = "1234";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check if the user already claimed the bonus
    $stmt = $pdo->prepare("SELECT bonus_claimed FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    if ($user['bonus_claimed']) {
        echo json_encode(['success' => false, 'message' => 'You have already claimed your bonus.']);
        exit();
    }

    // Add the bonus (e.g., 100 KSH) to the user's account balance
    $bonusAmount = 100; // The bonus amount
    $stmt = $pdo->prepare("UPDATE balances SET balance = balance + :bonus WHERE user_id = :user_id");
    $stmt->execute([':bonus' => $bonusAmount, ':user_id' => $_SESSION['user_id']]);

    // Mark the bonus as claimed
    $stmt = $pdo->prepare("UPDATE users SET bonus_claimed = TRUE WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);

    echo json_encode(['success' => true, 'message' => 'Bonus claimed successfully!']);
} 


catch (PDOException $e) {
    // Return database error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}


?>
