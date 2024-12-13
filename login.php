<?php
session_start();
include 'db.php';

date_default_timezone_set('Europe/Moscow'); // Установите ваш часовой пояс

// Максимальное количество попыток
$max_attempts = 3;
$lockout_time = 1; // Время блокировки в минутах

// Генерация CSRF-токена при загрузке страницы
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function isLockedOut($conn, $username, $max_attempts, $lockout_time) {
    $stmt = $conn->prepare("SELECT attempt_count, locked_until FROM login_attempts WHERE username = ? ORDER BY attempt_time DESC LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $locked_until = strtotime($row['locked_until']);
        $attempt_count = $row['attempt_count'];

        if ($locked_until && $locked_until > time()) {
            return $locked_until; // Вернем время окончания блокировки
        }

        // Если блокировка закончилась, сбрасываем счетчик попыток
        if ($attempt_count >= $max_attempts) {
            $locked_until_time = date("Y-m-d H:i:s", strtotime("+$lockout_time minutes"));
            $stmt = $conn->prepare("UPDATE login_attempts SET locked_until = ?, attempt_count = 0 WHERE username = ?");
            $stmt->bind_param("ss", $locked_until_time, $username);
            $stmt->execute();
            return strtotime($locked_until_time);
        }
    }

    return false; // Блокировки нет
}

function recordLoginAttempt($conn, $username) {
    $stmt = $conn->prepare("
        INSERT INTO login_attempts (username, attempt_time, attempt_count) 
        VALUES (?, NOW(), 1) 
        ON DUPLICATE KEY UPDATE 
            attempt_count = attempt_count + 1, 
            attempt_time = NOW()
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
}

$lockout_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('Invalid CSRF token');</script>";
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверка блокировки
    $locked_until = isLockedOut($conn, $username, $max_attempts, $lockout_time);
    if ($locked_until) {
        $lockout_message = "Too many attempts. Try again after " . date("H:i:s", $locked_until) . ".";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Успешный вход
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['login_time'] = time();

                    // Сбросить попытки и блокировку при успешном входе
                    $stmt = $conn->prepare("UPDATE login_attempts SET attempt_count = 0, locked_until = NULL WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();

                    // Редирект на страницы в зависимости от типа пользователя
                    if ($user['user_type'] == 'admin') {
                        header('Location: admin.php');
                        exit();
                    } elseif ($user['user_type'] == 'team_lead') {
                        header('Location: team_lead.php');
                        exit();
                    } else {
                        header('Location: index.php');
                        exit();
                    }
                } else {
                    recordLoginAttempt($conn, $username);
                    $lockout_message = 'Incorrect password.';
                }
            } else {
                recordLoginAttempt($conn, $username);
                $lockout_message = 'User not found.';
            }
        } catch (Exception $e) {
            $lockout_message = 'Error: ' . $e->getMessage();
        }
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
        <?php if (!empty($lockout_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($lockout_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
