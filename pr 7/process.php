
<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$message = trim($_POST["message"] ?? "");

$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$gender = htmlspecialchars($gender, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

$errors = [];

if (empty($name)) {
    $errors[] = "Name is required.";
} elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    $errors[] = "Name should contain only letters and spaces.";
}

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Phone number must contain exactly 10 digits.";
}

if (empty($gender)) {
    $errors[] = "Please select gender.";
}

if (!empty($errors)) {
?>

<!DOCTYPE html>
<html>
<head>

    <title>Registration Error</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            background: white;
            width: 450px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .icon {
            font-size: 55px;
            text-align: center;
        }

        h2 {
            text-align: center;
            color: #dc2626;
        }

        li {
            margin: 12px 0;
            color: #555;
        }

        .back {
            display: block;
            text-align: center;
            background: #4f46e5;
            color: white;
            padding: 13px;
            border-radius: 10px;
            text-decoration: none;
            margin-top: 25px;
        }

    </style>

</head>

<body>

<div class="box">

    <div class="icon">⚠️</div>

    <h2>Registration Failed</h2>

    <ul>

        <?php

        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }

        ?>

    </ul>

    <a href="index.php" class="back">
        ← Go Back
    </a>

</div>

</body>
</html>

<?php
exit();
}

$file = "registrations.csv";

$handle = fopen($file, "a");

if ($handle === false) {
    die("Unable to open CSV file.");
}

if (flock($handle, LOCK_EX)) {

    if (filesize($file) == 0) {

        fputcsv($handle, [
            "Name",
            "Email",
            "Phone",
            "Gender",
            "Message"
        ]);
    }

    fputcsv($handle, [
        $name,
        $email,
        $phone,
        $gender,
        $message
    ]);

    flock($handle, LOCK_UN);
}

fclose($handle);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Registration Successful</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-box {
            width: 500px;
            background: white;
            padding: 45px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .check {
            width: 80px;
            height: 80px;
            margin: auto;
            border-radius: 50%;
            background: #dcfce7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        h1 {
            color: #16a34a;
            margin-top: 25px;
        }

        p {
            color: #666;
        }

        .info {
            background: #f5f7ff;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            margin: 5px;
            border-radius: 9px;
            text-decoration: none;
            color: white;
            background: #4f46e5;
        }

        .btn.green {
            background: #16a34a;
        }

    </style>

</head>

<body>

<div class="success-box">

    <div class="check">
        ✓
    </div>

    <h1>Registration Successful!</h1>

    <p>
        Your information has been successfully processed
        and stored in the CSV file.
    </p>

    <div class="info">

        <strong>Welcome, <?php echo $name; ?>! 🎉</strong>

        <br><br>

        Your registration has been recorded successfully.

    </div>

    <a href="index.php" class="btn">
        Register Another
    </a>

    <a href="records.php" class="btn green">
        View Records
    </a>

</div>

</body>
</html>
