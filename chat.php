<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'], $_POST['receiver_id'])) {
    $message = trim($_POST['message']);
    $receiver_id = (int)$_POST['receiver_id'];

    if (!empty($message) && $receiver_id > 0) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Get chat partner
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

// Fetch all users except the logged-in user
$user_query = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$users = $user_query->get_result();
$user_query->close();

// Fetch messages (two-way chat)
$messages = [];
if ($receiver_id) {
    $msg_query = $conn->prepare("
        SELECT sender_id, message, timestamp 
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY timestamp ASC
    ");
    $msg_query->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $msg_query->execute();
    $messages = $msg_query->get_result();
    $msg_query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background: url("vedant.jpg") no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .chat-container {
            width: 100%;
            max-width: 500px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        #chat-box {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .message {
            padding: 10px;
            margin: 5px;
            border-radius: 10px;
            max-width: 80%;
            display: inline-block;
        }
        .sent {
            background-color: #dcf8c6;
            text-align: right;
            float: right;
            clear: both;
        }
        .received {
            background-color: #f1f0f0;
            text-align: left;
            float: left;
            clear: both;
        }
        .input-group {
            margin-top: 10px;
            border-radius: 2px;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <h2>Chat App</h2>

    <!-- Select Chat Partner -->
    <form method="GET">
        <label for="receiver">Chat with:</label>
        <select name="receiver_id" id="receiver" class="form-select" onchange="this.form.submit()">
            <option value="">Select a user</option>
            <?php while ($row = $users->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" <?= $receiver_id == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['username']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- Chat Box -->
    <div id="chat-box">
        <?php if ($receiver_id): ?>
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                    <strong><?= $msg['sender_id'] == $user_id ? "You" : "Partner" ?>:</strong>
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    <br><small><em><?= $msg['timestamp'] ?></em></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Select a user to start chatting.</p>
        <?php endif; ?>
    </div>

    <!-- Message Form -->
    <?php if ($receiver_id): ?>
        <form method="POST" class="input-group">
            <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
            <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    <?php endif; ?>
</div>

<script>
    // Auto-scroll to the bottom of chat
    document.addEventListener("DOMContentLoaded", function() {
        let chatBox = document.getElementById("chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>

</body>
</html>
