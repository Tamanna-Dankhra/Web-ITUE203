
<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        $email === "admin@example.com" &&
        $password === "Admin@123"
    ) {
        session_regenerate_id(true);
        $_SESSION["event_admin"] = true;
        header("Location: index.php");
        exit;
    }

    $error = "Invalid admin email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Event Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            padding: 40px 15px;
        }
        form {
            background: white;
            max-width: 380px;
            margin: auto;
            padding: 25px;
            border-radius: 12px;
        }
        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            margin: 8px 0;
        }
        button {
            background: #3157c8;
            color: white;
            border: 0;
            cursor: pointer;
        }
        .error { color: #c62828; }
    </style>
</head>
<body>
<form method="post">
    <h2>Admin Login</h2>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>
</body>
</html>
