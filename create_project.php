<?php 
session_start();
include 'db.php'; 
include 'header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

$team_lead_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'], $_POST['description'], $_POST['programming_language_ids'], $_FILES['logo'])) {
    // Получаем данные из формы
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $programming_language_ids = $_POST['programming_language_ids'];
    $place = $_POST['place']; // Array of selected language IDs

    // Проверки для title и description
    if (strlen($title) < 5 || empty(trim($title))) {
        echo "<script>alert('Project title must be at least 5 characters long and not consist of only spaces.');</script>";
    } elseif (strlen($description) < 10) {
        echo "<script>alert('Description must be at least 10 characters long.');</script>";
    } else {

        
        // Проверяем, что логотип является изображением и его размер не превышает 2 МБ
        $maxFileSize = 2 * 1024 * 1024;
        $sizeOk = true;
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] != UPLOAD_ERR_OK) {
            echo "<script>alert('There was an error uploading the file.');</script>";
            echo '<script>window.location.href="create_project.php";</script>';
            exit();
        }        
        // Проверяем, является ли файл изображением
        $imageInfo = @getimagesize($_FILES['logo']['tmp_name']);
        if ($imageInfo === false) {
            echo "<script>alert('File is no image.');</script>";
            echo '<script>window.location.href="create_project.php";</script>';
            exit();
            throw new Exception('Uploaded file is not a valid image.');
        }

        if ($_FILES['logo']['size'] > $maxFileSize) {
            echo "<script>alert('File is too big, 2 MB is the maximum limit.');</script>";
            echo '<script>window.location.href="create_project.php";</script>';
                         exit();
            $sizeOk = false;
        }

        if ($sizeOk) {
            // Папка для загрузки логотипов
            $targetDir = "uploads/logos/";

            // Если папка не существует, создаем её
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            

            $fileExtension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);

            
            function generateUniqueFileName($targetDir, $fileExtension) {
                do {
                    $uniqueFileName = uniqid('project_logo_', true) . '.' . $fileExtension;
                    $targetFile = $targetDir . $uniqueFileName;
                } while (file_exists($targetFile));

                return $targetFile;
            }

            // Получаем уникальное имя для файла
            $targetFile = generateUniqueFileName($targetDir, $fileExtension);

            // Перемещаем файл в папку
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                // Теперь у нас есть путь к файлу, сохраняем его в базе данных
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
                        // Вставка проекта в базу данных
                        $stmt = $conn->prepare("INSERT INTO projects (title, description, team_lead_id, place, logo) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssiss", $title, $description, $team_lead_id, $place, $targetFile);
                        if (!$stmt->execute()) {
                            throw new Exception($stmt->error);
                        }
                        $project_id = $stmt->insert_id;
                        $stmt->close();

                        // Вставка выбранных языков программирования для проекта
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
                        echo '<script>window.location.href="create_project.php";</script>';
                         exit();
                    }
                }
            } else {
                echo "<script>alert('Error uploading the logo file.');</script>";
                echo '<script>window.location.href="create_project.php";</script>';
                         exit();
            }
        }
    }
}

$languages = $conn->query("SELECT * FROM programming_languages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel='stylesheet' href='css/style.css'>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Project</title>
    <style>
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
    <h2>Create New Project</h2>
    <form name="projectForm" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">
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

        <label for="place">Place:</label>
        <select name="place" required>
            <option value="Minsk">Minsk</option>
            <option value="Belarus">Belarus</option>
            <option value="Moscow">Moscow</option>
            <option value="Spb">Spb</option>
            <option value="Kazan">Kazan</option>
            <option value="Novosibirsk">Novosibirsk</option>
            <option value="Ekaterinburg">Ekaterinburg</option>
            <option value="Nizhny_novgorod">Nizhny Novgorod</option>
            <option value="Chelyabinsk">Chelyabinsk</option>
            <option value="Samara">Samara</option>
            <option value="Omsk">Omsk</option>
            <option value="Rostov">Rostov</option>
        </select>

        <label for="logo">Upload Project Logo:</label>
        <input type="file" name="logo" accept="image/*" required>

        <button type="submit">Create Project</button>
    </form>
</body>
</html>
