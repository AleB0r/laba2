<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $skills = $_POST['skills']; // Массив выбранных умений

    $user_type = 'user';

    if (strpos($username, ' ') !== false) {
        echo "<script>alert('Username cannot contain spaces!');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } elseif (empty($skills)) {
        echo "<script>alert('Please select at least one skill!');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Начало транзакции
            $conn->begin_transaction();

            // Добавление пользователя
            $stmt = $conn->prepare("INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $user_type);

            if ($stmt->execute() === TRUE) {
                $user_id = $conn->insert_id; // Получаем ID нового пользователя

                // Добавление навыков пользователя
                $skill_stmt = $conn->prepare("INSERT INTO user_skills (user_id, language_id) VALUES (?, ?)");
                foreach ($skills as $skill_id) {
                    $skill_stmt->bind_param("ii", $user_id, $skill_id);
                    $skill_stmt->execute();
                }

                // Подтверждение транзакции
                $conn->commit();
                
                header('Location: login.php');
                exit();
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $conn->rollback();
            echo "<script>alert('Error registering user: " . addslashes($e->getMessage()) . "');</script>";
        }
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

            <input type="submit" value="Register" class="btn-login">
        </form>
        <p>Already have an account? <a href="login.php" class="btn">Login here</a></p>
    </div>
</body>
</html>
