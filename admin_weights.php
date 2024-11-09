<?php
session_start();
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

$weights = $conn->query("SELECT * FROM rating_weights LIMIT 1")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $weight_accepted = floatval($_POST['weight_accepted']);
    $weight_rejected = floatval($_POST['weight_rejected']);
    $weight_skills = floatval($_POST['weight_skills']);

    $stmt = $conn->prepare("UPDATE rating_weights SET weight_accepted = ?, weight_rejected = ?, weight_skills = ? WHERE id = ?");
    $stmt->bind_param("dddi", $weight_accepted, $weight_rejected, $weight_skills, $weights['id']);
    if ($stmt->execute()) {
        echo "<script>alert('Weights updated successfully.');</script>";
        header("Refresh:0"); 
        exit();
    } else {
        echo "<script>alert('Error updating weights.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Rating Weights</title>
</head>
<body>
    <h2>Update Rating Weights</h2>
    <form method="post">
        <label for="weight_accepted">Weight for Accepted Applications:</label>
        <input type="number" step="0.1" name="weight_accepted" value="<?= htmlspecialchars($weights['weight_accepted']) ?>" required>

        <label for="weight_rejected">Weight for Rejected Applications:</label>
        <input type="number" step="0.1" name="weight_rejected" value="<?= htmlspecialchars($weights['weight_rejected']) ?>" required>

        <label for="weight_skills">Weight for Skill Matches:</label>
        <input type="number" step="0.1" name="weight_skills" value="<?= htmlspecialchars($weights['weight_skills']) ?>" required>

        <button type="submit">Update Weights</button>
    </form>
</body>
</html>