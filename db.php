<?php
$host = "localhost";
$user = "root"; // Change if necessary
$password = "";
$dbname = "online_chat_db"; // Updated database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
