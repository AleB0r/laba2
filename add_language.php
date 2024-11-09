<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['language_name'])) {
    $language_name = trim($_POST['language_name']);

    if (empty($language_name)) {
        echo "<script>alert('Error: Language name cannot be empty or contain only spaces.');</script>";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM programming_languages WHERE name = ?");
            $stmt->bind_param("s", $language_name);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>alert('Error: Language already exists.');</script>";
            } else {
                $stmt = $conn->prepare("INSERT INTO programming_languages (name) VALUES (?)");
                $stmt->bind_param("s", $language_name);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                echo "<script>alert('Language added successfully.'); window.location.href='view_languages.php';</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error adding language: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Add New Language</title>
    <script>
        function validateForm() {
            var languageName = document.getElementById('language_name').value.trim(); 
            if (languageName === '') {
                alert('Language name cannot be empty or contain only spaces.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div>i</div>
    <div>i</div>
    <div>i</div>
    <h1>Add New Programming Language</h1>
    <form method="POST" action="" onsubmit="return validateForm();">
        <label for="language_name">Language Name:</label>
        <input type="text" name="language_name" id="language_name" required>
        <input type="submit" value="Add Language">
    </form>
</body>
</html>
