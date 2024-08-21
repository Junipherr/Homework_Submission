<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    $sql = "UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsi", $grade, $feedback, $submission_id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
