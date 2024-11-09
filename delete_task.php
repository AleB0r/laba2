<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];

    try {
        $sql = "DELETE FROM tasks WHERE id = $task_id";
        if ($conn->query($sql) !== TRUE) {
            throw new Exception($conn->error);
        }

        if ($_SESSION['user_type'] == 'team_lead') {
            header("Location: team_lead.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Error deleting task: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

