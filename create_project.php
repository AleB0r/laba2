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
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $programming_language_ids = $_POST['programming_language_ids'];
    $place = $_POST['place']; 

    // Проверки для title и description
    if (strlen($title) < 5 || empty(trim($title))) {
        echo "<script>alert('Project title must be at least 5 characters long and not consist of only spaces.');</script>";
    } elseif (strlen($description) < 10) {
        echo "<script>alert('Description must be at least 10 characters long.');</script>";
    } else {

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));


        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['logo']['tmp_name']);
        finfo_close($finfo);


        if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            echo "<script>alert('File is no image');</script>";
            echo '<script>window.location.href="create_project.php";</script>';
            exit();
        }

        
        $maxFileSize = 2 * 1024 * 1024;
        $sizeOk = true;
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] != UPLOAD_ERR_OK) {
            echo "<script>alert('There was an error uploading the file.');</script>";
            echo '<script>window.location.href="create_project.php";</script>';
            exit();
        }        

        
        
        $imageInfo = getimagesize($_FILES['logo']['tmp_name']);
        if ($imageInfo === false) {
            echo "<script>alert('File is corrupted.');</script>";
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
            $targetDir = "uploads/logos/";
            $csvDir = 'uploads/csv_files/';
            // Если папка не существует, создаем её
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (!is_dir($csvDir)) {
                mkdir($csvDir, 0777, true);
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

            if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                $csvTmpName = $_FILES['csv_file']['tmp_name'];
                $csvOriginalName = $_FILES['csv_file']['name'];
                $csvExtension = strtolower(pathinfo($csvOriginalName, PATHINFO_EXTENSION));
        
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $csvMimeType = finfo_file($finfo, $csvTmpName);
                finfo_close($finfo);
        
                $allowedCsvExtensions = ['csv'];
                if (!in_array($csvExtension, $allowedCsvExtensions) || !in_array($csvMimeType, ['text/csv', 'application/csv'])) {
                    echo "<script>alert('File is not a valid CSV');</script>";
                    echo '<script>window.location.href="create_project.php";</script>';
                    exit();
                }
        
                $csvNewName = generateUniqueFileName($csvDir, $csvExtension);
                
        
                // Перемещение файла в директорию
                if (move_uploaded_file($csvTmpName, $csvNewName)) {
                    echo "CSV file uploaded successfully: " . $csvNewName . "<br>";
                } else {
                    echo "Error uploading CSV file.<br>";
                }
            } else {
                echo "Error uploading CSV file.<br>";
            }

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

</head>
<body>
    <div>i</div>
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
            <!-- Options for places -->
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

        <label for="csv_file">Upload CSV File:</label>
<input type="file" name="csv_file" accept=".csv">


        <button type="submit">Create Project</button>
    </form>
</body>
<script>
function validateForm() {
    var title = document.forms["projectForm"]["title"].value;
    var description = document.forms["projectForm"]["description"].value;
    var logo = document.forms["projectForm"]["logo"].files[0];
    var csvFile = document.forms["projectForm"]["csv_file"].files[0];

    if (title.length < 5 || title.trim().length === 0) {
        alert('Project title must be at least 5 characters and cannot be empty.');
        return false;
    }

    if (description.length < 10) {
        alert('Description must be at least 10 characters.');
        return false;
    }

    if (!logo) {
        alert('Please upload a logo for the project.');
        return false;
    }

    if (!isFileAccessible(logo)) {
        alert('Logo file is inaccessible. Please check the file or device connection.');
        return false;
    }

    if (csvFile) {
        if (!isValidCSV(csvFile)) {
            alert('The uploaded file must be a valid CSV file.');
            return false;
        }
        
        if (!checkDelimiter(csvFile)) {
            alert('The CSV file must use the correct delimiter (e.g., comma or semicolon).');
            return false;
        }

        if (!checkQuotes(csvFile)) {
            alert('The CSV file contains incorrect quote escaping.');
            return false;
        }

        if (!checkRowCount(csvFile)) {
            alert('The CSV file has rows with an incorrect number of columns.');
            return false;
        }

        if (!checkEmptyRows(csvFile)) {
            alert('The CSV file contains empty rows.');
            return false;
        }

        if (!validateCSVData(csvFile)) {
            alert('The CSV file contains invalid data (e.g., non-numeric values in numeric columns).');
            return false;
        }
    }

    return true;
}

function isFileAccessible(file) {
    try {
        const fileLastModified = file.lastModified;
        const fileSize = file.size;
        const fileName = file.name;

        return fileLastModified && fileSize && fileName;
    } catch (error) {
        return false;
    }
}

function isValidCSV(file) {
    const allowedExtension = 'csv';
    const fileExtension = file.name.split('.').pop().toLowerCase();

    if (fileExtension !== allowedExtension) {
        return false;  
    }

    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('CSV file size must be less than 5 MB.');
        return false;
    }

    return true;
}

function checkDelimiter(file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const content = event.target.result;
            if (!content.includes(',')) {
                alert('The CSV file should contain commas as delimiters.');
                return false;
            }
        };
        reader.readAsText(file);
    }
    return true;
}

function checkQuotes(file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const content = event.target.result;
            if (!content.includes('"')) {
                alert('The CSV file must properly escape quotes.');
                return false;
            }
        };
        reader.readAsText(file);
    }
    return true;
}

function checkRowCount(file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const content = event.target.result;
            const rows = content.split('\n');
            const headerColumns = rows[0].split(',');
            rows.forEach(row => {
                if (row.split(',').length !== headerColumns.length) {
                    alert('One or more rows have an incorrect number of columns.');
                    return false;
                }
            });
        };
        reader.readAsText(file);
    }
    return true;
}

function checkEmptyRows(file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const content = event.target.result;
            const rows = content.split('\n');
            rows.forEach(row => {
                if (row.trim() === '') {
                    alert('The CSV file contains empty rows.');
                    return false;
                }
            });
        };
        reader.readAsText(file);
    }
    return true;
}

function validateCSVData(file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const content = event.target.result;
            const rows = content.split('\n');
            rows.forEach(row => {
                const columns = row.split(',');
                if (isNaN(columns[0])) {
                    alert('Invalid data found: the first column should contain numbers.');
                    return false;
                }
            });
        };
        reader.readAsText(file);
    }
    return true;
}
</script>
</html>