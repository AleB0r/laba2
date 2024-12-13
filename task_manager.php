<?php
// task_manager.php

function addTask($conn, $title, $description, $due_date, $reminder_time, $user_id) {
    $current_datetime = new DateTime();
    $due_datetime = new DateTime($due_date . ' ' . $reminder_time);

    if ($due_datetime < $current_datetime) {
        return "Error: The due date and reminder time must be in the future.";
    } else {
        try {
            $status = 'pending';

            // Проверка уникальности названия задачи
            $checkSql = "SELECT COUNT(*) FROM tasks WHERE title = ? AND user_id = ?";
            $stmt = $conn->prepare($checkSql);
            $stmt->bind_param('si', $title, $user_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                return "Error: The title must be unique.";
            } else {
                // Добавление задачи в базу данных
                $sql = "INSERT INTO tasks (title, description, due_date, reminder_time, status, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssi', $title, $description, $due_date, $reminder_time, $status, $user_id);

                if ($stmt->execute()) {
                    return "Task created successfully.";
                } else {
                    throw new Exception($stmt->error);
                }
            }
        } catch (Exception $e) {
            return "Error adding task: " . $e->getMessage();
        }
    }
}
?>
