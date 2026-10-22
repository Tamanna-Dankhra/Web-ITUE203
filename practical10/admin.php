<?php
require "auth.php";

if ($_SESSION["role"] != "admin") {
    header("Location: student.php");
    exit;
}
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
<p>You are logged in as an administrator.</p>
<a href="logout.php">Logout</a>