
<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
        $error = "Enter a valid email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, email, password, role, status
             FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "Incorrect email or password.";
        } elseif ($user["status"] !== "active") {
            $error = "Your account is inactive. Contact the administrator.";
        } else {
            session_regenerate_id(true);

            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            $userId = (int)$user["id"];
            $action = "LOGIN";
            $details = "User logged in successfully.";
            $ip = $_SERVER["REMOTE_ADDR"] ?? "";

            $log = $conn->prepare(
                "INSERT INTO audit_logs (user_id, action, details, ip_address)
                 VALUES (?, ?, ?, ?)"
            );
            $log->bind_param("isss", $userId, $action, $details, $ip);
            $log->execute();
            $log->close();

            if ($user["role"] === "admin") {
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Practical 14 - Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width:420px">
        <div class="card-body p-4">
            <h2 class="text-primary mb-3">Admin Dashboard Login</h2>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label class="form-label" for="email">Email</label>
                <input class="form-control mb-3" type="email"
                       id="email" name="email" required>

                <label class="form-label" for="password">Password</label>
                <input class="form-control mb-3" type="password"
                       id="password" name="password" required>

                <button class="btn btn-primary w-100" type="submit">
                    Login
                </button>
            </form>

            <p class="text-muted small mt-3 mb-0">
                Demo admin: admin@example.com / Admin@123
            </p>
        </div>
    </div>
</div>
</body>
</html>
