<?php
include 'db.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Unauthorized access"]));
}

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

if ($receiver_id == 0) {
    die(json_encode(["error" => "No user selected"]));
}

// Fetch only new messages after last_id
$query = "
    SELECT messages.id, messages.sender_id, messages.message, messages.timestamp, users.username
    FROM messages 
    JOIN users ON messages.sender_id = users.id
    WHERE ((messages.sender_id = ? AND messages.receiver_id = ?) 
       OR (messages.sender_id = ? AND messages.receiver_id = ?))
       AND messages.id > ?
    ORDER BY messages.timestamp ASC
";

// Log the query for debugging
error_log("Query: " . $query);  // Debugging query
$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $user_id, $receiver_id, $receiver_id, $user_id, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => $row['id'],
        "sender" => ($row['sender_id'] == $user_id) ? "You" : $row['username'],
        "message" => $row['message'],
        "timestamp" => $row['timestamp'],
        "class" => ($row['sender_id'] == $user_id) ? "sent" : "received"
    ];
}

// Log the fetched messages
error_log("Fetched messages: " . json_encode($messages));

echo json_encode($messages);
?>
