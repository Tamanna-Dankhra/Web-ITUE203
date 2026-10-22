
<?php
require_once "auth.php";
requireAdmin();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_var(
        $_POST["id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    $newStatus = $_POST["status"] ?? "";

    if (!$id || !in_array($newStatus, ["active", "inactive"], true)) {
        $error = "Invalid user or status.";
    } elseif ($id === (int)$_SESSION["user_id"] && $newStatus === "inactive") {
        $error = "You cannot deactivate your own account.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET status = ?
             WHERE id = ? AND role = 'student'"
        );
        $stmt->bind_param("si", $newStatus, $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            recordAudit(
                $conn,
                "USER_STATUS_CHANGED",
                "User ID " . $id . " status set to " . $newStatus
            );
            $message = "User status updated successfully.";
        } else {
            $error = "Student user not found or status was unchanged.";
        }
        $stmt->close();
    }
}

$users = $conn->query(
    "SELECT id, username, email, role, status, created_at
     FROM users ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Users</h2>
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
    <table class="table table-striped align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$user["id"] ?></td>
                <td><?= htmlspecialchars($user["username"]) ?></td>
                <td><?= htmlspecialchars($user["email"]) ?></td>
                <td><?= htmlspecialchars($user["role"]) ?></td>
                <td><?= htmlspecialchars($user["status"]) ?></td>
                <td>
                    <?php if ($user["role"] === "student"): ?>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="id"
                                   value="<?= (int)$user["id"] ?>">

                            <select name="status" class="form-select">
                                <option value="active"
                                    <?= $user["status"] === "active" ? "selected" : "" ?>>
                                    Active
                                </option>
                                <option value="inactive"
                                    <?= $user["status"] === "inactive" ? "selected" : "" ?>>
                                    Inactive
                                </option>
                            </select>

                            <button class="btn btn-primary" type="submit">
                                Save
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">Admin account</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
