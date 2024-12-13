<?php
function registerUser($conn, $username, $password, $confirm_password, $skills)
{
    // Проверка на пробелы в имени пользователя и пароле
    if (strpos($username, ' ') !== false) {
        return 'Username cannot contain spaces!';
    } elseif ($password !== $confirm_password) {
        return 'Passwords do not match!';
    } elseif (empty($skills)) {
        return 'Please select at least one skill!';
    }

    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Начало транзакции
        $conn->begin_transaction();

        // Добавление пользователя
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute() === TRUE) {
            $user_id = $conn->insert_id; // Получаем ID нового пользователя

            // Добавление навыков пользователя
            $skill_stmt = $conn->prepare("INSERT INTO user_skills (user_id, language_id) VALUES (?, ?)");
            foreach ($skills as $skill_id) {
                // Для каждого навыка передаем его в подготовленный запрос
                $skill_stmt->bind_param("ii", $user_id, $skill_id);
                $skill_stmt->execute();
            }

            // Подтверждение транзакции
            $conn->commit();

            return 'Registration successful!';
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        $conn->rollback();
        return 'Error registering user: ' . $e->getMessage();
    }
}

?>
