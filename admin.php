<?php
session_start();
include 'db.php';
include 'header.php';

// Проверяем, авторизован ли админ
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Обработка изменения типа пользователя
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['action']) && $_POST['action'] == 'update') {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];

    try {
        // Получаем текущий тип пользователя
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_user = $result->fetch_assoc();
        $current_user_type = $current_user['user_type'];

        // Обновляем тип пользователя
        $sql = "UPDATE users SET user_type = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $user_type, $user_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // Если понижаем до обычного пользователя, удаляем все заявки и проекты
        if ($user_type == 'user') {
            $stmt = $conn->prepare("DELETE FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE team_lead_id = ?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM projects WHERE team_lead_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

        } elseif ($current_user_type == 'user' && $user_type == 'team_lead') {
            // Если повышаем пользователя до тимлида, удаляем все его заявки
            $stmt = $conn->prepare("DELETE FROM project_applications WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            echo "<script>alert('User promoted to Team Lead. All applications were deleted.');</script>";
        }

        echo "<script>alert('User updated successfully.');</script>";
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error updating user: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Обработка удаления пользователя
if (isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    try {
        // Получаем текущий тип пользователя
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Если удаляем тимлида, удаляем все его проекты и заявки на них
        if ($user['user_type'] === 'team_lead') {
            $stmt = $conn->prepare("DELETE FROM project_applications WHERE project_id IN (SELECT id FROM projects WHERE team_lead_id = ?)");
            $stmt->bind_param("i", $delete_user_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM projects WHERE team_lead_id = ?");
            $stmt->bind_param("i", $delete_user_id);
            $stmt->execute();

        } elseif ($user['user_type'] === 'user') {
            // Если удаляем обычного пользователя, удаляем его заявки и задачи
            $stmt = $conn->prepare("DELETE FROM project_applications WHERE user_id = ?");
            $stmt->bind_param("i", $delete_user_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM tasks WHERE user_id = ?");
            $stmt->bind_param("i", $delete_user_id);
            $stmt->execute();
        }

        // Удаляем самого пользователя
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_user_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        echo "<script>alert('User deleted successfully.');</script>";
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error deleting user: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Получаем список всех пользователей
$result = $conn->query("SELECT * FROM users WHERE user_type IN ('user', 'team_lead')");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Manage Users</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="POST" action="">
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td>
                        <select name="user_type">
                            <option value="user" <?= $row['user_type'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="team_lead" <?= $row['user_type'] == 'team_lead' ? 'selected' : '' ?>>Team Lead</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="submit" value="Update">
                </form>
                <form method="POST" action="">
                    <input type="hidden" name="delete_user_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
