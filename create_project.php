<?php
session_start();
include 'db.php'; 
include 'header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

$team_lead_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'], $_POST['description'], $_POST['programming_language_ids'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $programming_language_ids = $_POST['programming_language_ids']; // Array of selected language IDs

    if (strlen($title) < 5 || empty(trim($title))) {
        echo "<script>alert('Project title must be at least 5 characters long and not consist of only spaces.');</script>";
    } elseif (strlen($description) < 10) {
        echo "<script>alert('Description must be at least 10 characters long.');</script>";
    } else {
        $stmt = $conn->prepare("SELECT id FROM projects WHERE title = ?");
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('A project with this title already exists. Please choose a different title.');</script>";
        } else {
            $stmt->close();
            $conn->begin_transaction();
            try {
                // Insert project
                $stmt = $conn->prepare("INSERT INTO projects (title, description, team_lead_id) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $title, $description, $team_lead_id);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $project_id = $stmt->insert_id;
                $stmt->close();

                // Insert selected programming languages for the project
                $stmt = $conn->prepare("INSERT INTO project_languages (project_id, language_id) VALUES (?, ?)");
                foreach ($programming_language_ids as $language_id) {
                    $stmt->bind_param("ii", $project_id, $language_id);
                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }
                }
                $stmt->close();

                $conn->commit();
                echo "<script>alert('Project created successfully.'); window.location.href = 'team_lead.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<script>alert('Error creating project: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }
}

$languages = $conn->query("SELECT * FROM programming_languages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project</title>
    <link rel='stylesheet' href='css/add_style.css'>
    <link rel='stylesheet' href='css/style.css'>
    <style>
        /* Внутри add_style.css или прямо в <style> секции */
select[name="programming_language_ids[]"] {
    width: 100%;
    height: 150px; /* Высота для мультивыбора */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    background-color: #f9f9f9;
    color: #333;
    box-sizing: border-box;
}

select[name="programming_language_ids[]"] option {
    padding: 5px;
}

/* Стилизация при фокусе */
select[name="programming_language_ids[]"]:focus {
    outline: none;
    border-color: #007bff; /* Цвет при фокусе */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

    </style>
    <script>
        function validateForm() {
            const title = document.forms["projectForm"]["title"].value.trim();
            const description = document.forms["projectForm"]["description"].value.trim();

            if (title.length < 5 || title.replace(/\s+/g, '').length === 0) {
                alert("Project title must be at least 5 characters long and not consist of only spaces.");
                return false;
            }

            if (description.length < 10) {
                alert("Description must be at least 10 characters long.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div>i</div>
    <div>i</div>
    <div>i</div>
    <h2>Create New Project</h2>
    <form name="projectForm" method="post" onsubmit="return validateForm()">
        <label for="title">Project Title:</label>
        <input type="text" name="title" required>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="programming_language_ids">Programming Languages:</label>
        <select name="programming_language_ids[]" multiple required>
            <?php while ($language = $languages->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($language['id']) ?>"><?= htmlspecialchars($language['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Create Project</button>
    </form>
</body>
</html>
