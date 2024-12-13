<?php
session_start();
require 'db.php';
require 'header.php'; // Подключение к базе данных

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$query = "SELECT id, username, user_type, avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($id, $username, $user_type, $avatar);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['setphoto'])) {
    if (!is_readable($_FILES['avatar']['tmp_name'])) {
        echo "<script>alert('File is not readable. Check file permissions and try again.');</script>";
        echo '<script>window.location.href="profile.php";</script>';
        exit();
    }
    // Проверяем, был ли загружен файл
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        // Максимальный размер файла (2 MB)
        $maxFileSize = 2*1024*1024 ;



        // Проверяем, является ли файл изображением
        $imageInfo = getimagesize($_FILES['avatar']['tmp_name']);
        if ($imageInfo === false) {
            echo "<script>alert('Invalid image. The file may be corrupted or not an image. Please try again.');</script>";
            echo '<script>window.location.href="profile.php";</script>';
            exit(); // Прерываем дальнейшее выполнение
        }

        

        // Проверяем, не превышает ли файл размер 2 MB
        if ($_FILES['avatar']['size'] > $maxFileSize) {
            echo "<script>alert('File is too big, 2 MB is a limit for files.');</script>";
            echo '<script>window.location.href="profile.php";</script>';
            exit(); // Прерываем дальнейшее выполнение
        }

        // Если все проверки пройдены
        $photo = file_get_contents($_FILES['avatar']['tmp_name']);
        $stmt = $conn->prepare("UPDATE users SET avatar=? WHERE id=?");
        $stmt->bind_param("si", $photo, $user_id);
        if ($stmt->execute()) {
            echo '<script>alert("Avatar updated successfully.");</script>';
            echo '<script>window.location.href="profile.php";</script>';
        } else {
            echo "<script>alert('Error saving the avatar. Please try again later.');</script>";
        }
    } else {
        // Обработка ошибок загрузки файла
        switch ($_FILES['avatar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo "<script>alert('The uploaded file exceeds the upload_max_filesize directive in php.ini');</script>";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "<script>alert('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');</script>";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "<script>alert('The uploaded file was only partially uploaded');</script>";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "<script>alert('No file was uploaded');</script>";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "<script>alert('Missing a temporary folder');</script>";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "<script>alert('Failed to write file to disk');</script>";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "<script>alert('A PHP extension stopped the file upload');</script>";
                break;
            default:
                echo "<script>alert('Unknown error occurred during file upload');</script>";
                break;
        }
    
        exit(); // Прерываем дальнейшее выполнение
    }
}}
catch (Exception $e) {
    echo "<script>alert('Error MySQL');</script>";

    exit();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
        }
        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ccc;
        }
        .profile-info {
            margin-top: 20px;
        }
        .profile-info p {
            font-size: 18px;
            margin: 5px 0;
        }
        form {
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Your Profile</h1>

    <!-- Аватар пользователя -->
    <h3>Avatar:</h3>
    <?php if ($avatar): ?>
        <img src="data:image/*;base64,<?= base64_encode($avatar) ?>" alt="Avatar" class="avatar">
    <?php else: ?>
        <p>No avatar set.</p>
    <?php endif; ?>

    <!-- Информация о пользователе -->
    <div class="profile-info">
        <p><strong>ID:</strong> <?= htmlspecialchars($id) ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
        <p><strong>User Type:</strong> <?= htmlspecialchars($user_type) ?></p>
    </div>

    <!-- Форма для изменения аватара -->
    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <label for="avatar">Change Avatar:</label><br>
        <input type="file" name="avatar" id="avatar" accept="image/*" required>
        <button type="submit" name="setphoto">Upload New Avatar</button>
    </form>
</body>
</html>
