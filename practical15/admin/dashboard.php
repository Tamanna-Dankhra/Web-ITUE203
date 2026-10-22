
<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied. Admins only.");
}

$students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()["total"];
$events = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()["total"];
$registrations = $conn->query("SELECT COUNT(*) AS total FROM registrations")->fetch_assoc()["total"];

$latest = $conn->query(
    "SELECT * FROM registrations ORDER BY 1 DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Hub Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 25px; background: #EEF3FF; }
        h1 { color: #3B5BDB; }
        .cards { display: flex; flex-wrap: wrap; gap: 20px; margin: 25px 0; }
        .card { background: white; padding: 25px; border-radius: 12px; flex: 1; min-width: 160px; box-shadow: 0 3px 12px #0001; }
        .card h2 { margin-top: 0; font-size: 18px; }
        .number { font-size: 32px; font-weight: bold; color: #3B5BDB; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #3B5BDB; color: white; }
        .top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        a { color: #3B5BDB; }
    </style>
</head>
<body>
    <div class="top">
        <h1>Student Hub Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "Admin") ?>!</p>
    </div>

    <div class="cards">
        <div class="card">
            <h2>Total Students</h2>
            <div class="number"><?= $students ?></div>
        </div>
        <div class="card">
            <h2>Total Events</h2>
            <div class="number"><?= $events ?></div>
        </div>
        <div class="card">
            <h2>Total Registrations</h2>
            <div class="number"><?= $registrations ?></div>
        </div>
    </div>

    <h2>Latest Registrations</h2>
    <table>
        <thead>
            <tr>
                <?php
                if ($latest && $latest->num_rows > 0) {
                    $fields = $latest->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    echo "</tr></thead><tbody>";
                    $latest->data_seek(0);
                    while ($row = $latest->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars((string)($value ?? "")) . "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<th>Information</th></tr></thead><tbody>";
                    echo "<tr><td>No registrations found yet.</td></tr>";
                }
                ?>
        </tbody>
    </table>

    <p><a href="../index.php">Back to Home</a> | <a href="../logout.php">Logout</a></p>
</body>
</html>
