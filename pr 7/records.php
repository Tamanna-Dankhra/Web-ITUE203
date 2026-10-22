
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Student Records</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 8px;
        }

        .header p {
            color: #777;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            color: #444;
        }

        tr:hover {
            background: #f5f7ff;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 9px;
        }

        .badge {
            background: #ede9fe;
            color: #6d28d9;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>📋 Student Records</h1>

        <p>
            All registered users stored in the CSV file
        </p>

    </div>

    <div class="table-container">

    <?php

    $file = "registrations.csv";

    if (!file_exists($file) || filesize($file) == 0) {

        echo "
        <div class='empty'>
            <h2>📭 No Records Found</h2>
            <p>Please register a student first.</p>
        </div>
        ";

    } else {

        $handle = fopen($file, "r");

        if ($handle !== false) {

            echo "<table>";

            $header = fgetcsv($handle);

            echo "<tr>";

            foreach ($header as $heading) {
                echo "<th>" . htmlspecialchars($heading) . "</th>";
            }

            echo "</tr>";

            while (($data = fgetcsv($handle)) !== false) {

                echo "<tr>";

                foreach ($data as $index => $value) {

                    if ($index == 3) {
                        echo "<td><span class='badge'>" .
                             htmlspecialchars($value) .
                             "</span></td>";
                    } else {
                        echo "<td>" .
                             htmlspecialchars($value) .
                             "</td>";
                    }
                }

                echo "</tr>";
            }

            echo "</table>";

            fclose($handle);
        }
    }

    ?>

    </div>

    <a href="index.php" class="back">
        ← Back to Registration
    </a>

</div>

</body>

</html>

