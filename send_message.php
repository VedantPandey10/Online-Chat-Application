<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    $receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;
    $sender_id = $_SESSION['user_id'];

    if (!empty($message) && $receiver_id > 0) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            echo "Message sent"; 
        } else {
            echo "Error: " . $conn->error; 
        }
    } else {
        echo "Message cannot be empty!";
    }
}
?>