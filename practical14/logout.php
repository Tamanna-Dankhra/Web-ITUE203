
<?php
session_start();

if (!empty($_SESSION["user_id"])) {
    require_once "db.php";

    $userId = (int)$_SESSION["user_id"];
    $action = "LOGOUT";
    $details = "User logged out.";
    $ip = $_SERVER["REMOTE_ADDR"] ?? "";

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, action, details, ip_address)
         VALUES (?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param("isss", $userId, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit;
?>
