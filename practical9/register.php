
<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($username == "" || $email == "" ||
        $password == "" || $confirm_password == "") {
        die("All fields are required.");
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        die("Username must be 3 to 50 characters.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE username = ? OR email = ?"
    );
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Username or email already exists.");
    }

    $stmt->close();

    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "sss",
        $username,
        $email,
        $hashed_password
    );

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        if ($stmt->errno == 1062) {
            echo "Username or email already exists.";
        } else {
            echo "Registration failed. Please try again.";
        }
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Please submit the registration form.";
}
?>
