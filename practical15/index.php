
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION["role"] ?? "") === "admin") {
    header("Location: admin/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Hub Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Student Hub Portal</h1>

        <p>
            Hello,
            <?= htmlspecialchars($_SESSION["name"] ?? "Student") ?>!
        </p>

        <p>Access your student information and portal features here.</p>

        <a href="students.php">Students</a>
        <a href="events.php">Events</a>
        <a href="notices.php">Notices</a>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
