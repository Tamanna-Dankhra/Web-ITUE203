<?php
$conn = new mysqli(
    "localhost",
    "root",
    "",
    "secure_login_db"
);

if ($conn->connect_error) {
    die("Database connection failed.");
}
?>