<?php
// Include the Database class
require 'db-conn.php'; // Update with the correct path to your Database class

// Instantiate the Database class
$database = new Database();

// Check for connection errors
if ($database->error) {
    die($database->error);
}

// Fetch events from the database
$event_query = "SELECT * FROM events";
$events = $database->db->query($event_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Event and Attendance</title>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <link rel="icon" href="../../assets/images/ccslogo.png" type="image/icon type">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="particles"></div>
    <div class="container">
        <h1><i class="fas fa-calendar-alt"></i> Select Event and Attendance</h1>
        <form method="POST" action="scan.php">
            <div class="input-group">
                <label for="event"><i class="fas fa-list-alt"></i> Select Event:</label>
                <select name="event" id="event" required>
                    <option value="">-- Select Event --</option>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($event['id_event']); ?>"><?php echo htmlspecialchars($event['name_event']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label for="attendance"><i class="fas fa-users"></i> Select Attendance:</label>
                <select name="attendance" id="attendance" required>
                    <option value="">-- Select Attendance --</option>
                </select>
            </div>

            <button type="submit"><i class="fas fa-play"></i> Start/Enter</button>
        </form>
    </div>

    <div class="computer" aria-hidden="true"></div>

    <script>
        // Listen for changes in the event dropdown
        $('#event').on('change', function() {
            var eventId = $(this).val();

            if (eventId) {
                // Make an AJAX request to fetch attendance options
                $.ajax({
                    url: 'get_attendance.php',
                    type: 'GET',
                    data: { event_id: eventId },
                    success: function(response) {
                        var attendances = JSON.parse(response);
                        var attendanceDropdown = $('#attendance');

                        // Clear previous attendance options
                        attendanceDropdown.empty();
                        attendanceDropdown.append('<option value="">-- Select Attendance --</option>');

                        // Populate the new attendance options
                        attendances.forEach(function(attendance) {
                            attendanceDropdown.append(
                                '<option value="' + attendance.id_attendance + '">' + attendance.attendance_name + '</option>'
                            );
                        });
                    }
                });
            } else {
                // Clear the attendance dropdown if no event is selected
                $('#attendance').empty();
                $('#attendance').append('<option value="">-- Select Attendance --</option>');
            }
        });

        // Add particle effect
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.width = `${Math.random() * 5 + 1}px`;
                particle.style.height = particle.style.width;
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                particle.style.animationDuration = `${Math.random() * 10 + 10}s`;
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();
    </script>
</body>
</html>
