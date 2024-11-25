<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to set flash messages
function setFlashMessage($type, $message) {
    // Check if flash_messages session exists, if not, initialize it
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    // Store the message under the specified type
    $_SESSION['flash_messages'][$type][] = $message;
}

// Function to display all flash messages
function displayFlashMessages() {
    // Check if there are flash messages in the session
    if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        // Loop through each message type
        foreach ($_SESSION['flash_messages'] as $type => $messages) {
            foreach ($messages as $message) {
                // Display each message in a div with the appropriate class
                echo "<div class='flash-message {$type}'>{$message}</div>";
            }
        }
        // Clear flash messages after displaying them
        unset($_SESSION['flash_messages']);
    }
}

?>