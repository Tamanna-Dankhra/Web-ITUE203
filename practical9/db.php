<?php
$conn = new mysqli("localhost", "root", "", "practical9");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>