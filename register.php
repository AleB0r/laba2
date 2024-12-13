<?php
include 'db.php';
include 'RegisterController.php'; // Подключаем функцию регистрации

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $skills = $_POST['skills']; // Массив выбранных умений

    // Вызываем функцию регистрации
    $result = registerUser($conn, $username, $password, $confirm_password, $skills);

    if ($result === 'Registration successful!') {
        header('Location: login.php');
        exit();
    } else {
        echo "<script>alert('$result');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/login.css">
    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;

            if (username.includes(" ")) {
                alert("Username cannot contain spaces!");
                return false;
            }

            if (password.includes(" ")) {
                alert("Password cannot contain spaces!");
                return false;
            }

            if (password !== confirm_password) {
                alert("Passwords do not match!");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h1>Register</h1>
        <form method="POST" action="" onsubmit="return validateForm();">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="input-field" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="input-field" required><br><br>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="input-field" required><br><br>

            <label for="skills">Select Your Skills:</label>
            <select id="skills" name="skills[]" class="input-field" multiple required>
                <?php
                $result = $conn->query("SELECT id, name FROM programming_languages");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select><br><br>

            <input type="submit" value="RegisterСontroller" class="btn-login">
        </form>
        <p>Already have an account? <a href="login.php" class="btn">Login here</a></p>
    </div>
</body>
</html>
