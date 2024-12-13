<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

$project_id = $_GET['project_id'];

// Проверяем, является ли проект доступным для тимлида
$project = $conn->query("SELECT * FROM projects WHERE id = $project_id AND team_lead_id = {$_SESSION['user_id']}");
if ($project->num_rows == 0) {
    echo "<script>alert('Unauthorized access.'); window.location.href = 'team_lead_dashboard.php';</script>";
    exit();
}

$project_languages = $conn->query("SELECT pl.language_id, pl.project_id, p.name AS language_name 
                                     FROM project_languages pl 
                                     JOIN programming_languages p ON pl.language_id = p.id 
                                     WHERE pl.project_id = $project_id");

$applications = $conn->query("
    SELECT pa.*, 
           u.username, 
           GROUP_CONCAT(s.name) AS skills
    FROM project_applications pa
    JOIN users u ON pa.user_id = u.id
    LEFT JOIN user_skills us ON us.user_id = u.id
    LEFT JOIN programming_languages s ON us.language_id = s.id
    WHERE pa.project_id = $project_id
    GROUP BY u.id
");

// Получение весов для рейтинга
$weights = $conn->query("SELECT * FROM rating_weights LIMIT 1")->fetch_assoc();

// Преобразуем заявки в массив и вычисляем рейтинг
$applications_array = [];
while ($application = $applications->fetch_assoc()) {
    $user_id = $application['user_id'];

    $approved_count = $conn->query("SELECT COUNT(*) FROM project_applications WHERE user_id = $user_id AND status = 'approved'")->fetch_row()[0];
    $rejected_count = $conn->query("SELECT COUNT(*) FROM project_applications WHERE user_id = $user_id AND status = 'rejected'")->fetch_row()[0];
    $total_count = $approved_count + $rejected_count;


    $matching_languages = $conn->query("SELECT COUNT(*) 
                                         FROM user_skills us
                                         WHERE us.user_id = $user_id 
                                           AND us.language_id IN 
                                             (SELECT pl.language_id FROM project_languages pl WHERE pl.project_id = $project_id)")
                               ->fetch_row()[0];
    $required_languages = $conn->query("SELECT COUNT(*) FROM project_languages WHERE project_id = $project_id")->fetch_row()[0];

    $popular_language = $conn->query("
        SELECT pl.language_id
        FROM project_languages pl
        JOIN projects p ON p.id = pl.project_id
        WHERE p.team_lead_id = {$_SESSION['user_id']}
        GROUP BY pl.language_id
        ORDER BY COUNT(pl.language_id) DESC
        LIMIT 1
    ")->fetch_row()[0];

    $has_popular_language = $conn->query("
        SELECT COUNT(*)
        FROM user_skills us
        WHERE us.user_id = $user_id AND us.language_id = $popular_language
    ")->fetch_row()[0];


    $rating = 0;
    if ($total_count > 0) {
        $rating += ($approved_count / $total_count) * $weights['weight_accepted'];
        $rating -= ($rejected_count / $total_count) * $weights['weight_rejected'];
    }
    if ($required_languages > 0) {
        $rating += ($matching_languages / $required_languages) * $weights['weight_skills'];
    }
    if ($has_popular_language > 0) {
        $rating += $weights['weight_popular_language'];
    }

    $application['rating'] = $rating;
    $applications_array[] = $application;
}

usort($applications_array, function ($a, $b) {
    return $b['rating'] <=> $a['rating'];
});

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
        <?php foreach ($applications_array as $application): ?>
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
        <?php endforeach; ?>
    </table>
</body>
</html>
