
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Practical 13</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            margin: 0;
            padding: 25px;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000015;
        }

        h1, h2 {
            color: #3454c5;
        }

        input, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-size: 15px;
        }

        input {
            border: 1px solid #bbb;
            width: 100%;
        }

        button {
            border: none;
            background: #3454c5;
            color: white;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.85;
        }

        .delete-btn {
            background: #d93636;
        }

        .edit-btn {
            background: #198754;
        }

        .cancel-btn {
            background: #666;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .search-row {
            display: flex;
            gap: 8px;
            margin: 15px 0;
        }

        .search-row input {
            flex: 1;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #3454c5;
            color: white;
        }

        #message {
            padding: 10px;
            margin: 12px 0;
            border-radius: 5px;
            display: none;
        }

        .success {
            display: block !important;
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            display: block !important;
            background: #f8d7da;
            color: #842029;
        }

        .loading {
            color: #3454c5;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 15px;
            }

            body {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Student Management System</h1>
    <p>Practical 13: PHP JSON API and AJAX/Fetch CRUD</p>

    <div id="message" role="status" aria-live="polite"></div>

    <h2 id="formTitle">Add Student</h2>

    <form id="studentForm">
        <input type="hidden" id="studentId">

        <div class="form-grid">
            <input
                type="text"
                id="nameInput"
                placeholder="Student Name"
                maxlength="100"
                required
            >

            <input
                type="email"
                id="emailInput"
                placeholder="Student Email"
                maxlength="150"
                required
            >

            <input
                type="text"
                id="courseInput"
                placeholder="Course"
                maxlength="100"
                required
            >
        </div>

        <button type="submit" id="submitBtn">Add Student</button>
        <button type="button" id="cancelBtn" class="cancel-btn"
                hidden>Cancel Edit</button>
    </form>

    <h2>Student Records</h2>

    <div class="search-row">
        <input
            type="search"
            id="searchInput"
            placeholder="Search by name, email, or course"
        >
        <button type="button" id="searchBtn">Search</button>
        <button type="button" id="clearBtn">Clear</button>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="studentTable">
                <tr>
                    <td colspan="5">Loading students...</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
    const apiURL = "api.php";

    const studentForm = document.getElementById("studentForm");
    const studentId = document.getElementById("studentId");
    const nameInput = document.getElementById("nameInput");
    const emailInput = document.getElementById("emailInput");
    const courseInput = document.getElementById("courseInput");
    const searchInput = document.getElementById("searchInput");
    const studentTable = document.getElementById("studentTable");
    const messageBox = document.getElementById("message");
    const submitBtn = document.getElementById("submitBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const formTitle = document.getElementById("formTitle");

    let messageTimer;

    function showMessage(message, isError = false) {
        clearTimeout(messageTimer);

        messageBox.textContent = message;
        messageBox.className = isError ? "error" : "success";

        messageTimer = setTimeout(() => {
            messageBox.style.display = "none";
            messageBox.className = "";
        }, 4000);
    }

    async function requestAPI(url, options = {}) {
        const response = await fetch(url, options);

        let result;

        try {
            result = await response.json();
        } catch {
            throw new Error("Invalid response from server.");
        }

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || "Something went wrong."
            );
        }

        return result;
    }

    // GET: Load and search students
    async function loadStudents(search = "") {
        try {
            studentTable.innerHTML =
                '<tr><td colspan="5">Loading students...</td></tr>';

            const result = await requestAPI(
                `${apiURL}?search=${encodeURIComponent(search)}`
            );

            displayStudents(result.data || []);

        } catch (error) {
            studentTable.innerHTML =
                '<tr><td colspan="5">Failed to load students.</td></tr>';

            showMessage(error.message, true);
        }
    }

    // Display students in the table
    function displayStudents(students) {
        studentTable.innerHTML = "";

        if (students.length === 0) {
            studentTable.innerHTML =
                '<tr><td colspan="5">No students found.</td></tr>';
            return;
        }

        students.forEach(student => {
            const row = document.createElement("tr");

            [
                student.id,
                student.name,
                student.email,
                student.course
            ].forEach(value => {
                const cell = document.createElement("td");
                cell.textContent = value;
                row.appendChild(cell);
            });

            const actionCell = document.createElement("td");

            const editBtn = document.createElement("button");
            editBtn.textContent = "Edit";
            editBtn.className = "edit-btn";
            editBtn.type = "button";
            editBtn.addEventListener("click", () => editStudent(student));

            const deleteBtn = document.createElement("button");
            deleteBtn.textContent = "Delete";
            deleteBtn.className = "delete-btn";
            deleteBtn.type = "button";
            deleteBtn.addEventListener("click", () =>
                deleteStudent(student.id)
            );

            actionCell.append(editBtn, deleteBtn);
            row.appendChild(actionCell);
            studentTable.appendChild(row);
        });
    }

    // Add or update a student
    studentForm.addEventListener("submit", async function(event) {
        event.preventDefault();

        const id = studentId.value;

        const student = {
            action: id ? "update" : "add",
            id: id ? Number(id) : undefined,
            name: nameInput.value.trim(),
            email: emailInput.value.trim(),
            course: courseInput.value.trim()
        };

        if (!student.name || !student.email || !student.course) {
            showMessage("Please fill in all fields.", true);
            return;
        }

        try {
            submitBtn.disabled = true;
            submitBtn.textContent = "Please wait...";

            const result = await requestAPI(apiURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(student)
            });

            resetForm();

            // Refresh the list first, then show the CRUD message.
            await loadStudents(searchInput.value.trim());

            showMessage(result.message);

        } catch (error) {
            showMessage(error.message, true);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = studentId.value
                ? "Update Student"
                : "Add Student";
        }
    });

    // Fill the form for editing
    function editStudent(student) {
        studentId.value = student.id;
        nameInput.value = student.name;
        emailInput.value = student.email;
        courseInput.value = student.course;

        formTitle.textContent = "Update Student";
        submitBtn.textContent = "Update Student";
        cancelBtn.hidden = false;

        studentForm.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    // Reset the form
    function resetForm() {
        studentForm.reset();
        studentId.value = "";
        formTitle.textContent = "Add Student";
        submitBtn.textContent = "Add Student";
        cancelBtn.hidden = true;
    }

    cancelBtn.addEventListener("click", resetForm);

    // Delete a student
    async function deleteStudent(id) {
        if (!confirm("Are you sure you want to delete this student?")) {
            return;
        }

        try {
            const result = await requestAPI(apiURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "delete",
                    id: Number(id)
                })
            });

            await loadStudents(searchInput.value.trim());

            showMessage(result.message);

        } catch (error) {
            showMessage(error.message, true);
        }
    }

    // Search students
    document.getElementById("searchBtn").addEventListener("click", () => {
        loadStudents(searchInput.value.trim());
    });

    searchInput.addEventListener("keydown", event => {
        if (event.key === "Enter") {
            event.preventDefault();
            loadStudents(searchInput.value.trim());
        }
    });

    // Clear search
    document.getElementById("clearBtn").addEventListener("click", () => {
        searchInput.value = "";
        loadStudents();
    });

    // Initial load
    loadStudents();
</script>

</body>
</html>
