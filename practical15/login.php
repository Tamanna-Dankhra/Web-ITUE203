
<?php
session_start();
require_once "config.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
        $error = "Enter a valid email and password.";
    } else {
        $stmt = $conn->prepare(
           "SELECT student_id, name, email, password, role
FROM        students WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["student_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Hub Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Student Hub</h1>
        <h2>Sign in to continue</h2>

        <?php if ($error !== ""): ?>
            <p class="error" role="alert">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">College Email</label>
            <input type="email" id="email" name="email"
                   placeholder="College Email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
