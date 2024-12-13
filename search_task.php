<?php
session_start();
include 'db.php';
include 'header.php';

// Заголовок для стилей
echo "<link rel='stylesheet' href='css/style.css'>";

// Проверка, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$search_type = $_GET['search_type'] ?? null;
$search_query = $_GET['search_query'] ?? null;

try {
    if ($search_type && $search_query) {
        if ($search_type == 'title') {
            $sql = "SELECT * FROM tasks WHERE title LIKE ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $search_param = "%" . $search_query . "%";
            $stmt->bind_param("si", $search_param, $_SESSION['user_id']);
        } elseif ($search_type == 'due_date') {
            $sql = "SELECT * FROM tasks WHERE due_date = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $search_query, $_SESSION['user_id']);
        } else {
            $sql = "SELECT * FROM tasks WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        echo "<div>i</div>";
        echo "<div>i</div>";
        echo "<div>i</div>";
        echo "<h1>Search Results</h1>";
        echo "<a href='index.php' class='back-link'>Back to Task List</a><br><br>";

        if ($result->num_rows > 0) {
            echo "<table class='task-table'>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Reminder Time</th>
                        <th>Status</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['title']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['due_date']}</td>
                        <td>{$row['reminder_time']}</td>
                        <td>{$row['status']}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No tasks found.</p>";
        }
    } else {
        echo "<p>Please enter a search query.</p>";
    }
} catch (Exception $e) {
    echo "<script>alert('Error searching task: " . addslashes($e->getMessage()) . "');</script>";
    exit;
}
?>
