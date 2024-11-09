<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type']; 
                $_SESSION['login_time'] = time(); 
                echo "<script>alert('Incorrect password');</script>";
                if ($user['user_type'] == 'admin') {
                    header('Location: admin.php');
                } elseif ($user['user_type'] == 'team_lead') {
                    header('Location: team_lead.php');
                }
                else  {
                    header('Location: index.php');
                }
                exit();
            } else {
                echo "<script>alert('Incorrect password');</script>";
            }
        } else {
            echo "<script>alert('User not found');</script>";
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/Login.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" class="input-field" required><br><br>

            <label for="password">Password:</label>
            <input type="password" name="password" class="input-field" required><br><br>

            <input type="submit" value="Login" class="btn-login">
        </form>

        <p>Don't have an account? <a href="register.php" class="btn-register">Register here</a></p>
    </div>
</body>
</html>
