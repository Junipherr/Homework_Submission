<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];
    $submitted_at = date('Y-m-d H:i:s');

    // File upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $upload_ok = 1;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $upload_ok = 0;
    }

    // Check file size (5MB limit)
    if ($_FILES["file"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $upload_ok = 0;
    }

    // Allow certain file formats (e.g., pdf, doc, docx)
    if ($file_type != "pdf" && $file_type != "doc" && $file_type != "docx") {
        echo "Sorry, only PDF, DOC & DOCX files are allowed.";
        $upload_ok = 0;
    }

    if ($upload_ok == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO submissions (assignment_id, student_id, file_path, submitted_at) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $assignment_id, $student_id, $target_file, $submitted_at);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close();
}
?>