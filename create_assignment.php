<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $teacher_id = $_SESSION['user_id'];

    $sql = "INSERT INTO assignments (title, description, due_date, teacher_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $due_date, $teacher_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Create Assignment</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <form action="create_assignment.php" method="post">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
</body>
</html>