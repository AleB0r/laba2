<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $status = isset($_POST['status']) && $_POST['status'] === 'complete' ? 'complete' : 'pending';

    try {
        $sql = "UPDATE tasks SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $task_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating task status: " . $stmt->error);
        }
        $stmt->close();

        
        $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        header("Location: $redirect_url");
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
