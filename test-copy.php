<div class="list-events-con">
    <div class="accordion" id="accordionExample">
        <?php while($event = $events->fetch_assoc()): ?>
            <?php
            // Fetch the creator's name based on the user ID
            $creatorQuery = "SELECT name FROM users WHERE id_user = ?";
            $creatorStmt = $db->db->prepare($creatorQuery);
            $creatorStmt->bind_param("i", $event['created_by']);
            $creatorStmt->execute();
            $creatorResult = $creatorStmt->get_result();
            $creator = $creatorResult->fetch_assoc();
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $event['id_event']; ?>">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $event['id_event']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $event['id_event']; ?>">
                       <strong><?php echo $event['name_event']; ?> - <?php echo date("F j, Y", strtotime($event['date_event'])); ?></strong> 
                    </button>
                </h2>
                <div id="collapse<?php echo $event['id_event']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <p><strong>Event Name:</strong> <?php echo $event['name_event']; ?></p>
                        <p><strong>Event Date:</strong> <?php echo date("F j, Y", strtotime($event['date_event'])); ?></p>
                        <p><strong>Start Time:</strong> <?php echo date("h:i A", strtotime($event['event_start_time'])); ?></p>
                        <p><strong>End Time:</strong> <?php echo date("h:i A", strtotime($event['event_end_time'])); ?></p>
                        <p><strong>Created By:</strong> <?php echo $creator['name']; ?></p> <!-- Display the creator's name -->
                        <p><strong>Actions:</strong></p>
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="openEditModal(
                            '<?php echo $event['id_event']; ?>',
                            '<?php echo addslashes($event['name_event']); ?>',
                            '<?php echo date('Y-m-d', strtotime($event['date_event'])); ?>',
                        )"><i class='fas fa-edit'></i></a>
                        <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="confirmDelete(<?php echo $event['id_event']; ?>)"><i class='fas fa-trash'></i></a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="document.getElementById('id_event').value='<?php echo $event['id_event']; ?>';">Add Attendance</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
