<?php
require_once './php/db-conn.php';

$database = Database::getInstance();
$db = $database->db;

// Get all semesters
$semesterQuery = "SELECT semester_ID, academic_year, semester_type, status FROM semester ORDER BY academic_year DESC";
$semesters = $db->query($semesterQuery);

// Get events by semester
if (isset($_POST['semester_id'])) {
    $semesterId = $_POST['semester_id'];
    $eventQuery = "SELECT * FROM events WHERE semester_ID = ? ORDER BY date_event, event_start_time";
    $stmt = $db->prepare($eventQuery);
    $stmt->bind_param("s", $semesterId);
    $stmt->execute();
    $events = $stmt->get_result();
    $output = '';

    if ($events->num_rows > 0) {
        while ($event = $events->fetch_assoc()) {
            $output .= '<tr>
                            <td>' . htmlspecialchars($event['name_event']) . '</td>
                            <td>' . date('F j, Y', strtotime($event['date_event'])) . '</td>
                            <td>' . date('g:i A', strtotime($event['event_start_time'])) . ' - ' . date('g:i A', strtotime($event['event_end_time'])) . '</td>
                            <td>' . htmlspecialchars($event['event_desc']) . '</td>
                        </tr>';
        }
    } else {
        $output .= '<tr><td colspan="4">No events found for this semester.</td></tr>';
    }
    echo $output;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Events</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <h1>View Events by Semester</h1>

    <label for="semester">Select Semester:</label>
    <select name="semester" id="semester">
        <option value="">-- Select Semester --</option>
        <?php while ($semester = $semesters->fetch_assoc()) : ?>
            <option value="<?php echo $semester['semester_ID']; ?>">
                <?php echo htmlspecialchars($semester['academic_year']) . ' - ' . htmlspecialchars($semester['semester_type']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <h2>Events</h2>
    <table id="events-table" border="1">
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <!-- Event rows will be populated here via AJAX -->
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // When the semester dropdown value changes
            $('#semester').change(function() {
                var semesterId = $(this).val();

                if (semesterId) {
                    // Use AJAX to fetch events for the selected semester
                    $.ajax({
                        url: '', // Current page
                        type: 'POST',
                        data: { semester_id: semesterId },
                        success: function(response) {
                            $('#events-table tbody').html(response); // Display the events
                        }
                    });
                } else {
                    $('#events-table tbody').html('<tr><td colspan="4">Please select a semester to see events.</td></tr>');
                }
            });
        });
    </script>
</body>
</html>
