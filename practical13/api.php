
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";

function respond($success, $message, $data = null, $status = 200) {
    http_response_code($status);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit;
}

function readInput() {
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        respond(false, "Invalid JSON input.", null, 400);
    }

    return $input;
}

function cleanText($value) {
    return is_string($value) ? trim($value) : "";
}

function validateStudent($input) {
    $name = cleanText($input["name"] ?? "");
    $email = cleanText($input["email"] ?? "");
    $course = cleanText($input["course"] ?? "");

    if ($name === "" || $email === "" || $course === "") {
        respond(false, "All fields are required.", null, 422);
    }

    if (strlen($name) > 100 || strlen($course) > 100) {
        respond(false, "Name or course is too long.", null, 422);
    }

    if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, "Enter a valid email address.", null, 422);
    }

    return [$name, $email, $course];
}

$method = $_SERVER["REQUEST_METHOD"];

// GET: List and search students
if ($method === "GET") {
    $search = trim($_GET["search"] ?? "");

    if ($search !== "") {
        $term = "%" . $search . "%";

        $stmt = $conn->prepare(
            "SELECT id, name, email, course, created_at
             FROM students
             WHERE name LIKE ? OR email LIKE ? OR course LIKE ?
             ORDER BY id DESC"
        );

        $stmt->bind_param("sss", $term, $term, $term);
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, course, created_at
             FROM students
             ORDER BY id DESC"
        );
    }

    if (!$stmt || !$stmt->execute()) {
        respond(false, "Failed to fetch students.", null, 500);
    }

    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);

    respond(true, "Students fetched successfully.", $students);
}

// Only GET and POST are supported
if ($method !== "POST") {
    header("Allow: GET, POST");
    respond(false, "Method not allowed.", null, 405);
}

$input = readInput();
$action = $input["action"] ?? "";

// ADD: Create a student
if ($action === "add") {
    [$name, $email, $course] = validateStudent($input);

    $stmt = $conn->prepare(
        "INSERT INTO students (name, email, course)
         VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        respond(false, "Database query failed.", null, 500);
    }

    $stmt->bind_param("sss", $name, $email, $course);

    if (!$stmt->execute()) {
        if ($stmt->errno === 1062) {
            respond(false, "This email address already exists.", null, 409);
        }

        respond(false, "Failed to add student.", null, 500);
    }

    respond(true, "Student added successfully.", [
        "id" => $conn->insert_id
    ], 201);
}

// UPDATE: Edit a student
if ($action === "update") {
    $id = filter_var(
        $input["id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$id) {
        respond(false, "Valid student ID is required.", null, 422);
    }

    [$name, $email, $course] = validateStudent($input);

    $stmt = $conn->prepare(
        "UPDATE students
         SET name = ?, email = ?, course = ?
         WHERE id = ?"
    );

    if (!$stmt) {
        respond(false, "Database query failed.", null, 500);
    }

    $stmt->bind_param("sssi", $name, $email, $course, $id);

    if (!$stmt->execute()) {
        if ($stmt->errno === 1062) {
            respond(false, "This email address already exists.", null, 409);
        }

        respond(false, "Failed to update student.", null, 500);
    }

    if ($stmt->affected_rows === 0) {
        $check = $conn->prepare("SELECT id FROM students WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$exists) {
            respond(false, "Student not found.", null, 404);
        }
    }

    respond(true, "Student updated successfully.");
}

// DELETE: Remove a student
if ($action === "delete") {
    $id = filter_var(
        $input["id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$id) {
        respond(false, "Valid student ID is required.", null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");

    if (!$stmt) {
        respond(false, "Database query failed.", null, 500);
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        respond(false, "Failed to delete student.", null, 500);
    }

    if ($stmt->affected_rows === 0) {
        respond(false, "Student not found.", null, 404);
    }

    respond(true, "Student deleted successfully.");
}

respond(false, "Invalid action.", null, 400);
?>
