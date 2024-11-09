<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255)
)";

$sql_tasks = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    due_date DATE,
    reminder_time TIME,
    status VARCHAR(255),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql_users) === TRUE && $conn->query($sql_tasks) === TRUE) {
    echo "Tables created successfully.";
} else {
    echo "Error creating tables: " . $conn->error;
}
?>
