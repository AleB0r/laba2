<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

$project_id = $_GET['project_id'];

$project = $conn->query("SELECT * FROM projects WHERE id = $project_id AND team_lead_id = {$_SESSION['user_id']}");
if ($project->num_rows == 0) {
    echo "<script>alert('Unauthorized access.'); window.location.href = 'team_lead_dashboard.php';</script>";
    exit();
}
$project_languages = $conn->query("SELECT pl.language_id, pl.project_id, p.name AS language_name 
                                     FROM project_languages pl 
                                     JOIN programming_languages p ON pl.language_id = p.id 
                                     WHERE pl.project_id = $project_id");

$start_time = microtime(true);
$start_memory = memory_get_usage();

$applications = $conn->query("
    SELECT pa.*, 
       u.username, 
       GROUP_CONCAT(s.name) AS skills,
       (SELECT COUNT(*) FROM project_applications WHERE user_id = u.id AND status = 'approved') AS approved_count,
       (SELECT COUNT(*) FROM project_applications WHERE user_id = u.id AND status = 'rejected') AS rejected_count,
       (SELECT weight_accepted FROM rating_weights LIMIT 1) AS weight_accepted,
       (SELECT weight_rejected FROM rating_weights LIMIT 1) AS weight_rejected,
       (SELECT weight_skills FROM rating_weights LIMIT 1) AS weight_skills,
       (SELECT weight_popular_language FROM rating_weights LIMIT 1) AS weight_popular_language,
       
       (
           COALESCE((SELECT COUNT(*) FROM project_applications WHERE user_id = u.id AND status = 'approved'), 0) / 
           NULLIF((SELECT COUNT(*) AS total_count FROM project_applications WHERE user_id = u.id), 0) * 
           (SELECT weight_accepted FROM rating_weights LIMIT 1) -
           COALESCE((SELECT COUNT(*) FROM project_applications WHERE user_id = u.id AND status = 'rejected'), 0) / 
           NULLIF((SELECT COUNT(*) AS total_count FROM project_applications WHERE user_id = u.id), 0) * 
           (SELECT weight_rejected FROM rating_weights LIMIT 1) +
           COALESCE((SELECT COUNT(DISTINCT us.language_id) 
                     FROM user_skills us
                     WHERE us.user_id = u.id 
                       AND us.language_id IN (
                           SELECT pl.language_id 
                           FROM project_languages pl 
                           WHERE pl.project_id = $project_id
                       )
           ), 0) / NULLIF((SELECT COUNT(*) FROM project_languages WHERE project_id = $project_id), 0) * 
           (SELECT weight_skills FROM rating_weights LIMIT 1) +
           
           CASE
               WHEN (SELECT language_id 
                     FROM project_languages pl
                     JOIN projects p ON p.id = pl.project_id
                     WHERE p.team_lead_id = (SELECT team_lead_id FROM projects WHERE id = $project_id)
                     GROUP BY pl.language_id
                     ORDER BY COUNT(pl.language_id) DESC
                     LIMIT 1) 
                    IN (SELECT language_id FROM project_languages WHERE project_id = $project_id) 
                    AND (SELECT COUNT(*) FROM user_skills us 
                         WHERE us.user_id = u.id 
                           AND us.language_id = (SELECT language_id 
                                                 FROM project_languages pl
                                                 JOIN projects p ON p.id = pl.project_id
                                                 WHERE p.team_lead_id = (SELECT team_lead_id FROM projects WHERE id = $project_id)
                                                 GROUP BY pl.language_id
                                                 ORDER BY COUNT(pl.language_id) DESC
                                                 LIMIT 1)) > 0
               THEN (SELECT weight_popular_language FROM rating_weights LIMIT 1)
               ELSE 0
           END
       ) AS rating,
       
       (
           SELECT COUNT(*) 
           FROM user_skills us
           WHERE us.user_id = u.id 
             AND us.language_id IN 
               (SELECT pl.language_id FROM project_languages pl WHERE pl.project_id = $project_id)
       ) AS matching_languages,
       
       (SELECT COUNT(*) FROM project_languages WHERE project_id = $project_id) AS required_languages
       
FROM project_applications pa
JOIN users u ON pa.user_id = u.id
LEFT JOIN user_skills us ON us.user_id = u.id
LEFT JOIN programming_languages s ON us.language_id = s.id
WHERE pa.project_id = $project_id
GROUP BY u.id
ORDER BY rating DESC;
");

$end_time = microtime(true);
$calculation_time = $end_time - $start_time;
$end_memory = memory_get_usage();
$memory_usage = $end_memory - $start_memory;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project Applications</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .view-applications {
            color: #ffffff; 
            background-color: #007bff; 
            padding: 8px 12px;
            text-decoration: none; 
            border-radius: 5px; 
            transition: background-color 0.3s; 
        }

        .view-applications:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<script>
    alert("Время выполнения расчёта: <?= number_format($calculation_time, 4) ?> секунд\n" +
        "Использовано памяти: <?= number_format($memory_usage / 1024, 2) ?> КБ\n");
</script>
<div>i</div>
<div>i</div>
<div>i</div>
<h2>Applications for Project: <?= htmlspecialchars($project->fetch_assoc()['title']) ?></h2>
<h3>Programming Languages for this Project:</h3>
    <ul>
        <?php while ($lang = $project_languages->fetch_assoc()): ?>
            <li><?= htmlspecialchars($lang['language_name']) ?></li>
        <?php endwhile; ?>
    </ul>

<table class="task-table">
    <tr>
        <th>Username</th>
        <th>Skills</th>
        <th>Status</th>
        <th>Rating</th>
        <th>Actions</th>
    </tr>
    <?php while ($application = $applications->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($application['username']) ?></td>
            <td><?= htmlspecialchars($application['skills']) ?></td>
            <td><?= htmlspecialchars($application['status']) ?></td>
            <td><?= htmlspecialchars(number_format($application['rating'], 2)) ?></td>
            <td>
                <form action="approve_application.php" method="post" style="display:inline;">
                    <input type="hidden" name="application_id" value="<?= htmlspecialchars($application['id']) ?>">
                    <button class="view-applications" type="submit">Approve</button>
                </form>
                <form action="reject_application.php" method="post" style="display:inline;">
                    <input type="hidden" name="application_id" value="<?= htmlspecialchars($application['id']) ?>">
                    <button class="view-applications" type="submit">Reject</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
