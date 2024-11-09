<?php
session_start();
include 'db.php';
echo "<link rel='stylesheet' href='css/add_style.css'>";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

date_default_timezone_set('Europe/Moscow');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $reminder_time = $_POST['reminder_time'];
    $user_id = $_SESSION['user_id'];
    
    $current_datetime = new DateTime();
    $due_datetime = new DateTime($due_date . ' ' . $reminder_time);

    if ($due_datetime < $current_datetime) {
        echo "<script>alert('Error: The due date and reminder time must be in the future.')</script>";
    } else {
        try {
            $status = 'pending';

            $checkSql = "SELECT COUNT(*) FROM tasks WHERE title = ? AND user_id = ?";
            $stmt = $conn->prepare($checkSql);
            $stmt->bind_param('si', $title, $user_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                echo "<script>alert('Error: The title must be unique.')</script>";
            } else {
                $sql = "INSERT INTO tasks (title, description, due_date, reminder_time, status, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssi', $title, $description, $due_date, $reminder_time, $status, $user_id);
                
                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit();
                } else {
                    throw new Exception($stmt->error);
                }
            }
        } catch (Exception $e) {
            echo "<script>alert('Error adding task: " . addslashes($e->getMessage()) . "');</script>";
        }
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