<?php
require "auth.php";

if ($_SESSION["role"] != "student") {
    header("Location: admin.php");
    exit;
}
?>

<h2>Student Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
<p>You are logged in as a student.</p>
<a href="logout.php">Logout</a>