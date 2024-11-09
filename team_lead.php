<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

$team_lead_id = $_SESSION['user_id'];

// Обработка удаления проекта
if (isset($_POST['delete_project'])) {
    $project_id = $_POST['project_id'];

    // Удаление заявок, связанных с проектом
    $conn->query("DELETE FROM project_applications WHERE project_id = $project_id");

    // Удаление проекта из базы данных
    $conn->query("DELETE FROM projects WHERE id = $project_id AND team_lead_id = $team_lead_id");

    // Перенаправление после удаления, чтобы обновить список проектов
    header('Location: team_lead.php');
    exit();
}

// Получение проектов с языками программирования
$projects = $conn->query("
    SELECT p.id, p.title, p.description, GROUP_CONCAT(pl.name SEPARATOR ', ') AS languages
    FROM projects p
    LEFT JOIN project_languages pl_assoc ON p.id = pl_assoc.project_id
    LEFT JOIN programming_languages pl ON pl_assoc.language_id = pl.id
    WHERE p.team_lead_id = $team_lead_id
    GROUP BY p.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Lead Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div>i</div>
    <div>i</div>
    <div>i</div>
    <h2>Your Projects</h2>
    <table class="task-table"> 
        <tr>
            <th>Project Title</th>
            <th>Description</th>
            <th>Programming Languages</th>
            <th>Actions</th>
        </tr>
        <?php while ($project = $projects->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($project['title']) ?></td>
            <td><?= htmlspecialchars($project['description']) ?></td>
            <td><?= htmlspecialchars($project['languages']) ?></td>
            <td>
                <a class="view-applications" href="view_project.php?project_id=<?= htmlspecialchars($project['id']) ?>">View Applications</a>
                
                <!-- Форма для удаления проекта -->
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['id']) ?>">
                    <button type="submit" name="delete_project" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <style>
        .delete-btn {
            color: #ffffff;
            background-color: #dc3545;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</body>
</html>
