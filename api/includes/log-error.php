<?php
require_once('db-connection.php');

function logError($type, $message) {
    $conn = dbConnect('read');
    $add_error = $conn->prepare("INSERT INTO error_log (error_type, error_message) VALUES (?, ?)");
    $add_error->bind_param("ss", $type, substr($message, 0, 1500));
    $add_error->execute();
    $add_error->close();
    $conn->close();
}
?>