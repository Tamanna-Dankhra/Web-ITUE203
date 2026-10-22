
<?php
require "db.php";

$message = "";
$editStudent = null;

// Add or update student
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"] ?? "";

    if ($action == "save") {
        $id = (int)($_POST["id"] ?? 0);
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $course = trim($_POST["course"] ?? "");

        if ($name == "" || $email == "" || $course == "") {
            $message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email address.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE students SET name=?, email=?, course=?
                     WHERE id=? AND status=1"
                );
                $stmt->bind_param("sssi", $name, $email, $course, $id);
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO students (name, email, course)
                     VALUES (?, ?, ?)"
                );
                $stmt->bind_param("sss", $name, $email, $course);
            }

            if ($stmt->execute()) {
                $message = $id > 0
                    ? "Student updated successfully."
                    : "Student added successfully.";
            } elseif ($stmt->errno == 1062) {
                $message = "This email already exists.";
            } else {
                $message = "Operation failed. Please try again.";
            }

            $stmt->close();
        }
    }

    // Soft delete student
    if ($action == "delete") {
        $id = (int)($_POST["id"] ?? 0);

        $stmt = $conn->prepare(
            "UPDATE students SET status=0
             WHERE id=? AND status=1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $message = $stmt->affected_rows > 0
            ? "Student deleted successfully."
            : "Student not found.";

        $stmt->close();
    }
}

// Load student for editing
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT id, name, email, course
         FROM students WHERE id=? AND status=1"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Search and filter
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");

$sql = "SELECT id, name, email, course
        FROM students WHERE status=1";
$params = [];
$types = "";

if ($search != "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $term = "%" . $search . "%";
    $params = [$term, $term, $term];
    $types = "sss";
}

if ($courseFilter != "") {
    $sql .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($types != "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$students = $stmt->get_result();

// CSV export
if (isset($_GET["export"])) {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=students.csv");

    $out = fopen("php://output", "w");
    fputcsv($out, ["ID", "Name", "Email", "Course"]);

    while ($row = $students->fetch_assoc()) {
        fputcsv($out, [
            $row["id"],
            $row["name"],
            $row["email"],
            $row["course"]
        ]);
    }

    fclose($out);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5fa;
            margin: 30px;
        }

        h1, h2 {
            color: #24478f;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .form-box, .table-box {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        input, select, button {
            padding: 9px;
            margin: 5px 0;
        }

        input, select {
            box-sizing: border-box;
        }

        .form-box input, .form-box select {
            width: 100%;
        }

        button, .btn {
            background: #24478f;
            color: white;
            border: none;
            padding: 9px 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .delete {
            background: #c62828;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #24478f;
            color: white;
        }

        .message {
            background: #e0f2e8;
            padding: 12px;
            margin-bottom: 15px;
        }

        .search-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Student Management System</h1>

    <?php if ($message != ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="form-box">
        <h2>
            <?php echo $editStudent ? "Edit Student" : "Add Student"; ?>
        </h2>

        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id"
                value="<?php echo (int)($editStudent["id"] ?? 0); ?>">

            <label>Student Name</label>
            <input type="text" name="name" required maxlength="100"
                value="<?php echo htmlspecialchars($editStudent["name"] ?? ""); ?>">

            <label>Email</label>
            <input type="email" name="email" required maxlength="100"
                value="<?php echo htmlspecialchars($editStudent["email"] ?? ""); ?>">

            <label>Course</label>
            <select name="course" required>
                <option value="">Select Course</option>
                <?php
                $courses = ["BCA", "BTech", "BSc IT", "MCA"];
                foreach ($courses as $c):
                    $selected = (($editStudent["course"] ?? "") == $c)
                        ? "selected" : "";
                ?>
                    <option value="<?php echo $c; ?>" <?php echo $selected; ?>>
                        <?php echo $c; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">
                <?php echo $editStudent ? "Update Student" : "Add Student"; ?>
            </button>

            <?php if ($editStudent): ?>
                <a class="btn" href="index.php">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-box">
        <h2>Student Records</h2>

        <form method="GET" class="search-form">
            <input type="text" name="search"
                placeholder="Search name, email or course"
                value="<?php echo htmlspecialchars($search); ?>">

           
<select name="course">
    <option value="">All Courses</option>
    <option value="BCA">BCA</option>
    <option value="BTech">BTech</option>
    <option value="MCA">MCA</option>
</select>

            <button type="submit">Search / Filter</button>
            <a class="btn" href="index.php">Reset</a>
            <a class="btn"
                href="?search=<?php echo urlencode($search); ?>&course=<?php echo urlencode($courseFilter); ?>&export=1">
                Export CSV
            </a>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>

            <?php if ($students->num_rows > 0): ?>
                <?php while ($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["email"]); ?></td>
                        <td><?php echo htmlspecialchars($row["course"]); ?></td>
                        <td>
                            <a class="btn"
                                href="index.php?edit=<?php echo $row["id"]; ?>">
                                Edit
                            </a>

                            <form method="POST" style="display:inline"
                                onsubmit="return confirm('Are you sure you want to delete this student?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id"
                                    value="<?php echo $row["id"]; ?>">
                                <button type="submit" class="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No students found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
