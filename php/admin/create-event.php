<?php
require_once "././php/db-conn.php";
$db = new Database();

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name_event = $_POST['name_event'];
    $date_event = $_POST['date_event'];
    $event_desc = $_POST['event_desc'];

    // Insert event into database
    $sql = "INSERT INTO events (name_event, date_event, event_desc) VALUES (?, ?, ?)";
    
    if ($stmt = $db->db->prepare($sql)) {
        $stmt->bind_param("sss", $name_event, $date_event, $event_desc);
        
        if ($stmt->execute()) {
            echo "Event created successfully!";
        } else {
            echo "Error creating event. Please try again.";
        }
        
        $stmt->close();
    } else {
        echo "Error preparing SQL statement.";
    }
}
?>
