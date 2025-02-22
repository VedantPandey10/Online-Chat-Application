<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $email, $hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        header("Location: chat.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title>Login</title>
    <style>
        body {
            background: url("Vedant_Image.jpg") no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username or Email:</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>

        </form>
    </div>
</body>
</html>