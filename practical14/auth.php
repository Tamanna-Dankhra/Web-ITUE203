
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

function recordAudit($conn, $action, $details) {
    $userId = $_SESSION["user_id"] ?? null;
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

function requireLogin() {
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (($_SESSION["role"] ?? "") !== "admin") {
        http_response_code(403);
        exit("403 Forbidden: Admin access only.");
    }
}
?>
