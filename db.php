<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "task_manager_2";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    
} catch (Exception $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    exit;
}
?>
