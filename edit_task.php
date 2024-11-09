<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

date_default_timezone_set('Europe/Moscow');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $reminder_time = $_POST['reminder_time'];
    $status = $_POST['status'];

    $current_datetime = new DateTime();
    $due_datetime = new DateTime($due_date . ' ' . $reminder_time);

    if ($due_datetime < $current_datetime) {
        $_SESSION['message'] = "The date cannot be in the past.";
        header("Location: index.php");
        exit();
    } else {
        try {
            $check_sql = "SELECT * FROM tasks WHERE title = ? AND user_id = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("sii", $title, $user_id, $task_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $_SESSION['message'] = "A task with this title already exists.";
                header("Location: index.php");
                exit();
            } else {
                $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, reminder_time = ?, status = ? WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssii", $title, $description, $due_date, $reminder_time, $status, $task_id, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Task updated successfully.";
                } else {
                    $_SESSION['message'] = "Error updating task: " . $stmt->error;
                }
                header("Location: index.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    }
} else {
    $task_id = $_GET['task_id'];

    try {
        $sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "<p>Task not found.</p>";
            exit();
        }

        $task = $result->fetch_assoc();
    } catch (Exception $e) {
        echo "Error retrieving task: " . $e->getMessage();
    }
}
?>

<h2>Edit Task</h2>
<form method="POST" action="">
    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
    
    <label for="title">Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br><br>

    <label for="description">Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea><br><br>

    <label for="due_date">Due Date:</label>
    <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required><br><br>

    <label for="reminder_time">Reminder Time:</label>
    <input type="time" name="reminder_time" value="<?php echo $task['reminder_time']; ?>" required><br><br>

    <label for="status">Status:</label>
    <select name="status">
        <option value="pending" <?php if ($task['status'] == 'pending') echo 'selected'; ?>>Pending</option>
        <option value="complete" <?php if ($task['status'] == 'complete') echo 'selected'; ?>>Complete</option>
    </select><br><br>

    <input type="submit" value="Update Task">
</form>