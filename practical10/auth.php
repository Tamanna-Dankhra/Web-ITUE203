<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

if (time() - $_SESSION["last_activity"] > 300) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.html?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>