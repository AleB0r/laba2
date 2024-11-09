<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['apply_project_id'])) {
        $project_id = $_POST['apply_project_id'];

        $existing_application = $conn->query("SELECT * FROM project_applications WHERE user_id = $user_id AND project_id = $project_id");

        if ($existing_application->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO project_applications (user_id, project_id, status) VALUES (?, ?, 'pending')");
            $stmt->bind_param("ii", $user_id, $project_id);
            $stmt->execute();
            $stmt->close();

            echo "<script>
                    alert('You have successfully applied for the project.');
                  </script>";
        } else {
            echo "<script>alert('You have already applied for this project.');</script>";
        }
    }
}

$projects = $conn->query("
    SELECT DISTINCT projects.*, 
           users.username AS team_lead_name, 
           GROUP_CONCAT(programming_languages.name) AS language_names,
           -- Подсчет совпадений языков проекта и пользователя
           (SELECT COUNT(*) 
            FROM user_skills us
            WHERE us.user_id = $user_id 
              AND us.language_id IN (SELECT pl.language_id FROM project_languages pl WHERE pl.project_id = projects.id)
           ) AS matching_languages_count,
           -- Подсчет количества заявок у тимлида
           (SELECT COUNT(*) 
            FROM project_applications pa
            JOIN projects p ON pa.project_id = p.id
            WHERE p.team_lead_id = projects.team_lead_id
           ) AS team_lead_level
    FROM projects
    JOIN users ON projects.team_lead_id = users.id
    LEFT JOIN project_applications pa ON pa.project_id = projects.id AND pa.user_id = $user_id
    LEFT JOIN project_languages pl ON pl.project_id = projects.id
    LEFT JOIN programming_languages ON pl.language_id = programming_languages.id
    WHERE pa.user_id IS NULL
    GROUP BY projects.id
    -- Сначала сортируем по количеству совпадений языков, затем по уровню тимлида, затем по дате создания проекта
    ORDER BY matching_languages_count DESC, team_lead_level DESC, projects.created_at DESC
");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Projects</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div>i</div>
    <div>i</div>
    <div>i</div>
    <h2>Available Projects</h2>
    <table class="task-table">
        <tr>
            <th>Project Title</th>
            <th>Description</th>
            <th>Team Lead</th> 
            <th>Programming Languages</th> <!-- Изменён заголовок -->
            <th>Actions</th>
        </tr>
        <?php while ($project = $projects->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($project['title']) ?></td>
                <td><?= htmlspecialchars($project['description']) ?></td>
                <td><?= htmlspecialchars($project['team_lead_name']) ?></td>
                <td><?= htmlspecialchars($project['language_names']) ?></td> <!-- Отображение всех языков -->
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="apply_project_id" value="<?= $project['id'] ?>">
                        <input type="submit" value="Apply">
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
