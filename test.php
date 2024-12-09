<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Semester-based Management System</h1>

    <form method="GET" action="">
        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester" onchange="this.form.submit()">
            <option value="">-- All Semesters --</option>
            <?php
            $stmt = $pdo->query("SELECT id_semester, semester_name FROM semesters ORDER BY start_date DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = (isset($_GET['semester']) && $_GET['semester'] == $row['id_semester']) ? 'selected' : '';
                echo "<option value='{$row['id_semester']}' $selected>{$row['semester_name']}</option>";
            }
            ?>
        </select>
    </form>

    <?php
    $semesterFilter = isset($_GET['semester']) && $_GET['semester'] ? "WHERE id_semester = {$_GET['semester']}" : '';
    $eventsQuery = "SELECT * FROM events $semesterFilter";
    $events = $pdo->query($eventsQuery)->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <table>
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Description</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= $event['name_event'] ?></td>
                    <td><?= $event['date_event'] ?></td>
                    <td><?= $event['event_desc'] ?></td>
                    <td><?= $event['id_semester'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
