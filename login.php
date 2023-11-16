<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'db_connect.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Retrieve the hashed password from the database
    $sql = "SELECT id, username, password, role FROM Users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $hashed_password, $role);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        // Authentication successful, set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        header("Location: index.php"); // Redirect to the main page after login
        exit();
    } else {
        $login_error = "Invalid username or password";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <title>Login</title>
</head>
<body>
    <div class="header">
        <h1>Welcome to GourmetHub</h1>
    </div>

    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <input type="submit">
    </form>

    <?php if (isset($login_error)): ?>
        <p><?= $login_error ?></p>
    <?php endif; ?>
</body>
</html>
