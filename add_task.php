<?php
session_start();
include 'db.php';
include 'header.php';
include 'task_manager.php'; // Подключаем файл с функцией

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $reminder_time = $_POST['reminder_time'];
    $user_id = $_SESSION['user_id'];

    // Вызываем функцию для добавления задачи
    $result = addTask($conn, $title, $description, $due_date, $reminder_time, $user_id);

    // Если задача добавлена успешно или произошла ошибка, выводим сообщение
    echo "<script>alert('$result');</script>";

    // Если задача была добавлена успешно, перенаправляем на главную страницу
    if ($result == "Task created successfully.") {
        echo "<script>window.location.href = 'index.php';</script>";
        exit();
    }
}
?>

<h2>Add Task</h2>

<form method="POST" action="">
    <label for="title">Title:</label>
    <input type="text" name="title" required><br><br>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea><br><br>

    <label for="due_date">Due Date:</label>
    <input type="date" name="due_date" required><br><br>

    <label for="reminder_time">Reminder Time:</label>
    <input type="time" name="reminder_time" required><br><br>

    <input type="submit" value="Add Task">
</form>
