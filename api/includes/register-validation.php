<?php
require_once('includes/db-connection.php');
require_once('private/parse-env.php');

function checkCode($family_id) {
    $conn = dbConnect('read');
    $problems = array();
    // CHECK IF CODE IS VALID ---------------------------------------------------------------------------------
    // Prepare MySQL statements
    $query_valid_code = $conn->prepare("SELECT * FROM family_id_list WHERE family_code = ?");
    $query_valid_code->bind_param("s", $family_id);

    $query_used_code = $conn->prepare("SELECT * FROM registered_families WHERE family_id = ?");
    $query_used_code->bind_param("s", $family_id);
    
    $query_valid_code->execute(); // should return 1 row if code is in database
    $result_valid_code = $query_valid_code->get_result();
    $row_count_valid_code = mysqli_num_rows($result_valid_code);

    $query_used_code->execute(); // should return 0 rows if code hasn't been registered
    $result_used_code = $query_used_code->get_result();
    $row_count_used_code = mysqli_num_rows($result_used_code);

    if($row_count_valid_code == 0) {
        // code not found in database
        array_push($problems, "Family code is invalid.");
    } else if ($row_count_used_code >= 1) {
        // code already registered
        array_push($problems, "Family code has already been registered. Please use your own, unique code. Contact us if you think this is a mistake.");
    }
    
    $query_valid_code->close();
    $query_used_code->close();

    return $problems;
}

