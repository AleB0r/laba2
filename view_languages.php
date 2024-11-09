<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['delete_language_id'])) {
    $delete_language_id = $_POST['delete_language_id'];
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE programming_language_id = ?");
        $stmt->bind_param("i", $delete_language_id);
        $stmt->execute();
        $stmt->bind_result($project_count);
        $stmt->fetch();
        $stmt->close();

        if ($project_count > 0) {

            $confirm_delete_language_id = $delete_language_id;
            echo "<script>alert('This language is used in $project_count projects. Deleting the language and set it to NULL in these projects.');</script>";
    try {
        // Установить programming_language_id в NULL в проектах, где использовался удаляемый язык
        $stmt = $conn->prepare("UPDATE projects SET programming_language_id = NULL WHERE programming_language_id = ?");
        $stmt->bind_param("i", $confirm_delete_language_id);
        $stmt->execute();
        $stmt->close();

        // Удаление языка
        $stmt = $conn->prepare("DELETE FROM programming_languages WHERE id = ?");
        $stmt->bind_param("i", $confirm_delete_language_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        echo "<script>alert('Language deleted and projects updated successfully.');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error deleting language and updating projects: " . addslashes($e->getMessage()) . "');</script>";
    }
        } else {
            // Удаление языка
            $stmt = $conn->prepare("DELETE FROM programming_languages WHERE id = ?");
            $stmt->bind_param("i", $delete_language_id);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            echo "<script>alert('Language deleted successfully.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error deleting language: " . addslashes($e->getMessage()) . "');</script>";
    }
}

if (isset($_POST['confirm_delete_language_id'])) {
    $confirm_delete_language_id = $_POST['confirm_delete_language_id'];
    echo "<script>alert('This language is used in $project_count projects. Deleting the language and set it to NULL in these projects.');</script>";
    try {
        // Установить programming_language_id в NULL в проектах, где использовался удаляемый язык
        $stmt = $conn->prepare("UPDATE projects SET programming_language_id = NULL WHERE programming_language_id = ?");
        $stmt->bind_param("i", $confirm_delete_language_id);
        $stmt->execute();
        $stmt->close();

        // Удаление языка
        $stmt = $conn->prepare("DELETE FROM programming_languages WHERE id = ?");
        $stmt->bind_param("i", $confirm_delete_language_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        echo "<script>alert('Language deleted and projects updated successfully.');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error deleting language and updating projects: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$result = $conn->query("SELECT * FROM programming_languages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Programming Languages</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Programming Languages</h1>
    <table class="task-table">
        <tr>
            <th>ID</th>
            <th>Language Name</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="delete_language_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this language?');">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <form method="POST" action="" id="deleteForm">
        <input type="hidden" name="confirm_delete_language_id" id="confirmDeleteLanguage">
    </form>
</body>
</html>
