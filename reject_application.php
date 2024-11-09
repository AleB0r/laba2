<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'team_lead') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];

    $stmt = $conn->prepare("
        SELECT project_applications.*, projects.title AS project_title, projects.team_lead_id, project_applications.user_id AS applicant_id 
        FROM project_applications 
        JOIN projects ON project_applications.project_id = projects.id
        WHERE project_applications.id = ? AND projects.team_lead_id = ?
    ");
    $stmt->bind_param("ii", $application_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
        $applicant_id = $application['applicant_id'];
        $project_title = $application['project_title'];

        $update_stmt = $conn->prepare("UPDATE project_applications SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $application_id);
        $update_stmt->execute();

        $notification_message = "Your application for project {$project_title} has been rejected.";
    
            $notification_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notification_stmt->bind_param("is", $applicant_id, $notification_message);
            $notification_stmt->execute();
        

        echo "<script>alert('Application rejected successfully.'); window.location.href = 'view_project.php?project_id={$application['project_id']}';</script>";
    } else {
        echo "<script>alert('Unauthorized access.'); window.location.href = 'team_lead.php';</script>";
    }
} else {
    header('Location: team_lead.php');
}
?>
