<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$assignment_id = $_GET['assignment_id'];
$teacher_id = $_SESSION['user_id'];

// Fetch assignment details
$sql = "SELECT title FROM assignments WHERE id = ? AND teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignment_id, $teacher_id);
$stmt->execute();
$stmt->bind_result($title);
$stmt->fetch();
$stmt->close();

// Fetch submissions for the assignment
$sql = "SELECT s.id, u.username, s.file_path, s.submitted_at, s.grade, s.feedback
        FROM submissions s
        JOIN users u ON s.student_id = u.id
        WHERE s.assignment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Submissions for "<?php echo htmlspecialchars($title); ?>"</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <?php if (!empty($submissions)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Submitted At</th>
                        <th>File</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($submission['username']); ?></td>
                            <td><?php echo htmlspecialchars($submission['submitted_at']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">View</a></td>
                            <td><?php echo $submission['grade'] ? htmlspecialchars($submission['grade']) : 'N/A'; ?></td>
                            <td><?php echo $submission['feedback'] ? htmlspecialchars($submission['feedback']) : 'N/A'; ?></td>
                            <td>
                                <form action="grade_submission.php" method="post">
                                    <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                    <input type="number" name="grade" placeholder="Grade" step="0.01" min="0" max="100" required>
                                    <input type="text" name="feedback" placeholder="Feedback">
                                    <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No submissions for this assignment yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>