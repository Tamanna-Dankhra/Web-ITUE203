
<?php
session_start();

if (empty($_SESSION["event_admin"])) {
    header("Location: admin_login.php");
    exit;
}

require "db.php";

$message = "";
$editEvent = null;

$uploadDir = __DIR__ . "/uploads/";
$maxSize = 2 * 1024 * 1024;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function savePoster($file, $uploadDir, $maxSize) {
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Poster upload failed.");
    }

    if ($file["size"] > $maxSize) {
        throw new Exception("Poster must be 2 MB or smaller.");
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        throw new Exception("Invalid uploaded file.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        throw new Exception("Only JPG, PNG, and WebP posters are allowed.");
    }

    $imageInfo = getimagesize($file["tmp_name"]);

    if ($imageInfo === false) {
        throw new Exception("The uploaded file is not a valid image.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        throw new Exception("Could not save the poster.");
    }

    return $filename;
}

function removePoster($filename, $uploadDir) {
    if ($filename !== null && $filename !== "") {
        $path = $uploadDir . basename($filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "save") {
            $id = (int)($_POST["id"] ?? 0);
            $title = trim($_POST["title"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $eventDate = trim($_POST["event_date"] ?? "");
            $status = $_POST["status"] ?? "Open";

            if (
                $title === "" || $description === "" ||
                $category === "" || $eventDate === ""
            ) {
                throw new Exception("Please fill in all required fields.");
            }

            $date = DateTime::createFromFormat("!Y-m-d", $eventDate);

            if (!$date || $date->format("Y-m-d") !== $eventDate) {
                throw new Exception("Please enter a valid event date.");
            }

            if (!in_array($status, ["Open", "Closed"], true)) {
                throw new Exception("Invalid event status.");
            }

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "SELECT poster FROM events WHERE id = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $existing = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$existing) {
                    throw new Exception("Event not found.");
                }

                $oldPoster = $existing["poster"];
                $newPoster = savePoster(
                    $_FILES["poster"] ?? null,
                    $uploadDir,
                    $maxSize
                );

                $poster = $newPoster ?? $oldPoster;

                $stmt = $conn->prepare(
                    "UPDATE events
                     SET title = ?, description = ?, category = ?,
                         event_date = ?, status = ?, poster = ?
                     WHERE id = ?"
                );
                $stmt->bind_param(
                    "ssssssi",
                    $title,
                    $description,
                    $category,
                    $eventDate,
                    $status,
                    $poster,
                    $id
                );
                $stmt->execute();
                $stmt->close();

                if ($newPoster !== null) {
                    removePoster($oldPoster, $uploadDir);
                }

                $message = "Event updated successfully.";
            } else {
                $poster = savePoster(
                    $_FILES["poster"] ?? null,
                    $uploadDir,
                    $maxSize
                );

                $stmt = $conn->prepare(
                    "INSERT INTO events
                     (title, description, category, event_date, status, poster)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssssss",
                    $title,
                    $description,
                    $category,
                    $eventDate,
                    $status,
                    $poster
                );
                $stmt->execute();
                $stmt->close();

                $message = "Event added successfully.";
            }
        } elseif ($action === "delete") {
            $id = (int)($_POST["id"] ?? 0);

            $stmt = $conn->prepare(
                "SELECT poster FROM events WHERE id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$event) {
                throw new Exception("Event not found.");
            }

            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            removePoster($event["poster"], $uploadDir);
            $message = "Event deleted successfully.";
        }
    } catch (Throwable $e) {
        $message = $e instanceof mysqli_sql_exception
            ? "Database operation failed."
            : $e->getMessage();
    }
}

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$editEvent) {
        $message = "Event not found.";
    }
}

$search = trim($_GET["search"] ?? "");
$filterStatus = $_GET["status"] ?? "";
$filterCategory = $_GET["category"] ?? "";

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "sss";
}

if (in_array($filterStatus, ["Open", "Closed"], true)) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}

if ($filterCategory !== "") {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
    $types .= "s";
}

$sql .= " ORDER BY event_date ASC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$categoriesResult = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
);