function addAttempt($IP) {
    $conn = dbConnect('read');
    $add_attempt = $conn->prepare("INSERT INTO failed_attempts (ip_address, attempts, last_attempt)
        VALUES (?, 1, NOW())

        ON DUPLICATE KEY UPDATE
        attempts = attempts + 1,
        last_attempt = NOW();
    ");
    if (!$add_attempt) {
        echo "Error in SQL statement: " . $conn->error;
    } else {
        // Bind the parameter and execute the query
        $add_attempt->bind_param("s", $IP);
        $add_attempt->execute();
        $add_attempt->close();
    }
    $conn->close();
}

function getAttempts($IP) {
    $conn = dbConnect('read');
    $get_attempts = $conn->prepare("SELECT attempts FROM failed_attempts WHERE ip_address = ?");
    $get_attempts->bind_param("s", $IP);
    $get_attempts->execute();
    $get_attempts->bind_result($attemptCount);
    $get_attempts->fetch();
    $get_attempts->close();
    $conn->close();

    return $attemptCount;
}

function resetAttempts($IP) {
    $conn = dbConnect('read');
    $reset_attempts = $conn->prepare("UPDATE failed_attempts SET attempts = 0 WHERE ip_address = ?");
    $reset_attempts->bind_param("s", $IP);
    $reset_attempts->execute();
    $reset_attempts->close();
    $conn->close();
}

function addLanguage($IP, $language) {
    $conn = dbConnect('read');
    $add_language = $conn->prepare("INSERT INTO language_changes (ip, language)
        VALUES (?, ?)

        ON DUPLICATE KEY UPDATE
        language = VALUES(language)
    ");
    if (!$add_language) {
        return "Error in SQL statement: " . $conn->error;
    } else {
        // Bind the parameter and execute the query
        $add_language->bind_param("ss", $IP, $language);
        $add_language->execute();
        $add_language->close();
        return false;
    }
    $conn->close();
}



function getTimeframes($limit = true) {
    $conn = dbConnect('read');

    $query_timeframe_limit = mysqli_query($conn, "SELECT value as timeframe_limit FROM event_settings WHERE name = 'timeframe_limit'");
    $ACTIVATE_LIMIT = mysqli_fetch_all($query_timeframe_limit)[0][0];

    // If there is a limit, return limited values. Otherwise, return all possible times.
    $query_timeframes = mysqli_query($conn, "SELECT es.value AS timeframe, COUNT(rf.reservation) AS count
        FROM event_settings es
        LEFT JOIN registered_families rf ON es.value = rf.reservation
        WHERE es.name = 'timeframe'
        GROUP BY es.value, rf.reservation");

    $timeframes = array();
    while($row = mysqli_fetch_array($query_timeframes)){
        $timeframes[$row["timeframe"]] = $row["count"];
    }

    $conn->close();

    if($limit) {
        // Filter keys where the value is less than $ACTIVATE_LIMIT
        $available_timeframes = array();
        foreach ($timeframes as $key => $value) {
            if ($value < $ACTIVATE_LIMIT) {
                $available_timeframes[$key] = $value;
            }
        }

        if(count($available_timeframes) == 1) {
            // If there's only one key remaining, get the two lowest values
            asort($timeframes); // Sort the original array by values in ascending order
            $available_timeframes = array_slice($timeframes, 0, 2, true);
        }

        return array_keys($available_timeframes);
    } else {
        return array_keys($timeframes);
    }
}

function validateRegistration($edit = false) {
    $conn = dbConnect('read');
    $problems = array();

    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_members = intval($_POST['fam-members']);
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $fam_email = trim($_POST['fam-email']);
    $fam_phone = preg_replace("/[^0-9]/", '', trim($_POST['fam-phone']));
    $fam_gift = $_POST['fam-gift'];
    $fam_reservation = trim($_POST['fam-reservation']);
    $limit_members = true;

    $members = $_POST['members'];

    foreach ($members as &$member) {
        $member['name'] = ucfirst(trim($member['name']));
        $member['age'] = intval($member['age']);
        $member['gift'] = trim($member['gift']);
    }

    if(!$edit) {
        $checkCode = checkCode($fam_id);
    }

    if($checkCode) {
        array_push($problems, $checkCode[0]);
    }
    if(($fam_members != count($members))) {
        array_push($problems, "Number of members and members entered do not match. Please contact site administrator.");
    }
    if ((limit_members && count($members) > 30) || count($members) < 1) {
        array_push($problems, "You can only register between 1 and 8 family members using the online form.");
    }
    if (!filter_var($fam_email, FILTER_VALIDATE_EMAIL)) {
        array_push($problems, "Email is invalid.");
    } else if (strlen($fam_email) > 255) {
        array_push($problems, "Email is too long. Please try another email.");
    }
    if (strlen($fam_name) > 255) {
        array_push($problems, "Last name entered is too long.");
    }
    if (strlen($fam_phone) > 15) {
        array_push($problems, "Phone number entered is too long.");
    }
    if (strlen($fam_gift) > 255) {
        array_push($problems, "Family gift is too long. Please contact site administrator.");
    }
    if (strlen($fam_reservation) > 255) {
        array_push($problems, "Family reservation value is too long. Please contact site administrator.");
    }
    if (!$fam_id || !$fam_name || !$fam_phone || !$fam_email) {
        array_push($problems, "Please fill out all fields.");
    }
    foreach ($members as $member) {
        if (strlen($member["name" ]) > 255) {
            array_push($problems, htmlentities($member['name']) . "'s name is too long.");
        }
        if ($member["age"] > 130) {
            array_push($problems, htmlentities($member['name']) . "'s age is too large.");
        }
        if (strlen($member["gift"]) > 255) {
            array_push($problems, htmlentities($member['name']) . "'s gift preference is too long. Please contact site administrator.");
        }
    }
    if ($fam_reservation != "" && !in_array($fam_reservation, getTimeframes(false))) {
        array_push($problems, ("Family reservation timeframe not available."));
    }
    if ($fam_gift == "") {
        array_push($problems, "No family gift selected.");
    }
    return $problems;
}

function insertFamily() {
    $conn = dbConnect('read');

    $fam_number = intval($_POST['fam-number']);
    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $fam_email = trim($_POST['fam-email']);
    $fam_phone = preg_replace("/[^0-9]/", '', trim($_POST['fam-phone']));
    $fam_gift = $_POST['fam-gift'];
    $fam_reservation = $_POST['fam-reservation'];
    $fam_access = $_POST['access'];
    $fam_notes = trim($_POST['fam-notes']);

    if($_POST['email-reminders']) {
        $email_reminders = 1;
    } else {
        $email_reminders = 0;
    }

    $submitted_members = $_POST['members'];
    $members = [];

    foreach ($submitted_members as $member) {
        $new_array = [];

        if($member['age'] === "") {
            $new_array['age'] = '18';
        } else {
            $new_array['age'] = intval($member['age']);
        }

        $new_array['name'] = ucfirst(trim($member['name']));
        $new_array['gift'] = trim($member['gift']);

        array_push($members, $new_array);
    }

    // If there are no problems, add family to database
    if($fam_number) {
        $add_family = $conn->prepare("INSERT INTO registered_families 
        (family_number, family_id, phone, email, family_name, family_gift, email_reminders, reservation, access, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $add_family->bind_param("isssssisss", $fam_number, $fam_id, $fam_phone, $fam_email, $fam_name, $fam_gift, $email_reminders, $fam_reservation, $fam_access, $fam_notes);
    } else {
        $add_family = $conn->prepare("INSERT INTO registered_families 
        (family_id, phone, email, family_name, family_gift, email_reminders, reservation, access, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $add_family->bind_param("sssssisss", $fam_id, $fam_phone, $fam_email, $fam_name, $fam_gift, $email_reminders, $fam_reservation, $fam_access, $fam_notes);
    }

    $add_family->execute();
    $add_family->close();


    // If there are no problems, add family members to database
    $add_member = $conn->prepare("INSERT INTO registered_members (first_name, age, gift_preference, family_id)
                                    VALUES (?, ?, ?, ?)");
    $add_member->bind_param("siss", $mem_name, $mem_age, $mem_gift, $fam_id);

    foreach ($members as $member) {
        $mem_name = $member['name'];
        $mem_age = $member['age'];
        $mem_gift = $member['gift'];
        
        $add_member->execute();
    }

    $add_member->close();
    $conn->close();
}

function backupFamily() {
    $conn = dbConnect('read');

    $fam_id = strtoupper(trim($_POST['fam-id']));

    // If there are no problems, insert family into backup database
    $remove_old = $conn->prepare("DELETE FROM deleted_families
        WHERE family_id = ?");
    $remove_old->bind_param("s", $fam_id);
    $remove_old->execute();

    $backup_family = $conn->prepare("INSERT INTO deleted_families
        SELECT * FROM registered_families
        WHERE family_id = ?");
    $backup_family->bind_param("s", $fam_id);
    $backup_family->execute();

    if($backup_family->affected_rows == 1) {
        $delete_members = $conn->prepare("DELETE FROM registered_members WHERE family_id = ?");
        $delete_members->bind_param("s", $fam_id);
        $delete_members->execute();

        $delete_family = $conn->prepare("DELETE FROM registered_families WHERE family_id = ?");
        $delete_family->bind_param("s", $fam_id);
        $delete_family->execute();

        if($delete_members->affected_rows >= 1 && $delete_family->affected_rows == 1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }

    $backup_family->close();
    $delete_members->close();
    $delete_family->close();
    $conn->close();
}

function registerEmail() {
    $email_problem = false;

    $fam_email = trim($_POST['fam-email']);
    if($_POST['email-reminders']) {
        $email_opt = true;
    } else {
        $email_opt = false;
    }

    $fam_id = strtoupper(trim($_POST['fam-id']));
    $fam_name = ucfirst(trim($_POST['fam-name']));
    $members = $_POST['members'];
    $first_name = ucfirst(trim($members[1]['name']));
    $total_members = count($members);
    $reservation = $_POST['fam-reservation'];

    $data = '{"email": "' . $fam_email . '", 
        "listIds" : [11],
        "attributes": {
            "FIRSTNAME" : "' . $first_name . '", 
            "LASTNAME" : "' . $fam_name . '", 
            "FAMILYCODE" : "' . $fam_id . '", 
            "FAMILYMEMBERS" : "'. $total_members . '", 
            "RESERVATIONTIME" : "'. $reservation . '"}}';


    if($email_opt && $fam_email) {
        $ch = curl_init();
        $api_key = $brevo_api;

        curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/contacts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = array();
        $headers[] = 'Api-Key: ' . $api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $email_problem = 'Error:' . curl_error($ch);
        } else if(!strpos($result, "id")) {
            $email_problem = $result;
        }
        curl_close($ch);

        return $email_problem;
    } else {
        return "opt-out";
    }
}

?>