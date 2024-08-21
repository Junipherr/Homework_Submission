<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = '<br><p style="font-size:10px;">Username already taken. Please choose another one.</p>';
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Error: " . $stmt->error;
        }
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
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card {
    height: 400px;
  }
  .hh{
    height: 100px;
    width: 200px;
  }
    </style>
</head>
<body>
    <div class="container mt-5 col-7">
        <div class="row justify-content-center">
        <div class="col-md-6">
        <div class="card">
            <div align="center" class="card-header ">
                
        <h3>Register</h3>
        <?php if (isset($error)) { echo "<div class='alert alert-danger hh'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='alert alert-success hh'>$success</div>"; } ?>
        
        </div>
        
        <div class="card-body">
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
                <br>
                <button type="submit" class="btn btn-primary">Register</button>
            </div><br>
            
            <br>
           
        </form>
        </div>
</div>
</div>
        </div>
    </div>
</body>
</html>