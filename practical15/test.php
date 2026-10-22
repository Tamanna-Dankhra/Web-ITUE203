
<?php
session_start();
require_once "config.php";

$checks = [
    "Database connection" => true,
    "Users table" => false,
    "Students table" => false,
    "Events table" => false,
    "Registrations table" => false
];

$tables = [
    "Users table" => "users",
    "Students table" => "students",
    "Events table" => "events",
    "Registrations table" => "registrations"
];

foreach ($tables as $label => $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $checks[$label] = $result->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Hub Test Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Student Hub Integration Tests</h1>

    <?php foreach ($checks as $label => $passed): ?>
        <p>
            <?= htmlspecialchars($label) ?>:
            <strong>
                <?= $passed ? "PASS" : "FAIL" ?>
            </strong>
        </p>
    <?php endforeach; ?>

    <p>Complete the remaining manual tests before submission.</p>
</div>
</body>
</html>
