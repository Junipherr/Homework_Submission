<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Fetch user data
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Fetch assignments for teachers
if ($user_role == 'teacher') {
    $sql = "SELECT * FROM assignments WHERE teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch assignments and submissions for students
if ($user_role == 'student') {
    $sql = "SELECT a.id, a.title, a.due_date, s.id AS submission_id, s.grade, s.feedback 
            FROM assignments a 
            LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
            ORDER BY a.due_date";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-danger {
            background-color: #f8d7da;
        }
        .btn-upload {
            font-size: 0.875rem;
        }
        .alert-warning {
            font-size: 1.25rem;
            margin-bottom: 20px;
        }
        .dashboard-header {
            margin-bottom: 20px;
        }
        .btn-logout {
            float: right;
        }
        .due-soon {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="dashboard-header">Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        
        <!-- Logout Button -->
        <a href="logout.php" class="btn btn-danger btn-logout">Logout</a>

        <!-- For Teachers -->
        <?php if ($user_role == 'teacher'): ?>
            <h3>Your Assignments</h3>
            <a href="create_assignment.php" class="btn btn-primary mb-3">Create New Assignment</a>
            <?php if (!empty($assignments)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Submissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                <td>
                                    <a href="view_submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-info btn-sm">View Submissions</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not created any assignments yet.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- For Students -->
        <?php if ($user_role == 'student'): ?>
            <h3>Your Assignments</h3>
            <?php if (!empty($assignments)): ?>
                <?php
                $today = new DateTime();
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Feedback</th>
                            <th>Submit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <?php
                            $due_date = new DateTime($assignment['due_date']);
                            $is_overdue = $today > $due_date;
                            $due_soon = $today->diff($due_date)->days <= 3 && !$is_overdue;
                            ?>
                            <tr class="<?php echo $is_overdue ? 'table-danger' : ($due_soon ? 'due-soon' : ''); ?>">
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                <td><?php echo $assignment['submission_id'] ? 'Submitted' : ($is_overdue ? 'Overdue' : 'Pending'); ?></td>
                                <td><?php echo $assignment['grade'] ? htmlspecialchars($assignment['grade']) : 'N/A'; ?></td>
                                <td><?php echo $assignment['feedback'] ? htmlspecialchars($assignment['feedback']) : 'N/A'; ?></td>
                                <td>
                                    <?php if (!$assignment['submission_id'] && !$is_overdue): ?>
                                        <form action="submit_assignment.php" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                            <input type="file" name="file" required>
                                            <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No assignments assigned yet.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>