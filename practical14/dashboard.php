
<?php
require_once "auth.php";
requireLogin();

$isAdmin = ($_SESSION["role"] === "admin");

function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    return (int)$result->fetch_row()[0];
}

$userCount = getCount($conn, "SELECT COUNT(*) FROM users");
$studentCount = getCount($conn, "SELECT COUNT(*) FROM students");
$eventCount = getCount($conn, "SELECT COUNT(*) FROM events");
$registrationCount = getCount($conn, "SELECT COUNT(*) FROM registrations");

$latest = $conn->query(
    "SELECT r.id, s.name AS student_name, s.email,
            e.title AS event_title, r.registered_at
     FROM registrations r
     JOIN students s ON r.student_id = s.id
     JOIN events e ON r.event_id = e.id
     ORDER BY r.registered_at DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - Practical 14</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
<style>
    body { background: #f2f5fb; }
    .sidebar { min-height: 100vh; background: #24345d; }
    .sidebar a {
        color: white;
        display: block;
        text-decoration: none;
        padding: 12px;
        border-radius: 6px;
    }
    .sidebar a:hover { background: #3b5080; }
    .stat-card { border: 0; border-radius: 12px; }
</style>
</head>
<body>

<div class="container-fluid">
<div class="row">

    <aside class="col-md-3 col-lg-2 sidebar p-3">
        <h4 class="text-white mb-4">Student Hub</h4>

        <a href="dashboard.php">Dashboard</a>

        <?php if ($isAdmin): ?>
            <a href="users.php">Manage Users</a>
            <a href="audit_logs.php">Audit Logs</a>
        <?php else: ?>
            <a href="dashboard.php">My Dashboard</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </aside>

    <main class="col-md-9 col-lg-10 p-4">
        <h2>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!</h2>

        <p class="text-muted">
            Your role: <?= htmlspecialchars(ucfirst($_SESSION["role"])) ?>
        </p>

        <?php if ($isAdmin): ?>

            <h4 class="mb-3">Dashboard Overview</h4>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm p-3">
                        <p class="text-muted mb-1">Total Users</p>
                        <h2><?= $userCount ?></h2>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm p-3">
                        <p class="text-muted mb-1">Total Students</p>
                        <h2><?= $studentCount ?></h2>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm p-3">
                        <p class="text-muted mb-1">Total Events</p>
                        <h2><?= $eventCount ?></h2>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card shadow-sm p-3">
                        <p class="text-muted mb-1">Registrations</p>
                        <h2><?= $registrationCount ?></h2>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Latest Event Registrations</h4>

                    <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Registered At</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($latest && $latest->num_rows > 0): ?>
                            <?php while ($row = $latest->fetch_assoc()): ?>
                                <tr>
                                    <td><?= (int)$row["id"] ?></td>
                                    <td><?= htmlspecialchars($row["student_name"]) ?></td>
                                    <td><?= htmlspecialchars($row["email"]) ?></td>
                                    <td><?= htmlspecialchars($row["event_title"]) ?></td>
                                    <td><?= htmlspecialchars($row["registered_at"]) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No registrations found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="alert alert-info">
                You are logged in as a student. Admin statistics,
                user management, and audit logs are restricted to admins.
            </div>

        <?php endif; ?>
    </main>
</div>
</div>

</body>
</html>
