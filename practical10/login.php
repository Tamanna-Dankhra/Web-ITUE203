<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.html");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $conn->prepare(
    "SELECT id, username, password, role
     FROM users WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Invalid email or password.");
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user["password"])) {
    die("Invalid email or password.");
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];
$_SESSION["last_activity"] = time();

$update = $conn->prepare(
    "UPDATE users SET last_login = NOW() WHERE id = ?"
);
$update->bind_param("i", $user["id"]);
$update->execute();

$role = $user["role"];

$update->close();
$stmt->close();
$conn->close();

if ($role == "admin") {
    header("Location: admin.php");
} else {
    header("Location: student.php");
}
exit;
?>