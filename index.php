<?php
echo "<link rel='stylesheet' href='css/style.css'>";
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['message'])) {
    echo "<script>alert('" . addslashes($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']);
}


$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    echo "<div class='task-manager-container'>";
    echo "<h1>Task Manager</h1>";
    echo "<a href='add_task.php' class='add-task-link'>Add New Task</a><br><br>";
    

    if ($result->num_rows > 0) {
        echo "<table class='task-table'>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Reminder Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $checked = ($row['status'] === 'complete') ? 'checked' : '';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['due_date']}</td>
                    <td>{$row['reminder_time']}</td>
                    <td>
                        <form action='complete_task.php' method='post' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <input type='checkbox' name='status' value='complete' onchange='this.form.submit();' $checked>
                        </form>
                    </td>
                    <td>
                        <form action='edit_task.php' method='get' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <button type='submit' class='edit-button' title='Edit'>&#9998;</button>
                        </form>
                        <form action='delete_task.php' method='post' style='display:inline;'>
                            <input type='hidden' name='task_id' value='{$row['id']}'>
                            <button type='submit' class='delete-button'>&#10005;</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-tasks'>No tasks found.</p>";
    }
    echo "<form method='POST' action='search_task.php' class='form1'>
            <select name='search_type'>
                <option value='title'>Title</option>
                <option value='due_date'>Due Date</option>
            </select>
            <input type='text' name='search_query' placeholder='Search' required>
            <input type='submit' value='Search'>
          </form>";
} catch (Exception $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
}
?>