<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require_once('includes/db-connection.php');
require_once('includes/log-error.php');

function submitFeedback() {
    $conn = dbConnect('read');
    $satisfaction = $_POST['satisfaction'];
    $message = trim($_POST['message']);

    $options = ['good', 'neutral', 'bad', ''];

    if(!in_array($satisfaction, $options)) {
        return "error";
        exit;
    }

    if(strlen($message) > 1000) {
        return "error";
        exit;
    }

    // If there are no problems, add feedback to database
    $add_feedback = $conn->prepare("INSERT INTO feedback (satisfaction, message) VALUES (?, ?)");
    $add_feedback->bind_param("ss", $satisfaction, $message);
    $add_feedback->execute();
    if($add_feedback->affected_rows == 1) {
        return "success";
    } else {
        return "fail";
        logError("FEEDBACK SURVEY", ("Error adding feedback to database. Satisfaction: " . $satisfaction . ". Message: " . $message));
    }
    $add_feedback->close();
    $conn->close();
}

echo submitFeedback();

?>