$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row["category"];
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Event Management</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, sans-serif;
        background: #eef3ff;
        margin: 0;
        padding: 24px;
        color: #202938;
    }
    .container {
        max-width: 1100px;
        margin: auto;
    }
    .top, .panel {
        background: white;
        padding: 22px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 3px 12px #0000000d;
    }
    .top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    h1, h2 { color: #3157c8; }
    label {
        display: block;
        margin-top: 12px;
        font-weight: bold;
    }
    input, textarea, select {
        width: 100%;
        padding: 10px;
        margin-top: 6px;
        border: 1px solid #ccd3df;
        border-radius: 6px;
    }
    button, .button {
        display: inline-block;
        padding: 10px 14px;
        border: 0;
        border-radius: 6px;
        background: #3157c8;
        color: white;
        cursor: pointer;
        text-decoration: none;
        margin-top: 12px;
    }
    .delete { background: #c62828; }
    .cancel { background: #657080; }
    .message {
        background: #e4f7e9;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    .filters {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 10px;
        align-items: end;
    }
    .filters label { margin: 0; }
    .filters button { margin: 0; }
    .table-wrap { overflow-x: auto; }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #e1e5ed;
        text-align: left;
        vertical-align: top;
    }
    th { background: #eaf0ff; }
    .poster {
        width: 100px;
        max-height: 85px;
        object-fit: cover;
        border-radius: 5px;
    }
    .actions form { display: inline-block; }
    .actions button { margin: 3px 0; }
    @media (max-width: 700px) {
        body { padding: 10px; }
        .filters { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>
<div class="container">

    <header class="top">
        <div>
            <h1>Event Management</h1>
            <p>Add, update, view and manage college events.</p>
        </div>
        <a class="button cancel" href="logout.php">Logout</a>
    </header>

    <?php if ($message !== ""): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id"
                   value="<?= h($editEvent["id"] ?? 0) ?>">

            <label>Event Title</label>
            <input type="text" name="title" maxlength="150" required
                   value="<?= h($editEvent["title"] ?? "") ?>">

            <label>Description</label>
            <textarea name="description" rows="3" required><?= h($editEvent["description"] ?? "") ?></textarea>

            <label>Category</label>
            <input type="text" name="category" maxlength="100" required
                   placeholder="Workshop, Cultural, Competition"
                   value="<?= h($editEvent["category"] ?? "") ?>">

            <label>Event Date</label>
            <input type="date" name="event_date" required
                   value="<?= h($editEvent["event_date"] ?? "") ?>">

            <label>Status</label>
            <select name="status" required>
                <option value="Open"
                    <?= ($editEvent["status"] ?? "Open") === "Open" ? "selected" : "" ?>>
                    Open
                </option>
                <option value="Closed"
                    <?= ($editEvent["status"] ?? "") === "Closed" ? "selected" : "" ?>>
                    Closed
                </option>
            </select>

            <label>Event Poster (JPG, PNG, WebP — maximum 2 MB)</label>
            <input type="file" name="poster"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if (!empty($editEvent["poster"])): ?>
                <p>Current poster:</p>
                <img class="poster"
                     src="<?= "uploads/" . rawurlencode(basename($editEvent["poster"])) ?>"
                     alt="Current event poster">
                <p>Leave the poster field empty to keep the existing poster.</p>
            <?php endif; ?>

            <button type="submit">
                <?= $editEvent ? "Update Event" : "Add Event" ?>
            </button>

            <?php if ($editEvent): ?>
                <a class="button cancel" href="index.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Search and Filter Events</h2>

        <form method="get" class="filters">
            <label>
                Search
                <input type="text" name="search"
                       placeholder="Search title, description, category"
                       value="<?= h($search) ?>">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="Open" <?= $filterStatus === "Open" ? "selected" : "" ?>>Open</option>
                    <option value="Closed" <?= $filterStatus === "Closed" ? "selected" : "" ?>>Closed</option>
                </select>
            </label>

            <label>
                Category
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= h($category) ?>"
                            <?= $filterCategory === $category ? "selected" : "" ?>>
                            <?= h($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button type="submit">Apply</button>
        </form>
    </section>

    <section class="panel">
        <h2>Event Records</h2>

        <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Poster</th>
                <th>Title / Description</th>
                <th>Category</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($events->num_rows === 0): ?>
                <tr><td colspan="7">No events found.</td></tr>
            <?php else: ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?= h($event["id"]) ?></td>
                        <td>
                            <?php if (!empty($event["poster"])): ?>
                                <img class="poster"
                                     src="<?= "uploads/" . rawurlencode(basename($event["poster"])) ?>"
                                     alt="Event poster">
                            <?php else: ?>
                                No poster
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= h($event["title"]) ?></strong><br>
                            <?= nl2br(h($event["description"])) ?>
                        </td>
                        <td><?= h($event["category"]) ?></td>
                        <td><?= h($event["event_date"]) ?></td>
                        <td><?= h($event["status"]) ?></td>
                        <td class="actions">
                            <a class="button"
                               href="?edit=<?= (int)$event["id"] ?>">Edit</a>

                            <form method="post"
                                  onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id"
                                       value="<?= (int)$event["id"] ?>">
                                <button class="delete" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </section>

</div>
</body>
</html>
