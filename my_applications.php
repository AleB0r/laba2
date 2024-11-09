<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем уведомления для пользователя
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC");

$notification_messages = [];
while ($notification = $notifications->fetch_assoc()) {
    $notification_messages[] = htmlspecialchars($notification['message']);
    // Обновляем уведомление, чтобы отметить его как прочитанное
    $update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $notification['id']);
    $update_stmt->execute();
}


$applications = $conn->query("
    SELECT pa.*, p.title AS project_title, p.description AS project_description, u.username AS team_lead_name
    FROM project_applications pa
    JOIN projects p ON pa.project_id = p.id
    JOIN users u ON p.team_lead_id = u.id
    WHERE pa.user_id = $user_id
    ORDER BY pa.id DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_application_id'])) {
    $cancel_application_id = $_POST['cancel_application_id'];

  
    $check_stmt = $conn->prepare("SELECT status FROM project_applications WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $cancel_application_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $application = $check_result->fetch_assoc();
        $status = $application['status'];


        if ($status != 'approved' && $status != 'rejected') {
            $delete_stmt = $conn->prepare("DELETE FROM project_applications WHERE id = ?");
            $delete_stmt->bind_param("i", $cancel_application_id);
            if ($delete_stmt->execute()) {
                echo "<script>alert('Заявка успешно отменена.');</script>";
                header("Location: my_applications.php"); 
                exit();
            } else {
                echo "<script>alert('Ошибка при отмене заявки.');</script>";
            }
        } else {
            echo "<script>alert('Вы не можете отменить одобренную или отвергнутую заявку.');</script>";
        }
    } else {
        echo "<script>alert('Заявка не найдена.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<script>
    function showNotifications() {
        var notifications = <?= json_encode($notification_messages) ?>;
        if (notifications.length > 0) {
            alerts = notifications.map(message => `Notifications: ${message}`).join('\n');
            alert(alerts);
        }
    }
</script>
<body onload="showNotifications()">
<div>i</div>
    <div>i</div>
    <div>i</div>
    <h1>Project Applications</h1>
    <table class="task-table">
        <tr>
            <th>Project Title</th>
            <th>Description</th>
            <th>Team Lead</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($app = $applications->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($app['project_title']) ?></td>
                <td><?= htmlspecialchars($app['project_description']) ?></td>
                <td><?= htmlspecialchars($app['team_lead_name']) ?></td>
                <td><?= htmlspecialchars($app['status']) ?></td>
                <td>
                    <?php if ($app['status'] != 'approved' && $app['status'] != 'rejected'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="cancel_application_id" value="<?= $app['id'] ?>">
                            <input type="submit" value="Cancel application">
                        </form>
                    <?php else: ?>
                        NO
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
