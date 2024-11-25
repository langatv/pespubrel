<?php
require_once 'utils.php';

function logMessage($userId, $message, $type = 'info') {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO user_messages (user_id, message, type) VALUES (:user_id, :message, :type)");
    $stmt->execute([
        ':user_id' => $userId,
        ':message' => $message,
        ':type' => $type,
    ]);
}
