<?php
// Проверка на тему из куков
// Отключить отображение ошибок
ini_set('display_errors', 0);  // Это отключает отображение ошибок
error_reporting(0);  // Это полностью отключает все уровни ошибок
$theme = 'light'; // по умолчанию светлая тема

// Декодируем тему из куки, если она существует
if (isset($_COOKIE['theme'])) {
    $decoded_theme = base64_decode($_COOKIE['theme']);
    if ($decoded_theme === 'dark') {
        $theme = 'dark';
    }
}

// Устанавливаем класс для тела документа в зависимости от темы
$theme_class = ($theme === 'dark') ? 'dark-theme' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Title</title>
    <link rel="stylesheet" href="css/header.css">
    <style>
        /* Стили для светлой темы */
        body {
            background-color: #ffffff;
            color: #000000;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Стили для темной темы */
        .dark-theme {
            background-color: #121212 !important;
            color: #ffffff !important;
        }

        .dark-theme a {
            color: #ffffff !important;
        }

        .dark-theme nav {
            background-color: #333333 !important;
        }

        /* Переключатель темы */
        .theme-toggle {
            cursor: pointer;
            padding: 5px 10px;
            background-color: #007bff !important;
            color: white !important;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .theme-toggle:hover {
            background-color: #0056b3 !important;
        }

        /* Стиль кнопок в темной теме */
        .dark-theme .button {
            background-color: #444444 !important;
            color: #ffffff !important;
            border: 1px solid #666666 !important;
        }

        .dark-theme .button:hover {
            background-color: #555555 !important;
            border-color: #888888 !important;
        }

        /* Ссылки в темной теме */
        .dark-theme a {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .dark-theme a:hover {
            text-decoration: underline !important;
        }

        /* Заголовки в темной теме */
        .dark-theme h1, .dark-theme h2, .dark-theme h3 {
            color: #ffffff !important;
        }

        /* Таблицы в темной теме */
        .dark-theme table {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        .dark-theme table th, .dark-theme table td {
            border: 1px solid #444444 !important;
            padding: 8px !important;
            text-align: left !important;
        }

        .dark-theme table th {
            background-color: #333333 !important;
        }

        .dark-theme table tr:nth-child(even) {
            background-color: #2a2a2a !important;
        }

        /* Формы и инпуты в темной теме */
        .dark-theme input, .dark-theme select, .dark-theme textarea {
            background-color: #333333 !important;
            color: #ffffff !important;
            border: 1px solid #666666 !important;
            padding: 10px !important;
            border-radius: 5px !important;
        }

        .dark-theme input:hover, .dark-theme select:hover, .dark-theme textarea:hover {
            background-color: #444444 !important;
        }

        /* Фон для навигации */
        .dark-theme nav {
            background-color: #222222 !important;
        }

        /* Стиль для профиля пользователя */
        .dark-theme .profile {
            background-color: #333333 !important;
            color: #ffffff !important;
        }

        /* Основной контейнер для страниц */
        .dark-theme .container {
            background-color: #181818 !important;
            color: #ffffff !important;
        }

        /* Стили для контейнера task-manager-container в темной теме */
.dark-theme .task-manager-container {
    background-color: #222222 !important;
    color: #ffffff !important;
    padding: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
    margin-top: 20px !important;
}

.dark-theme .task-manager-container h2 {
    color: #ffffff !important;
}

.dark-theme .task-manager-container .task-item {
    background-color: #333333 !important;
    color: #ffffff !important;
    border-radius: 4px !important;
    padding: 10px !important;
    margin-bottom: 10px !important;
}

.dark-theme .task-manager-container .task-item:hover {
    background-color: #444444 !important;
}

/* Стили для формы projectForm в темной теме */
.dark-theme .projectForm {
    background-color: #333333 !important;
    color: #ffffff !important;
    padding: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
    width: 100% !important;
    max-width: 600px !important;
    margin: 0 auto !important;
}

.dark-theme .projectForm label {
    font-weight: bold !important;
    color: #ffffff !important;
}

.dark-theme .projectForm input,
.dark-theme .projectForm select,
.dark-theme .projectForm textarea {
    background-color: #444444 !important;
    color: #ffffff !important;
    border: 1px solid #666666 !important;
    padding: 10px !important;
    border-radius: 5px !important;
    width: 100% !important;
    margin-bottom: 10px !important;
}

.dark-theme .projectForm input[type="submit"],
.dark-theme .projectForm button {
    background-color: #007bff !important;
    color: white !important;
    padding: 10px 20px !important;
    border: none !important;
    border-radius: 5px !important;
    cursor: pointer !important;
    display: block !important;
    width: 100% !important;
}

.dark-theme .projectForm input[type="submit"]:hover,
.dark-theme .projectForm button:hover {
    background-color: #0056b3 !important;
}

.dark-theme .projectForm input[type="submit"]:disabled,
.dark-theme .projectForm button:disabled {
    background-color: #666666 !important;
    cursor: not-allowed !important;
}
.dark-theme .form1 {
    background-color: #666666 !important;
    cursor: not-allowed !important;
}

.dark-theme.task-table th {
    background-color: black !important;
}

.dark-theme.task-table tr:nth-child(even) {
    background-color: black !important;
}

.dark-theme.task-table tr:hover {
    background-color: black !important;
}
.dark-theme .task-table th {
    background-color: black !important;
}

.dark-theme .task-table tr:nth-child(even) {
    background-color: black !important;
}

/* Убираем изменение фона при наведении на строку */
.dark-theme .task-table tr:hover {
    background-color: transparent !important;
}
    </style>
</head>
<body class="<?= $theme_class ?>">

<header>
    <nav>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'admin'): ?>
            <a href="admin.php">Home</a>
            <a href="view_languages.php">View Programming Languages</a>
            <a href="add_language.php">Add New Language</a>
            <a href="admin_weights.php">Update weights</a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'team_lead'): ?>
            <a href="team_lead.php">Home</a>
            <a href="create_project.php">Create New Project</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'): ?>
            <a href="index.php">Home</a>
            <a href="all_projects.php">Projects</a>
            <a href="my_applications.php">My Applications</a> 
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
            <span>Hello, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
        
        <!-- Переключатель темы -->
        <button class="theme-toggle" id="theme-toggle">
            Switch to <?= $theme === 'light' ? 'Dark' : 'Light' ?> Theme
        </button>
    </nav>
</header>

<script>
    const themeToggle = document.getElementById('theme-toggle');
    const currentTheme = '<?= $theme ?>'; // Получаем текущую тему с PHP

    // Слушатель клика для кнопки переключения темы
    themeToggle.addEventListener('click', function() {
        let newTheme = currentTheme === 'light' ? 'dark' : 'light';
        // Кодируем тему в base64
        document.cookie = "theme=" + btoa(newTheme) + ";path=/;max-age=" + (60 * 60 * 24 * 365);

        // Перезагружаем страницу, чтобы применить новую тему
        location.reload();
    });
</script>
</body>
</html>
