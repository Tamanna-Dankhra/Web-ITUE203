
<?php
require_once "auth.php";
requireAdmin();

$result = $conn->query(
    "SELECT a.id, u.username, a.action, a.details,
            a.ip_address, a.created_at
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC
     LIMIT 100"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Audit Logs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Audit Logs</h2>
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>

    <p class="text-muted">Showing the latest 100 audit log entries.</p>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action</th>
            <th>Details</th>
            <th>IP Address</th>
            <th>Date and Time</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row["id"] ?></td>
                    <td><?= htmlspecialchars($row["username"] ?? "Deleted user") ?></td>
                    <td><?= htmlspecialchars($row["action"]) ?></td>
                    <td><?= htmlspecialchars($row["details"]) ?></td>
                    <td><?= htmlspecialchars($row["ip_address"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["created_at"]) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No audit logs found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
