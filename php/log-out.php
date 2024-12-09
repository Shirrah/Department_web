<?php
session_start();
session_unset();
session_destroy();
http_response_code(200); // Respond with success
exit();
?>
    